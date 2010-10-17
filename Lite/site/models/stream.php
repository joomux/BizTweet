<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted Access');

/**
 * Website: www.jtips.com.au
 * @author Jeremy Roberts
 * @copyright Copyright &copy; 2009, EvolutionEngin
 * @license GPLv3
 *
 * @since 1.0 - 02/06/2009
 * @version 1.0.0
 * @package BizTweet
 */

jimport('joomla.application.component.model');

class BizTweetsModelStream extends JModel {
	var $twitter = null;
	var $_callback = null;
	var $_stream	= null;
	var $_screenname = null;
	var $_params = null;

	function _init() {
		global $mainframe, $Itemid;
		if (empty($this->_twitter)) {
			$params = &$mainframe->getParams();
			$this->_params =& $params;
			$this->twitter = new Twitter($params->get('username', ''), $params->get('password', ''), 'BizTweet');
			$cache_time = $this->_params->get('cache', null);
			$this->twitter->setCacheTime($cache_time);
		}
	}
	function getData($callback='getFriendsTimeline') {
		if (!isset($this->_stream[$callback]) or empty($this->_stream[$callback])) {
			$this->_init();
			$timeline = $this->twitter->$callback();
			$this->_stream[$callback] = $this->_parseTweets($timeline);
		}
		return $this->_stream[$callback];

	}

	function getStream() {
		return $this->getData();
	}

	function getUserOnly() {
		return $this->getData('getUserTimeline');
	}

	function getReplies() {
		return $this->getData('getMentions');
	}

	/**
	 * Get the most recent tweet for the configured account
	 * @return object A single tween object
	 */
	function getLatestTweet() {
		$this->_init();
		$latest = $this->twitter->getUserTimeline();
		$tweets = array($latest->status);
		return array_shift($this->_parseTweets($tweets));
	}

	function getRateLimit() {
		$limit = $this->twitter->rateLimitStatus(true);
		return $limit;
	}

	/**
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
					$post->text .= '... TRUNCATED';//????
				}

				$parsed[] = $post;
			}
			return $parsed;
		}
	}

	function tweet() {
		global $mainframe;
		$this->_init();
		$status = JRequest::getString('tweet', '', 'default', 2);
		if (!$status) {
			return false;
		}
		$reply_id = JRequest::getVar('reply_id', null);
		if (!$reply_id) $reply_id = null;
		$this->twitter->clearCache();
		$result = $this->twitter->updateStatus($status, $reply_id);
		return $result;
	}

	function _getUserImage($user) {
		return "<img src='" .$user->profile_image_url. "' border='0' alt='" .$this->_params->get('user', 'screen_name'). "' width='48px' height='48px' />";
	}

	function _getUserName($user) {
		$field = $this->_params->get('user', 'screen_name');
		return $user->$field;
	}
}
