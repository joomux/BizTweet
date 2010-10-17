<?php
// no direct access
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

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

$controller	= new BizTweetsController( );

// Perform the Request task
$controller->execute( JRequest::getCmd('task'));
$controller->redirect();
