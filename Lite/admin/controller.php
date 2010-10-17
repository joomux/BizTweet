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

jimport( 'joomla.application.component.controller' );
jimport( 'joomla.filesystem.file' );

class BizTweetsController extends JController {
	function display() {
		parent::display();
	}

	/**
	 * Save the CSS file
	 */
	function apply() {
		$css = JRequest::getVar('css', '');
		$path = JPATH_SITE.DS.'components'.DS.'com_biztweet'.DS.'assets'.DS;
		if (JFile::write($path.'btstyle.css', $css)) {
			$msg = JText::_('CSS Updated');
		} else {
			$msg = JText::_('Failed to apply changes. Is the file writable?');
		}

		$url = JRoute::_('index.php?option=com_biztweet');

		$this->setRedirect($url, $msg);
	}
	// delete all cached data
	function purge() {
		$deleted = 0;
		$cache = JPATH_SITE.DS.'cache'.DS.'biztweet';
		// get a list of all files in that firectory
		$files = JFolder::files($cache);
		if (!empty($files)) {
			foreach ($files as $filename) {
				if (JFile::exists($cache.DS.$filename)) {
					if (JFile::delete($cache.DS.$filename)) {
						$deleted++;
					}
				}
			}
		}
		$msg = $deleted. ' ' .JText::_('Files Purged');
		$url = JRoute::_('index.php?option=com_biztweet', false);
		$this->setRedirect($url, $msg);
	}
}
