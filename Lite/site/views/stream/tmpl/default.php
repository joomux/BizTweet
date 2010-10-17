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

if ( $this->params->def( 'show_page_title', 1 ) ) : ?>
<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php endif; ?>

<?php
$gids = $this->params->get('canpost', null);
if (!is_array($gids)) $gids = array($gids);
if (in_array($this->user->gid, $gids) or (!$this->user->gid and in_array('29', $gids))) {
	// does the use have access to post a tweet?
	?>
	<script type="text/javascript" language="Javascript">
	window.addEvent('domready', function() {
		btCountChars($('tweet'));
		$('raw_url').addEvent('focus', function () {btCheckUrl('raw_url', '<?php echo JText::_('enter link here...'); ?>');});
		$('raw_url').addEvent('blur',  function () {btCheckUrl('raw_url', '<?php echo JText::_('enter link here...'); ?>');});
	});
	</script>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate">
		<input type="hidden" name="option" value="com_biztweet" />
		<input type="hidden" name="task" value="tweet" />
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		<input type="hidden" name="reply_id" id="reply_id" value="" />
		<table width="100%">
		<tr>
		<td width="90%">
		<textarea name="tweet" id="tweet" class="inputbox required" style="width:95%;" rows="2" maxlength="140" onkeyup="btCountChars(this);"><?php echo $this->post_prefix; ?></textarea>
		</td>
		<td class="contentheading" id="btcharcount">
			140
		</td>
		</tr>
		<tr>
		<td valign="middle">
			<input type="text" name="raw_url" id="raw_url" value="<?php echo JText::_('enter link here...'); ?>" class="inputbox" style="width:70%;" />
			&nbsp;<input type="button" class="button" onClick="return btParseUrl('raw_url', '<?php echo JText::_('enter link here...'); ?>');" value="<?php echo JText::_('Add Link'); ?>" />
		</td>
		<td valign="middle" align="center" nowrap>
			<input type="submit" id="btpost" class="button validate" value="<?php echo JText::_('Tweet!'); ?>" />
			&nbsp;<input type="button" id="btclear" class="button" value="<?php echo JText::_('Clear'); ?>" onClick="btReset('<?php echo $this->post_prefix; ?>');" />
		</td>
		</tr>
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
		BizTweetsHelperParser::display($this->replies, $this->params->get('replies_title', JText::_('@Replies')));
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
<div style="text-align: center; font-size: small; padding: 5px;" id="btfooter">
	<?php echo JHTML::link('http://www.evolutionengin.com', 'BizTweet Lite &copy; ' .date('Y'). ' EvolutionEngin', array('target' => '_blank')); ?>
</div>