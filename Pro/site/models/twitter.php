<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted Access');

/**
 * Website: www.jtips.com.au
 * @author Jeremy Roberts
 * @copyright Copyright &copy; 2009, EvolutionEngin
 * @license Commercial - See website for details
 *
 * @since 1.0 - 02/06/2009
 * @version 1.0.0
 * @package BizTweet
 */

jimport('joomla.application.component.model');
jimport('joomla.utilities.simplecrypt');

class BizTweetsModelTwitter extends JModel {
	var $twitter = null;
	var $_callback = null;
	var $_stream	= null;
	var $_screenname = null;
	var $_params = null;

	/**
	 * Initialize the connection to the Twitter API
	 */
	function _init() {
		global $mainframe, $Itemid;
		if (empty($this->_twitter)) {
			$params = &$mainframe->getParams();
			$this->_params =& $params;
			$username = $params->get('username', '');
			$password = $params->get('password', '');
			if (!$username and !$password) {
				// get current users details
				$credentials = $this->_getUserLogin();
				$username = $credentials['username'];
				$password = $credentials['password'];
			}
			$this->twitter = new Twitter($username, $password, 'BizTweet');
			$cache_time = $this->_params->get('cache', null);
			$this->twitter->setCacheTime($cache_time);
		}
		//$mainframe->close();
	}

	/**
	 * Retrieve the Twitter connection details for the current user. Saves/updates details as required
	 * @return array
	 */
	function _getUserLogin() {
		if (JRequest::getVar('btusername', '') and JRequest::getVar('btpassword', '')) {
			// save details
			$saved = $this->_saveUserLogin(JRequest::getVar('btusername', ''), JRequest::getVar('btpassword'));
			if (!$saved) { // not a registered user
				return array(
					'username' => JRequest::getVar('btusername', ''),
					'password' => JRequest::getVar('btpassword')
				);
			}
		}
		return $this->_lookupUserLogin();
	}

	/**
	 * Retrieve the Twitter connection details for the current user
	 * @return array
	 */
	function _lookupUserLogin() {
		global $mainframe;
		$user =& Jfactory::getUser();
		if ($user->id) { // make sure there is a logged in user
			$btuser =& JTable::getInstance('btuser');
			$data = $this->getUser($user->id);
			$key = str_rot13(strrev($user->username));
			$crypt = new JSimpleCrypt($key);
			$password = $crypt->decrypt($data->password);
			return array('username' => $data->username, 'password' => $password);
		} else {
			return array('username' => '', 'password' => '');
		}
	}

	/**
	 * Write user twitter details to database, encrypting the password
	 * @param $username
	 * @param $password
	 * @return bool
	 */
	function _saveUserLogin($username, $password) {
		global $mainframe;
		$user =& JFactory::getUser();
		if ($user->id) { // make sure there is a logged in user
			$key = str_rot13(strrev($user->username));
			$crypt = new JSimpleCrypt($key);
			$hash = $crypt->encrypt($password);
			$btuser =& JTable::getInstance('btuser');
			$data = $this->getUser($user->id);
			$btuser->id = $data->id;
			$btuser->userid = $user->id;
			$btuser->username = $username;
			$btuser->password = $hash;
			return $btuser->save($btuser);
		} else {
			return false;
		}
	}

	/**
	 * Delete Twitter API login details for the selected user
	 * @param $userid
	 */
	function _deleteUserLogin($userid=null) {
		global $mainframe;
		if (!$userid) {
			$user =& JFactory::getUser();
			$userid = $user->id;
		}
		$btuser =& JTable::getInstance('btuser');
		$query = "SELECT id FROM #__btusers WHERE userid = " .$btuser->_db->Quote($userid);
		$btuser->_db->setQuery($query);
		$list = $btuser->_db->loadResultArray();
		if (!empty($list)) {
			foreach ($list as $id) {
				$btuser->delete($id);
			}
		}
	}

	/**
	 * Load the user info from the bt tables
	 * @param $userid
	 * @return object
	 */
	function &getUser($userid) {
		$db =& JFactory::getDBO();
		$query = "SELECT * FROM #__btusers WHERE userid = " .$db->Quote($userid);
		$db->setQuery($query);
		$data = $db->loadObject();
		if (!is_object($data) or !$data->id) {
			$data->username = '';
			$data->password = '';
		}
		return $data;
	}

	/**
	 * Wrapper for each of the main twitter API calls that require no additional arguments
	 * @param $callback
	 * @return object
	 */
	function getData($callback='getFriendsTimeline') {
		if (!isset($this->_stream[$callback]) or empty($this->_stream[$callback])) {
			$this->_init();
			$timeline = $this->twitter->$callback();
			$this->_stream[$callback] = $this->_parseTweets($timeline);
		}
		return $this->_stream[$callback];

	}

	/**
	 * Get the friends timeline from twitter
	 * @return object
	 */
	function getStream() {
		return $this->getData();
	}

	/**
	 * Get the timeline for the currently logged in user (logged into the twitter api)
	 * @return object
	 */
	function getUserOnly() {
		return $this->getData('getUserTimeline');
	}

	/**
	 * Get the replies for the currently logged in user (logged into the twitter api)
	 * @return object
	 */
	function getReplies() {
		return $this->getData('getMentions');
	}

	/**
	 * Get the most recent tweet for the configured account
	 * @return object A single tweet object
	 */
	function getLatestTweet() {
		$this->_init();
		$latest = $this->twitter->getUserTimeline();
		$tweets = array($latest->status);
		return array_shift($this->_parseTweets($tweets));
	}

	/**
	 * Get the rate limit for the current connection to twitter
	 * @return object
	 */
	function getRateLimit() {
		$limit = $this->twitter->rateLimitStatus(true);
		return $limit;
	}

	/**
	 * Parse a large SimpleXML object into smaller, simpler objects
	 *
	 * @param array The array of XML tweets
	 * @return array An array of parsed objects
	 */
	function _parseTweets($tweets) {
		global $mainframe;
		$parsed = array();
		if ($tweets === false) {
			$mainframe->enqueueMessage(JText::_('Error Connecting to Twitter. Check login detials'), 'error');
		}
		if (empty($tweets)) {
			// check rate limit
			$limit = $this->getRateLimit();
		} else {
			foreach ($tweets as $tweet) {
				$post = new stdClass();
				$post->status_id = $tweet->id;
				$post->in_reply_to_status_id = $tweet->in_reply_to_status_id;
				$post->in_reply_to_screen_name = $tweet->in_reply_to_screen_name;
				$post->user->image = $this->_getUserImage($tweet->user);
				$post->user->name = $this->_getUserName($tweet->user);
				$post->user->screen_name = $tweet->user->screen_name;
				$post->when = BizTweetsHelperParser::_getPostDateTime($tweet->created_at);
				$post->source = $tweet->source;
				$post->text = BizTweetsHelperParser::_parseText($tweet->text);
				//echo "<pre>".print_r($tweet, true);
				if ($tweet->truncated == 'true') {
					//$post->text .= '... TRUNCATED';//????
				}

				$parsed[] = $post;
			}
			return $parsed;
		}
	}

	/**
	 * Post a single tweet to twitter
	 *
	 * @return object
	 */
	function tweet() {
		global $mainframe;
		$this->_init();
		$status = JRequest::getString('tweet', '', 'default', 2);
		if (!$status) {
			return false;
		}
		$pic = $this->twitpic();
		if ($pic !== false) { // failed to upload and post
			if ($pic) $status .= ' ' .$pic;
			//die($pic);
			$reply_id = JRequest::getVar('reply_id', null);
			if (!$reply_id) $reply_id = null;
			$this->twitter->clearCache();
			$result = $this->twitter->updateStatus($status, $reply_id);
			if (!$result) {
				// clear login info from db
				$this->_deleteUserLogin();
			} else {
				$user =& JFactory::getUser();
				$post =& JTable::getInstance('btpost');
				$post->userid = $user->id;
				$post->post = $status;
				$post->save($post);
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Post an image to TwitPic
	 */
	function twitpic() {
		$filedata = JRequest::getVar('twitpic', null, 'files');
		jimport('joomla.filesystem.file');
		if (is_array($filedata) and isset($filedata['tmp_name']) and !empty($filedata['tmp_name']) and JFile::exists($filedata['tmp_name'])) {
			$this->_init();
			return $this->twitter->postpic($filedata);
		} else {
			return '';
		}
	}

	/**
	 * Parse a twitter avatar
	 * @param $user
	 * @return string
	 */
	function _getUserImage($user) {
		return "<img src='" .$user->profile_image_url. "' border='0' alt='" .$this->_params->get('user', 'screen_name'). "' width='48px' height='48px' />";
	}

	/**
	 * Return either the user name or screen name based on parameter settings
	 *
	 * @param $user
	 * @return string
	 */
	function _getUserName($user) {
		$field = $this->_params->get('user', 'screen_name');
		return $user->$field;
	}

	function getUserSearches() {
		global $mainframe;
		$params =& $mainframe->getParams();
		// load search criteria from database
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$query = "SELECT * FROM #__btsearches WHERE userid = " .$db->Quote($user->id). " ORDER BY id LIMIT 3";
		$db->setQuery($query);
		$results = $db->loadAssocList();
		$data = array();
		//if (!empty($results)) {
			for ($i=0; $i<3; $i++) {
				$data[$i] = isset($results[$i]['search']) ? $results[$i]['search'] : '';
			}
		//}
		return $data;
	}

	function saveSearch() {
		global $mainframe;
		$user =& JFactory::getUser();
		$term = JRequest::getVar('term', '', '', 'string');
		$btsearch =& JTable::getInstance('btsearch');
		$btsearch->userid = $user->id;
		$btsearch->search = $term;
		return $btsearch->save($btsearch);
	}

	function deleteSearch() {
		global $mainframe;
		$user =& JFactory::getUser();
		$term = JRequest::getVar('term', '', '', 'string');
		$btsearch =& JTable::getInstance('btsearch');
		$btsearch->userid = $user->id;
		$btsearch->search = $term;
		$query = "SELECT id FROM #__btsearches WHERE userid = " .$btsearch->_db->Quote($user->id). " AND search = " .$btsearch->_db->Quote($term);
		$btsearch->_db->setQuery($query);
		$id = $btsearch->_db->loadResult();
		return $btsearch->delete($id);
	}

	function follow() {
		global $mainframe;
		$params =& $mainframe->getParams();
		$auto_follow = $params->get('auto_follow', '');
		if (!empty($auto_follow)) {
			$this->_init();
			$t_user = substr($this->twitter->credentials, 0, strpos($this->twitter->credentials, ':'));
			if ($t_user != $auto_follow) { // make sure you don't follow yourself
				$test = $this->twitter->friendshipExists($t_user, $auto_follow);
				$exists_string = (string)$test;
				if ($exists_string == 'true') {
					$exists = true;
				} else {
					$exists = false;
				}
				if (!$exists) {
					$options = array('id' => $auto_follow);
					$result = $this->twitter->createFriendship($options);
					if ($result) {
						$mainframe->enqueueMessage(JText::_('You are now following'). ' ' .$auto_follow);
						return true;
					} else {
						// try to follow instead
						if ($this->twitter->follow($options)) {
							$mainframe->enqueueMessage(JText::_('You are now following'). ' ' .$auto_follow);
							return true;
						}
						return false;
					}
				}
			}
		}
		return true;
	}
}
