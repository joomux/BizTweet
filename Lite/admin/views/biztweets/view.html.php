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

jimport( 'joomla.application.component.view');
jimport( 'joomla.filesystem.file' );

class BizTweetsViewBizTweets extends JView {
	function display($tpl=null) {
		JToolBarHelper::title(JText::_('BizTweet'));
		JToolBarHelper::apply();
		JToolBarHelper::trash('purge', JText::_('Clear Cache'), false);

		$css = JFile::read(JPATH_SITE.DS.'components'.DS.'com_biztweet'.DS.'assets'.DS.'btstyle.css');
		$this->assignRef('css', $css);

		parent::display($tpl);
	}
}
