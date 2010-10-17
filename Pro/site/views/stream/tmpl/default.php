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

if ( $this->params->def( 'show_page_title', 1 ) ) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php endif; ?>
<?php
$gids = $this->params->get('canpost', array());
if (!empty($gids) and !is_array($gids)) $gids = array($gids);
if (in_array($this->user->gid, $gids) or (!$this->user->gid and in_array('29', $gids))) {
	// does the use have access to post a tweet?
	?>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
		<input type="hidden" name="option" value="com_biztweet" />
		<input type="hidden" name="task" value="tweet" />
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		<input type="hidden" name="reply_id" id="reply_id" value="" />
		<table width="100%">
		<tr>
		<td width="90%">
		<textarea name="tweet" id="tweet" class="inputbox required validate-hashtags" style="width:95%;" rows="2" maxlength="140" onkeyup="btCountChars(this);"><?php echo $this->post_prefix; ?></textarea>
		</td>
		<td class="contentheading" id="btcharcount">
			140
		</td>
		</tr>
		<?php if ($this->params->get('hashtags', '')) : ?>
		<tr>
		<td id="bthashtags">
			<?php echo $this->params->get('requiretags', 0) ? JText::_('Required') : JText::_('Optional'); ?>
			<?php echo JText::_('Tags'); ?>:
			<?php
			$tags = explode(' ', $this->hashtags);
			$cleantags = array_map('trim', $tags);
			$hashtags = array_unique($cleantags);
			sort($hashtags);
			foreach ($hashtags as $tag) {
				$tag = preg_replace('/[^A-Za-z0-9_]/', '', trim($tag));
				if (empty($tag)) continue;
				echo JHTML::link("javascript:btAddTag('#" .rawurlencode($tag). "');", "#$tag", array('title' => JText::_('Add Tag')));
				echo "&nbsp;&nbsp;";
			}
			?>
		</td>
		<td>&nbsp;</td>
		</tr>
		<?php endif; ?>
		<tr>
		<td valign="middle">
			<input type="text" name="raw_url" id="raw_url" value="<?php echo JText::_('ENTER LINK HERE'); ?>" class="inputbox" style="width:65%;" />
			&nbsp;<input type="button" class="button" onClick="return btParseUrl('raw_url', '<?php echo JText::_('ENTER LINK HERE'); ?>');" value="<?php echo JText::_('Add Link'); ?>" style="width:25%;" id="btlinkbtn" />&nbsp;<?php echo JHTML::image(JURI::base().'components/com_biztweet/assets/loading.gif', JText::_('Loading...'), array('style' => 'visibility:hidden;', 'align' => 'absmiddle', 'id' => 'btlinkload')); ?>
		</td>
		<td valign="middle" align="center" nowrap>
			<input type="submit" id="btpost" class="button validate" value="<?php echo JText::_('TWEET'); ?>" />
			&nbsp;<input type="button" id="btclear" class="button" value="<?php echo JText::_('Clear'); ?>" onClick="btReset('<?php echo $this->post_prefix; ?>');" />
		</td>
		</tr>
		<?php if ($this->params->get('twitpic', 1)) : ?>
		<tr>
		<td valign="middle">
			<input type="file" name="twitpic" id="twitpic" value="Upload Image" />
			&nbsp;<?php echo JHTML::tooltip(JText::_('Upload an image with TwitPic'), JText::_('Upload')); ?>
		</td>
		<td>&nbsp;</td>
		</tr>
		<?php endif; ?>
		</table>
	</form>
	<?php
}
if ($this->params->get('show_latest', 1) and $this->latest->text) {
	?>
	<div id="btlatest">
		<h2><?php echo JText::_('Latest'); ?></h2>
		<blockquote><?php echo $this->latest->text; ?></blockquote>
	</div>
	<?php
}
?><div style="clear:both;"></div><?php
if ($this->params->get('followme_image', -1) != -1) {
	?>
	<p id="btfollowme">
		<a href="http://twitter.com/<?php echo $this->params->get('username', ''); ?>" target="_blank" />
			<?php echo JHTML::image('images'.DS.'stories'.DS.$this->params->get('followme_image', ''), JText::_('Follow Us on Twitter'), array('border'=>0)); ?>
		</a>
	</p>
	<?php
}
if ($this->params->get('show_friends', 1)) {
	?>
	<div style="float:left;width:<?php echo $this->colwidth; ?>%;">
	<?php

		BizTweetsHelperParser::display($this->items, $this->params->get('friends_title', JText::_('Our Friends')));
	?>
	</div>
	<?php
}
if ($this->params->get('show_replies', 0)) {
	?>
	<div style="float:left;width:<?php echo $this->colwidth; ?>%;">
	<?php
		BizTweetsHelperParser::display($this->replies, $this->params->get('replies_title', JText::_('REPLIES')));
	?>
	</div>
	<?php
}
if ($this->params->get('show_useronly', 0)) {
	?>
	<div style="float:left;width:<?php echo $this->colwidth; ?>%;">
	<?php
	BizTweetsHelperParser::display($this->useronly, $this->params->get('useronly_title', JText::_('Our Tweets')));
	?>
	</div>
	<?php
}
?>
<div style="clear:left;"></div>
<div style="text-align:center;">
<?php
$remaining = 'remaining-hits';
$hourly = 'hourly-limit';
$reset = 'reset-time';
echo JText::_('Remaining API'); ?>: <?php echo $this->limit->$remaining; ?>/<?php echo $this->limit->$hourly; ?>. <?php echo JText::_('Resets'); ?> <?php echo BizTweetsHelperParser::_getPostDateTime($this->limit->$reset);?>
</div>
