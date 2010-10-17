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
?>
<form action="index.php" method="post" name="adminForm">
	<input type="hidden" name="option" value="com_biztweet" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="hidemainmenu" value="" />
	<input type="hidden" name="boxchecked" value="" />

	<div class="col width-50">
		<h1><?php echo JText::_('HEADING'); ?></h1>
		<p><?php echo JHTML::link("http://www.evolutionengin.com", "&copy; " .date('Y'). " EvolutionEngin", array("target" => "_blank")); ?></p>
		<p><?php echo JHTML::link('http://twitter.com', 'Twitter.com', array('target' => '_blank')); ?> enforces a limit of 100 requests per hour for a single account. For this reason, and the speed up page loading, a caching system is in use for BizTweet. You can configure how long to cache results for when configuring a Joomla! menu item for BizTweet.</p>
		<p><?php echo JHTML::link('http://twitter.com', 'Twitter.com', array('target' => '_blank')); ?> also enforces a limit of 140 characters per tweet. A handy character counter displays the number of characters remaining out of the allowed 140. If more than 140 characters are entered, you will not be able to submit the tweet.</p>
		<p>You can easily add a link to your Tweets with the 'Add Link' button. Simply enter or paste the full URL of your link in the 'enter link here...' field and click the 'Add Link' button. This will use an external service to shorten the link to save valuable tweeting space.</p>
		<h2>Twitter Streams</h2>
		<p>This application allows you to display the twenty most recent posts from the following sections. Each stream/timeline will display the twenty most recent tweets, with the most recent appearing at the top.</p>
		<h3>Friends Timeline</h3>
		<p>This is the standard Twitter feed which includes your posts and those of the people you are following.</p>
		<h3>Replies and Mentions</h3>
		<p>Any post that contains @your_user_name. These may be made by anyone and are often used when someone ReTweets one of your posts.</p>
		<h3>User Timeline</h3>
		<p>This stream contains only those posts from your account.</p>
		<h2>Configuration</h2>
		<p>There are a number of configurable options available. Each option is available when creating, or editing, a Joomla! menu item for the BizTweet component.</p>
		<p>These options include configuring which stream(s) to display, what permission level is required to post a message, showing a 'Follow Me' image, customising stream titles, displaying your most recent tweet, how long to cache results for and more!</p>
		<h2>Copyright</h2>
		<p>This component, including its variants are Copyright &copy; <?php echo date('Y'); ?>. No part of this component may be copied, modified, or redistributed without prior written consent from the <?php echo JHTML::link('mailto:sales@jtips.com.au', 'author'); ?>.</p>
		<h2>Thanks To</h2>
		<p>BizTweet uses the following utilities, libraries and services:</p>
		<ul>
			<li><?php echo JHTML::link("http://www.phpclasses.org/browse/package/4335.html", "twitterlibphp", array('target' => '_blank')); ?> - by Justin Poliey</li>
			<li><?php echo JHTML::link("http://tr.im", "tr.im", array('target' => '_blank')); ?> - URL Shortening Service</li>
			<li><?php echo JHTML::link("http://tinyurl.com", "tinyurl", array('target' => '_blank')); ?> - URL Shortening Service</li>
			<li><?php echo JHTML::link("http://twitter.com", "Twitter", array('target' => '_blank')); ?></li>
		</ul>
		<p><?php echo JHTML::link("http://www.evolutionengin.com", "&copy; " .date('Y'). " EvolutionEngin", array("target" => "_blank")); ?> All rights reserved.</p>
	</div>
	<div class="col width-50">
		<fieldset>
			<legend><?php echo JText::_('Update CSS'); ?></legend>
			<p><?php echo JText::_('Only edit this file if you know what you are doing.'); ?></p>
			<p><textarea class="inputbox" style="width:100%" rows="50" name="css" id="css"><?php echo $this->css; ?></textarea></p>
		</fieldset>
	</div>
</form>
