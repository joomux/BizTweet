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

jimport( 'joomla.application.component.view');

class BizTweetsViewSearch extends JView {
	function display($tpl=null) {
		global $mainframe, $Itemid;

		$pathway  =& $mainframe->getPathway();
		$document =& JFactory::getDocument();
		$params = &$mainframe->getParams();
		$user =& JFactory::getUser();

		JHTML::_('behavior.formvalidation');
		JHTML::_('behavior.mootools');
		JHTML::script('btscripts.js', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
		JHTML::stylesheet('btstyle.css', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
		$hashtags = preg_replace('/["\']/', '', $params->get('hashtags', ''));
		$document->addScriptDeclaration("
			var btSitePath = '" .JURI::base(). "';
			var btItemid = " .$Itemid. ";
			var btSearchLimit = " .$params->get('limit', 10). ";
			var btUpdateFrequency = " .($params->get('frequency', 10)*1000). ";
			var btRequireTags = " .$params->get('requiretags', 0). ";
			var btHashTags = escape('" .$hashtags. "');
			var btLanguage = '" .$params->get('lang', ''). "';
			var btTwitPicThumbs = " .$params->get('pic_thumb', 1). ";
			var btLinkNewWindow = " .$params->get('target', 1). ";
			window.addEvent('domready', function() {btUpdateSearches();});
		");

		$gids = $params->get('canpost', array());
		if (!empty($gids) and !is_array($gids)) $gids = array($gids);
		if (in_array($user->gid, $gids) or (!$user->gid and in_array('29', $gids))) {
			$document->addScriptDeclaration("
				var btEnableRetweet = " .$params->get('retweet', 0). ";
				var btEnableReply = " .$params->get('reply', 0). ";
				window.addEvent('domready', function() {
					btCountChars($('tweet'));
					$('raw_url').addEvent('focus', function () {btCheckUrl('raw_url', '" .JText::_('ENTER LINK HERE'). "');});
					$('raw_url').addEvent('blur',  function () {btCheckUrl('raw_url', '" .JText::_('ENTER LINK HERE'). "');});
				});
			");
		} else {
			$document->addScriptDeclaration("
				var btEnableRetweet = 0;
				var btEnableReply = 0;
			");
		}
		/* Override the default class to avoid dual lines */
		$document->addStyleDeclaration("
			ul.timeline li:first-child {
				border-top:none;
			}
		");

		// Page Title
		$menus  = &JSite::getMenu();
		$menu   = $menus->getActive();
		$this->assignRef('Itemid', $menu->id);

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',      JText::_( 'Twitter Search' ));
			}
		} else {
			$params->set('page_title',      JText::_( 'Twitter Search' ));
		}
		$document->setTitle( $params->get( 'page_title' ) );

		$this->assignRef('user', $user);
		$this->assignRef('params', $params);

		$this->loadHelper('twitter');
		$this->loadHelper('parser');

		$this->assignRef('hashtags', $hashtags);

		$model		=& $this->getModel();

		$btuser =& $model->getUser($user->id);
		$this->assignRef('btuser', $btuser);

		// get search params
		$search1 = $params->get('search1', '');
		$search2 = $params->get('search2', '');
		$search3 = $params->get('search3', '');

		$divisor = 0;
		if (!empty($search1)) $divisor++;
		if (!empty($search2)) $divisor++;
		if (!empty($search3)) $divisor++;
		if (!$divisor) $divisor = 1;

		$width = floor(100/$divisor);
		$this->assignRef('colwidth', $width);
		parent::display($tpl);
		parent::display('footer');
	}
}
