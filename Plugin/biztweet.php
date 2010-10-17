<?php
defined('_JEXEC') or die('Restricted access');
/**
 * Website: www.jtips.com.au
 * @author Jeremy Roberts
 * @copyright Copyright &copy; 2009, EvolutionEngin
 * @license Commercial - See website for details
 *
 * @since 1.0 - 16/07/2009
 * @version 1.0.0
 * @package BizTweet Plugin
 */

jimport( 'joomla.plugin.plugin' );

class plgContentBizTweet extends JPlugin {

	var $_params = null;

	function plgContentBizTweet(&$subject, $params) {
		parent::__construct($subject, $params);
		$plugin =& JPluginHelper::getPlugin('content', 'biztweet');
		$pluginParams = new JParameter( $plugin->params );
		$this->_params = $pluginParams;
	}

	function onPrepareContent(&$article, &$params, $limitstart) {
		global $Itemid;
		// if not viewing a full article, do nothing
		if (JRequest::getCmd('view') == 'article') {
			// load the script
			$document =& JFactory::getDocument();
			JHTML::_('behavior.formvalidation');
			JHTML::_('behavior.mootools');
			jimport('joomla.filesystem.file');
			// Changed to use CSS and JS from the plugin directory if component not available
			if (JFile::exists('components'.DS.'com_biztweet'.DS.'assets'.DS.'btstyle.css')) {
				//JHTML::script('btscripts.js', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
				JHTML::stylesheet('btstyle.css', 'components'.DS.'com_biztweet'.DS.'assets'.DS);
			} else {
				//JHTML::script('btscripts.js', JURI::base().'plugins/content/biztweet/');
				JHTML::stylesheet('btstyle.css', JURI::base().'plugins/content/biztweet/');
			}
			// Always use the local js package to avoid conflicts with BT Lite
			JHTML::script('btscripts.js', JURI::base().'plugins/content/biztweet/');
			$document->addScriptDeclaration("
				var btSitePath = '" .JURI::base(). "';
				var btItemid = " .$Itemid. ";
				var btSearchLimit = " .$this->_params->get('limit', 10). ";
				var btUpdateFrequency = " .($this->_params->get('frequency', 10)*1000). ";
				var btLanguage = '" .$this->_params->get('lang', ''). "';
				var btTwitPicThumbs = " .$this->_params->get('pic_thumb', 1). ";
				var btLinkNewWindow = '" .$this->_params->get('target', 1). "';
				var btEnableRetweet = 0;
				var btEnableReply = 0;
				window.addEvent('domready', function() {btUpdateSearches();});
			");
			/* Override the default class to avoid dual lines */
			$document->addStyleDeclaration("
				ul.timeline li:first-child {
					border-top:none;
				}
			");
		}
	}

	/**
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param       object	The article object. Note $article->text is also available
	 * @param       object	The article params
	 * @param       int		The 'page' number
	 * @return      string
	 */
	function onAfterDisplayContent(&$article, &$params, $limitstart) {
		$regex = '/{biztweet}([^{]+){\/biztweet}/i';
		// simple performance check to determine whether bot should process further
		if ( strpos( $article->text, 'biztweet' ) !== false  and JRequest::getCmd('view') == 'article') {
			$matches = array();
			if (preg_match_all($regex, $article->text, $matches)) {
				//$search = array_pop($matches); // pop from the end
				$count = count($matches[1]);
				$width = floor(100 / $count);

				// now replace it
				$article->text = preg_replace($regex, '', $article->text);

				// build the html
				$tweets  = "";
				if ($this->_params->get('title', '')) {
					$tweets .= "<div class='contentheading'>" .$this->_params->get('title', ''). "</div>";
				}
				foreach ($matches[1] as $search) {
					$search	= preg_replace('/[^A-Za-z0-9_\s]/', '', $search);
					$id		= preg_replace('/[^A-Za-z0-9_]/', '__', $search);
					$tweets .= "<div id='btload_" .$id. "' style='text-align:center;'>";
					$tweets .= JHTML::image(JURI::base().'plugins/content/biztweet/loading.gif', JText::_('Loading...'));
					$tweets .= "</div>";
					$tweets .= "<ul class='timeline btsearchbox' title='" .$search. "' id='btsearch1'></ul>";
				}
				$tweets .= "<div style='clear:both;'></div>";
				return $tweets;
			}
		} else if (strpos( $article->text, 'biztweet' ) !== false  and JRequest::getCmd('view') != 'article') {
			// remove the tag
			$article->text = preg_replace($regex, '', $article->text);
		}
	}
}
