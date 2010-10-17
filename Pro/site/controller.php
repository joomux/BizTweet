<?php
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
		JHTML::_('behavior.mootools');
		JHTML::_('behavior.modal');
		JHTML::_('behavior.tooltip');
		$model =& $this->getModel('Twitter');
		$document =& JFactory::getDocument();
		$viewType   = $document->getType();
		$viewName   = JRequest::getCmd( 'view', $this->getName() );
		$viewLayout   = JRequest::getCmd( 'layout', 'default' );
		$view =& $this->getView($viewName, $viewType);
		$view->setModel($model, true);

		parent::display();
	}

	function tweet() {
		$Itemid = JRequest::getCmd('Itemid', '');

		$model =& $this->getModel('Twitter');

		$result = $model->tweet();

		$url = 'index.php?option=com_biztweet&Itemid=' .$Itemid;

		if ($result) {
			$msg = JText::_('TWEETWEET');
			$model->follow();
		} else {
			$msg = JText::_('POSTERROR');
		}

		$this->setRedirect(JRoute::_($url, false), $msg);
	}

	function trim() {
		global $mainframe;
		$params =& $mainframe->getParams();
		$apis = array(
			'trim' => 'http://api.tr.im/api/trim_url.json?url=',
			'tinyurl' => 'http://tinyurl.com/api-create.php?url='
		);
		$url = JRequest::getVar('url', '');
		// make sure we have a valid URL
		if (!preg_match('/^http[s]{0-1}:\/\//i', $url)) {
			$url = 'http://' .$url;
		}
		$trimUrl = $apis[$params->get('url_api', 'trim')].urlencode($url);
		$handle = curl_init($trimUrl);
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, 'url='.$url);
		$response = curl_exec($handle);
		echo preg_replace('/1$/', '', $response);
		$mainframe->close();

	}

	function search() {
		global $mainframe;
		$model =& $this->getModel('Twitter');
		$res = $model->saveSearch();
		echo intval($res);
		$mainframe->close();
	}

	function clear() {
		global $mainframe;
		$model =& $this->getModel('Twitter');
		$res = $model->deleteSearch();
		echo intval($res);
		$mainframe->close();
	}

	function twitpic() {
		global $mainframe;
		jimport('joomla.filesystem.file');
		$image = 'http://twitpic.com/show/full/' .JFile::stripExt(JRequest::getVar('image'));
		$info = getimagesize($image);
		if (!isset($info[0]) or !$info[0]) { // something went wrong! show an error
			echo JText::_('Unable to load image');
			$mainframe->close();
		} else {
			ob_end_clean();
			$imagestring = file_get_contents($image);
			header('Last-Modified: '.date('r'));
			header('Accept-Ranges: bytes');
			header('Content-Length: ' .strlen($imagestring));
			header('Content-Type: ' .$info['mime']);
			echo $imagestring;
			$mainframe->close();
		}

	}

	/**
	 * Upload an image and return the URL from TwitPic
	 */
	function upload() {
		global $mainframe;
		$model =& $this->getModel('Twitter');
//		$result = $model->twitpic();
//		echo json_encode($result);
		$filedata = JRequest::getVar('twitpic', null, 'files');
		$post = JRequest::get('post');
		$file = JRequest::get('files');
		print_r($file);
		$mainframe->close();
	}
}
