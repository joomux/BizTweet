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

jimport('joomla.application.component.helper');

class BizTweetsHelperParser {
	function display($items=array(), $title='') {
		global $mainframe;
		$params =& $mainframe->getParams();
		$user	=& JFactory::getUser();
		$gids = $params->get('canpost', null);
		if (!is_array($gids)) $gids = array($gids);
		if (!empty($items)) {
			?>
			<div class="contentheading"><?php echo $title; ?></div>
			<ul class="timeline"><?php
			foreach ($items as $item) {
				?>
				<li>
				<span class="biztweet_image">
					<a href="http://twitter.com/<?php echo $item->user->screen_name; ?>" target="_blank" title="<?php echo $item->user->name; ?>" border="0">
					<?php echo $item->user->image; ?>
					</a>
				</span>
				<span class="biztweet_body">
					<a href="http://twitter.com/<?php echo $item->user->screen_name; ?>" target="_blank" title="<?php echo $item->user->name; ?>" border="0">
						<strong><?php echo $item->user->name; ?></strong>
					</a>
					&nbsp;<span id="bt_<?php echo $item->status_id; ?>"><?php echo $item->text; ?></span>
					<br /><small><em><?php echo JText::_('about'); ?> <?php echo $item->when; ?> <?php echo JText::_('ago via'); ?> <?php echo $item->source; ?>
					<?php
					if (!empty($item->in_reply_to_status_id) and $item->in_reply_to_status_id != '' and !empty($item->in_reply_to_screen_name) and $item->in_reply_to_screen_name != '') {
						$reply_to = JText::_('in reply to ').$item->in_reply_to_screen_name;
						if ($params->get('newwindow', 0)) {
							$attribs = array("target" => "_blank");
						} else {
							$blank = array();
						}
						echo JHTML::link('http://twitter.com/'.$item->in_reply_to_screen_name.'/status/'.$item->in_reply_to_status_id, $reply_to, $attribs);
					}
					?>
					</em></small>
					<?php

					if (in_array($user->gid, $gids) or (!$user->gid and in_array('29', $gids))) {
						if ($params->get('retweet', 0) == 1) {
							?>
						<a href="javascript:btRT('bt_<?php echo $item->status_id; ?>', '<?php echo $item->user->screen_name; ?>');" title="<?php echo JText::_('ReTweet'); ?>">
							<img src="<?php echo 'components'.DS.'com_biztweet'.DS.'assets'.DS.'retweet.png'; ?>" border="0" align="absmiddle" />
						</a>
							<?php
						}
						if ($params->get('reply', 0) == 1) {
							?>
						<a href="javascript:btReply('<?php echo $item->user->screen_name; ?>', '<?php echo $item->status_id; ?>');" title="<?php echo JText::_('Reply'); ?>">
							<img src="<?php echo 'components'.DS.'com_biztweet'.DS.'assets'.DS.'reply.png'; ?>" border="0" align="absmiddle" />
						</a>
							<?php
						}
					}
					?>

				</span>
				</li>
				<?php
			}
			?>
			</ul>
			<?php
		}
	}

	function _getPostDateTime($time) {
                $posted = strtotime($time);
                $now = time();

		$diffInSeconds = abs(intval($now - $posted));
		if ($diffInSeconds <= 60) {

			return abs(ceil($diffInSeconds)). " " .JText::_('seconds');
		} else {
                	$diffInMinutes = $diffInSeconds/60;
			if ($diffInMinutes < 60) {
				return abs(ceil($diffInMinutes)). " " .JText::_('minutes');
			} else {
				// greater than 60, check if less than 1 day
				$diffInHours = $diffInMinutes/60;
				if ($diffInHours < 24) {
					return abs(ceil($diffInHours)). " " .JText::_('hours');
				} else {
					$diffInDays = $diffInHours/24;
					return abs(ceil($diffInDays)). " " .JText::_('days');
				}
			}
		}
                return strftime('%Y %b, %e', $posted);
        }

	function _parseText($text) {
		global $mainframe;
		$params =& $mainframe->getParams();
		if ($params->get('newwindow', 0)) {
			$blank = " target='_blank'";
		} else {
			$blank = "";
		}
                // parse links
                $text = preg_replace( '/(http|ftp)+(s)?:(\/\/)((\w|\.)+)(\/)?(\S+)?/i', '<a href="\0"' .$blank. '>\0</a>', $text );
                // parse @ replies
                $text = preg_replace( '/@([a-zA-Z0-9_]+)/', '@<a href="http://twitter.com/\1"' .$blank. '>\1</a>', $text);
                // parse hashtags
                $text = preg_replace( '/\#([a-zA-Z0-9_]+)/', '#<a href="http://search.twitter.com/search?q=\1"' .$blank. '>\1</a>', $text);
                return $text;
        }
}
