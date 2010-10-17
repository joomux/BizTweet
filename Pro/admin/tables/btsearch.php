<?php
/**
* @version		$Id: weblink.php 11253 2008-11-10 23:38:48Z ircmaxell $
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
* BTSearch Table class
*
* @package		Joomla
* @subpackage	Weblinks
* @since 1.0
*/
class JTableBTSearch extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * @var int
	 */
	var $userid = null;

	/**
	 * @var string
	 */
	var $search = null;

	/**
	 * @var datetime
	 */
	var $date_modified = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db) {
		parent::__construct('#__btsearches', 'id', $db);
	}
}
