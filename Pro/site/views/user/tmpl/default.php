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
<!-- div id="bt_search_area" style="text-align:right;">
	<input type="text" name="btsearch" id="searchbt" class="inputbox" value="<?php echo JText::_('search...'); ?>" style="width:200px;" />
	<?php echo JHTML::image('components/com_biztweet/assets/magnifier.png', JText::_('Search'), array('type' => 'button', 'class' => 'button', 'onClick' => "return btAddSearch('searchbt', '" .JText::_('search...'). "');")); ?>
</div -->
<div id="btsource"></div>
<?php
$gids = $this->params->get('canpost', array());
if (!empty($gids) and !is_array($gids)) $gids = array($gids);
if (in_array($this->user->gid, $gids) or (!$this->user->gid and in_array('29', $gids))) {
	// does the use have access to post a tweet?
	if ($this->btuser->username and $this->btuser->password) {
		// hide login
		$loginStyle = "none";
		$loggedInStyle = "block";
		$disabled = "disabled";
		$loginRequired = "";
	} else {
		// show login
		$loginStyle = "block";
		$loggedInStyle = "none";
		$disabled = "";
		$loginRequired = " required";
	}
	?>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate" enctype="multipart/form-data" id="btForm">
		<input type="hidden" name="option" value="com_biztweet" />
		<input type="hidden" name="task" value="tweet" />
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		<input type="hidden" name="reply_id" id="reply_id" value="" />
		<div id="btlogin" style="display:<?php echo $loginStyle; ?>">
		<?php echo JText::_('Username'); ?>&nbsp;<input type="text" class="inputbox<?php echo $loginRequired; ?>" id="btusername" name="btusername" value="<?php echo $this->btuser->username; ?>" <?php echo $disabled; ?> />
		&nbsp;
		<?php echo JText::_('Password'); ?>&nbsp;<input type="password" class="inputbox<?php echo $loginRequired; ?>" id="btpassword" name="btpassword" value="" <?php echo $disabled; ?> />
		</div>
		<div id="btloggedin" style="display:<?php echo $loggedInStyle; ?>">
		<?php echo JText::_('Logged in as') ?>
		<?php echo JHTML::link('http://twitter.com/' .$this->btuser->username, '<strong>'.$this->btuser->username.'</strong>', array('title' => JText::_('View Profile'))); ?>
		(<?php echo JHTML::link("javascript:btShowLogin();", JText::_('change'), array('title' => JText::_('Update Login Details'))); ?>)
		</div>
		<table width="100%">
		<tr>
		<td width="90%">
		<textarea name="tweet" id="tweet" class="inputbox required validate-hashtags" style="width:95%;" rows="2" maxlength="140" onkeyup="btCountChars(this);"></textarea>
		</td>
		<td class="contentheading" id="btcharcount">140</td>
		</tr>
		<?php if ($this->params->get('hashtags', '')) : ?>
		<tr>
		<td id="bthashtags">
			<?php echo $this->params->get('requiretags', 0) ? JText::_('Required') : JText::_('Optional'); ?>
			<?php echo JText::_('Tags'); ?>:
			<?php
			$tags = explode(' ', $this->params->get('hashtags', ''));
			$cleantags = array_map('trim', $tags);
			$hashtags = array_unique($cleantags);
			sort($hashtags);
			foreach ($hashtags as $tag) {
				$tag = trim($tag);
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
		<td valign="middle" nowrap>
			<input type="text" name="raw_url" id="raw_url" value="<?php echo JText::_('ENTER LINK HERE'); ?>" class="inputbox" style="width:65%;" />
			&nbsp;<input type="button" class="button" onClick="return btParseUrl('raw_url', '<?php echo JText::_('ENTER LINK HERE'); ?>');" value="<?php echo JText::_('Add Link'); ?>" style="width:25%;" id="btlinkbtn" />&nbsp;<?php echo JHTML::image(JURI::base().'components/com_biztweet/assets/loading.gif', JText::_('Loading...'), array('style' => 'visibility:hidden;', 'align' => 'absmiddle', 'id' => 'btlinkload')); ?>
		</td>
		<td valign="middle" align="center" nowrap>
			<input type="submit" id="btpost" class="button validate" value="<?php echo JText::_('Tweet!'); ?>" />
			&nbsp;<input type="button" id="btclear" class="button" value="<?php echo JText::_('Clear'); ?>" onClick="btReset();" />
		</td>
		</tr>
		<?php if ($this->params->get('twitpic', 1)) : ?>
		<tr>
		<td valign="middle">
			<input type="file" name="twitpic" id="twitpic" value="Upload Image" />
			&nbsp;<?php echo JHTML::tooltip(JText::_('Upload an image with TwitPic'), JText::_('Upload')); ?>
			&nbsp;<input type="button" class="button" onClick="btUpload('btForm', 'twitpic');" value="<?php echo JText::_('Upload File'); ?>" />
		</td>
		<td>&nbsp;</td>
		</tr>
		<?php endif; ?>
		<tr>
		<td colspan="2" nowrap>
			<div id="bt_search_area">
				<input type="text" name="btsearch" id="searchbt" class="inputbox" value="<?php echo JText::_('SEARCHDOT'); ?>" style="width:95%;" />&nbsp;<?php echo JHTML::image('components/com_biztweet/assets/magnifier.png', JText::_('Search'), array('type' => 'button', 'class' => 'button', 'onClick' => "return btAddSearch('searchbt', '" .JText::_('SEARCHDOT'). "');")); ?>
			</div>
		</td>
		</tr>
		</table>
	</form>
	<?php
} else {
	if ($this->params->get('allow_search', 1) and $this->user->id) {
		?>
		<div id="bt_search_area">
			<input type="text" name="btsearch" id="searchbt" class="inputbox" value="<?php echo JText::_('SEARCHDOT'); ?>" style="width:95%;" />&nbsp;<?php echo JHTML::image('components/com_biztweet/assets/magnifier.png', JText::_('Search'), array('type' => 'button', 'class' => 'button', 'onClick' => "return btAddSearch('searchbt', '" .JText::_('SEARCHDOT'). "');")); ?>
		</div>
		<?php
	} else {
		?><span class="alert"><?php echo JText::_('You do not have permission to access this resource.'); ?></span><?php
	}
}
?>
<div id="btsearchresults">
<?php
for ($i=1; $i<4; $i++) {
	$key = "search" .$i;
	$value = $this->params->get($key, '');
	if ($value) {
		$term_id = preg_replace('/\+/', '__', urlencode(preg_replace('/\#/', '__', $value)));
		?>
		<div style="float:left;width:<?php echo $this->colwidth; ?>%;" id="wrap<?php echo $term_id; ?>">
		<div class="contentheading">
			<a href="javascript:btRemoveSearch('bt<?php echo $term_id; ?>');" title="<?php echo JText::_('Remove Search'); ?>">
			<?php echo JHTML::image('components/com_biztweet/assets/delete.png', JText::_('Remove Search')); ?>
			</a> <?php echo $value; ?></div>
		<div id="btload_<?php echo $term_id; ?>" style="text-align:center;">
			<?php echo JHTML::image('components/com_biztweet/assets/loading.gif', JText::_('SEARCHINGDOT')); ?>
		</div>
		<ul class="timeline btsearchbox" title="<?php echo $value; ?>" id="bt<?php echo $term_id; ?>">
		</ul>
		</div>
		<?php
	}
}
?>
</div>
<div style="clear:left;"></div>
