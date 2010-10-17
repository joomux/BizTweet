<?php
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

jimport('joomla.application.component.controller');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'twitter.php');

class BizTweetsController extends JController {
        /**
         * Method to show a tweet view
         *
         * @access      public
         * @since       1.0
         */
        function display() {
//		$model =& $this->getModel('BizTweets');

                parent::display();
        }

	function tweet() {
		$Itemid = JRequest::getCmd('Itemid', '');

		$model =& $this->getModel('Stream');

		$model->tweet();

		$url = JRoute::_('index.php?option=com_biztweet&Itemid=' .$Itemid, false);

		$this->setRedirect($url, JText::_('Tweet tweet!'));
	}

	function trim() {
		global $mainframe;
		$url = JRequest::getVar('url', '');
		// make sure we have a valid URL
		if (!preg_match('/^http[s]{0-1}:\/\//i', $url)) {
			$url = 'http://' .$url;
		}
		$trimUrl = 'http://api.tr.im/api/trim_url.json?url=' .urlencode($url);
		$handle = curl_init($trimUrl);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, 'url='.$url);
		$response = curl_exec($handle);
		echo preg_replace('/1$/', '', $response);
		$mainframe->close();
		
	}
}
