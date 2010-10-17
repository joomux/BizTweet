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

class BizTweetsViewUser extends JView {
	function display($tpl=null) {
		global $mainframe, $Itemid;

		$pathway  =& $mainframe->getPathway();
		$document =& JFactory::getDocument();
		$params = &$mainframe->getParams();

		JHTML::_('behavior.formvalidation');
		JHTML::_('behavior.mootools');
		JHTML::script('btscripts.js', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
		JHTML::stylesheet('btstyle.css', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
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
				$params->set('page_title',      JText::_( 'My Twitter' ));
			}
		} else {
			$params->set('page_title',      JText::_( 'My Twitter' ));
		}
		$document->setTitle( $params->get( 'page_title' ) );

		$user =& JFactory::getUser();
		$this->assignRef('user', $user);
		$this->assignRef('params', $params);

		$this->loadHelper('twitter');
		$this->loadHelper('parser');

		$model		=& $this->getModel();

		$btuser =& $model->getUser($user->id);
		$this->assignRef('btuser', $btuser);

		// get search params
		$search =& $model->getUserSearches();
		$params->set('search1', $search[0]);
		$params->set('search2', $search[1]);
		$params->set('search3', $search[2]);

		$searches = array();
		foreach ($search as $term) {
			if (!empty($term)) $searches[] = $term;
		}
		if (!empty($searches)) {
			$jsArray = '"' .implode('", "', $searches). '"';
		} else {
			$jsArray = '';
		}
		//$this->assignRef('btUserSearches', $jsArray);
		$hashtags = preg_replace('/["\']/', '', $params->get('hashtags', ''));
		$document->addScriptDeclaration("
			var btSitePath = '" .JURI::base(). "';
			var btItemid = " .$Itemid. ";
			var btSearchLimit = " .$params->get('limit', 10). ";
			var btUpdateFrequency = " .($params->get('frequency', 10)*1000). ";
			var btRequireTags = " .$params->get('requiretags', 0). ";
			var btHashTags = escape('" .$hashtags. "');
			var btUserSearches = [" .$jsArray. "];
			var btLanguage = '" .$params->get('lang', ''). "';
			var btTwitPicThumbs = " .$params->get('pic_thumb', 1). ";
			var btLinkNewWindow = " .$params->get('target', 1). ";
			window.addEvent('domready', function() {
				btUpdateSearches();
				$('searchbt').addEvent('focus', function () {btCheckUrl('searchbt', '" .JText::_('SEARCHDOT'). "');});
				$('searchbt').addEvent('blur',  function () {btCheckUrl('searchbt', '" .JText::_('SEARCHDOT'). "');});
			});
		");

		$gids = $params->get('canpost', array());
		if (!empty($gids) and !is_array($gids)) $gids = array($gids);
		if (in_array($user->gid, $gids) or (!$this->user->gid and in_array('29', $gids))) {
			$document->addScriptDeclaration("
			var btEnableRetweet = " .$params->get('retweet', 0). ";
			var btEnableReply = " .$params->get('reply', 0). ";
				window.addEvent('domready', function() {
					btCountChars($('tweet'));
					$('raw_url').addEvent('focus', function () {btCheckUrl('raw_url', '" .JText::_('ENTER LINK HERE'). "');});
					$('raw_url').addEvent('blur',  function () {btCheckUrl('raw_url', '" .JText::_('ENTER LINK HERE'). "');});
					//$('searchbt').addEvent('focus', function () {btCheckUrl('searchbt', '" .JText::_('SEARCHDOT'). "');});
					//$('searchbt').addEvent('blur',  function () {btCheckUrl('searchbt', '" .JText::_('SEARCHDOT'). "');});
				});
			");
		} else {
			$hasSearch = false;
			$document->addScriptDeclaration("
				var btEnableRetweet = 0;
				var btEnableReply = 0;
			");
		}

		$divisor = 0;
		if (!empty($search[0])) $divisor++;
		if (!empty($search[1])) $divisor++;
		if (!empty($search[2])) $divisor++;
		if (!$divisor) {
			$divisor = 1;
		} else {
			$hasSearch = true;
		}

		$width = floor(100/$divisor);
		$this->assignRef('colwidth', $width);

		if (!$hasSearch) {
			$mainframe->enqueueMessage(JText::_('No searches available'));
		}

		parent::display($tpl);
		parent::display('footer');
	}
}
