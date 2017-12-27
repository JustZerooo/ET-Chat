<?php
/**
 * Class Blacklist checks if the user is allowed to be in the chat and inserts the user to the blacklist
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class Blacklist extends EtChatConfig
{
	/**
	* DB-Connection Obj
	* @var ConnectDB
	*/
	private $dbObj;
	
	/**
	* User IP Key
	* @var String
	*/
	public $user_param_all;
	
	/**
	* Time until the user is banned
	* @var String
	*/
	public $user_bann_time;
	
	/**
	* Constructor
	*
	* @param  ConnectDB $dbObj, Obj with the db connection handler
	* @return void
	*/
	public function __construct ($dbObj){ 
	
		// call parent Constructor from class EtChatConfig
		parent::__construct();
		
		$this->dbObj = $dbObj;
		
		// new since v307-Beta10
		$this->user_param_all = $_SERVER['REMOTE_ADDR']."@".@gethostbyaddr($_SERVER['REMOTE_ADDR']);
	}
	
	/**
	* UserInBlacklist,  checks if the curent user IP in zhe Blacklist or has the user browser an actual "black cookie"
	*
	* @uses ConnectDB::sqlGet()	
	* @return bool
	*/
	public function userInBlacklist(){	

		//look first for a "black cookie". If is set, compare it with the actual datasets in the etchat_blacklist tab
		if(isset($_COOKIE['cookie_etchat_blacklist_ip']) && isset($_COOKIE['cookie_etchat_blacklist_until']))
			$blacklist_c=$this->dbObj->sqlGet("SELECT etchat_blacklist_time FROM {$this->_prefix}etchat_blacklist WHERE etchat_blacklist_ip = '".addslashes($_COOKIE['cookie_etchat_blacklist_ip'])."' and etchat_blacklist_time = ".(int)$_COOKIE['cookie_etchat_blacklist_until']." and etchat_blacklist_time > ".date('U'));
		
		// just compare by IP 
		//echo "SELECT etchat_blacklist_time FROM {$this->_prefix}etchat_blacklist WHERE etchat_blacklist_ip = '".$this->user_param_all."' and etchat_blacklist_time > ".date('U');
		$blacklist=$this->dbObj->sqlGet("SELECT etchat_blacklist_time FROM {$this->_prefix}etchat_blacklist WHERE etchat_blacklist_ip = '".$this->user_param_all."' and etchat_blacklist_time > ".date('U'));
		
		if (is_array($blacklist)) $this->user_bann_time = $blacklist[0][0];
		if (is_array($blacklist_c)) $this->user_bann_time = $blacklist_c[0][0];
		
		// if the user is banned, destroy the session and return true
		if (is_array($blacklist) || is_array($blacklist_c)) return true;
		else return false;
	}
	
	/**
	* AllowedToAndSetCookie, return true if done
	*
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::sqlSet()	
	* @return bool
	*/
	public function allowedToAndSetCookie(){
		$rechte_zum_sperren=$this->dbObj->sqlGet("select etchat_userprivilegien FROM {$this->_prefix}etchat_user where etchat_user_id = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);
		if ($rechte_zum_sperren[0][0]!="admin" && $rechte_zum_sperren[0][0]!="mod"){
			$this->dbObj->sqlSet("DELETE FROM {$this->_prefix}etchat_useronline WHERE etchat_onlineuser_fid = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);
			setcookie("cookie_etchat_blacklist_until", $this->user_bann_time, $this->user_bann_time, "/"); 
			setcookie("cookie_etchat_blacklist_ip", $this->user_param_all, $this->user_bann_time, "/");
			return true;
		}
		else return false;
	}	
	
	/**
	* insertUser into the blacklist table
	*
	* @param  int $userID
	* @param  int $time, Unix time
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::sqlSet()	
	* @return bool
	*/
	public function insertUser($userID,$time){
		$rechte_zum_sperren=$this->dbObj->sqlGet("select etchat_userprivilegien FROM {$this->_prefix}etchat_user where etchat_user_id = ".$userID);
		if ($rechte_zum_sperren[0][0]!="admin" && $rechte_zum_sperren[0][0]!="mod"){
			$ip=$this->dbObj->sqlGet("SELECT etchat_onlineip FROM {$this->_prefix}etchat_useronline WHERE etchat_onlineuser_fid = ".$userID);
			$time_to_hold = date("U")+$time; 
			$this->dbObj->sqlSet("INSERT INTO {$this->_prefix}etchat_blacklist (etchat_blacklist_ip, etchat_blacklist_userid, etchat_blacklist_time) VALUES ('".$ip[0][0]."', ".$userID.", ".$time_to_hold.")");
			return true;
		}
		else return false;
	}	
	
	/**
	* killUserSession, if the user is in blacklist
	*
	* @return void
	*/
	public function killUserSession(){	
		@session_unset();
		@session_destroy();
	}
}