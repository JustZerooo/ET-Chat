<?php
/**
 * Abstract Class EtChatConfig contains vars for inheritance
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

abstract class EtChatConfig
{
	protected $_database;
	protected $_sqlhost;
	protected $_sqluser;
	protected $_sqlpass;
	
	protected $_prefix;
	
	protected $_usedDatabase;
	protected $_usedDatabaseExtension;

	protected $_messages_shown_on_entrance;
	protected $_limit_logins_in_three_minutes;
	protected $_allowed_privates_in_chat_win;
	protected $_allowed_privates_in_separate_win;
	protected $_show_history_all_user;
	protected $_interval_for_inactivity;

	protected $_allow_nick_registration;
	
	/**
	* Constructor
	*
	* @return void
	*/
	protected function __construct (){
	
		error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
		
		// Require the config.php
		if (isset($GLOBALS["path"]))
			require ($GLOBALS["path"].'config.php');
		else 
			require ('./config.php');

		
		$this->_database=$database;
		$this->_sqlhost=$sqlhost;
		$this->_sqluser=$sqluser;
		$this->_sqlpass=$sqlpass;
		$this->_prefix=$prefix;
		$this->_usedDatabase=$usedDatabaseType;
		$this->_usedDatabaseExtension=$usedDatabaseExtension;
		$this->_messages_shown_on_entrance=$messages_shown_on_entrance;
		$this->_limit_logins_in_three_minutes=$limit_logins_in_three_minutes;
		$this->_allowed_privates_in_chat_win=$allowed_privates_in_chat_win;
		$this->_allowed_privates_in_separate_win=$allowed_privates_in_separate_win;
		$this->_show_history_all_user=$show_history_all_user;
		$this->_interval_for_inactivity=$interval_for_inactivity;
		$this->_allow_nick_registration=$allow_nick_registration;
		$this->_messages_sound=$messages_sound;
		
		if(!isset($_SESSION['etchat_'.$this->_prefix.'sys_messages']))
			$_SESSION['etchat_'.$this->_prefix.'sys_messages']=$show_sys_messages;
	}
}
