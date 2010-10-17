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

class BizTweetsViewStream extends JView {
	function display($tpl=null) {
		global $mainframe, $Itemid;

		$pathway  =& $mainframe->getPathway();
		$document =& JFactory::getDocument();
		$params = &$mainframe->getParams();
		$user =& JFactory::getUser();

		JHTML::_('behavior.formvalidation');
		JHTML::script('btscripts.js', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
		JHTML::stylesheet('btstyle.css', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
		$hashtags = preg_replace('/["\']/', '', $params->get('hashtags', ''));
		$document->addScriptDeclaration("
			var btSitePath = '" .JURI::base(). "';
			var btItemid = " .$Itemid. ";
			var btRequireTags = " .$params->get('requiretags', 0). ";
			var btHashTags = escape('" .$hashtags. "');
		");

		$gids = $params->get('canpost', array());
		if (!empty($gids) and !is_array($gids)) $gids = array($gids);
		if (in_array($user->gid, $gids) or (!$user->gid and in_array('29', $gids))) {
			$document->addScriptDeclaration("
				window.addEvent('domready', function() {
					btCountChars($('tweet'));
					$('raw_url').addEvent('focus', function () {btCheckUrl('raw_url', '" .JText::_('ENTER LINK HERE'). "');});
					$('raw_url').addEvent('blur',  function () {btCheckUrl('raw_url', '" .JText::_('ENTER LINK HERE'). "');});
				});
			");
		}

		// Page Title
		$menus  = &JSite::getMenu();
		$menu   = $menus->getActive();
		$this->assignRef('Itemid', $menu->id);

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',      JText::_( 'Our Tweets' ));
			}
		} else {
			$params->set('page_title',      JText::_( 'Our Tweets' ));
		}
		$document->setTitle( $params->get( 'page_title' ) );

		$this->assignRef('user', $user);
		$this->assignRef('params', $params);

		if ($params->get('prefix', '')) {
			$field = $params->get('prefix', 'username');
			if (!$user->id) { // this is a guest
				if ($field == 'username') {
					$prefix = JText::_("guest"). ": ";
				} else {
					$prefix = JText::_("Guest"). ": ";
				}
			} else {
				$prefix = $user->$field. ": ";
			}

		} else {
			$prefix = '';
		}
		$this->assignRef('post_prefix', $prefix);

		$this->loadHelper('twitter');
		$this->loadHelper('parser');

		$this->assignRef('hashtags', $hashtags);

		$model		=& $this->getModel();

		if ($params->get('show_latest', 1)) {
			$latest		= $model->getLatestTweet();
			$this->assignRef('latest', $latest);
		}

		if ($params->get('show_friends', 1)) {
			$data		= $model->getStream();
			$this->assignRef('items', $data);
		}
		if ($params->get('show_replies', 0)) {
			$replies	= $model->getReplies();
			$this->assignRef('replies', $replies);
		}
		if ($params->get('show_useronly', 0)) {
			$useronly	= $model->getUserOnly();
			$this->assignRef('useronly', $useronly);
		}
		$limit		= $model->getRateLimit();
		$this->assignRef('limit', $limit);

		$divisor = $params->get('show_friends', 1) + $params->get('show_replies', 0) + $params->get('show_useronly', 0);
		if (!$divisor) $divisor = 1;
		$width = floor(100/$divisor);
		$this->assignRef('colwidth', $width);

		parent::display($tpl);
		parent::display('footer');
	}
}
