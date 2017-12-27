<?php
/**
 * Class SysMessage, just a method to set sys messages
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class SysMessage extends EtChatConfig
{
	
	/**
	* DB-Connection Obj
	* @var ConnectDB
	*/
	protected $dbObj;
	
	/**
	* Last ID from an inserted message into db
	* @var int
	*/
	public $lastInsertedId;
	
	/**
	* Constructor
	*
	* @param  ConnectDB $dbObj, Obj with the db connection handler
	* @param  string $message, message text
	* @param  int $room_fid, room id (0= at all rooms)
	* @param  int $privat, user id (0= at all user in this room)
	* @uses ConnectDB::sqlSet()
	* @uses ConnectDB::$lastId
	* @return int, the inserted message dataset id from db
	*/
	public function __construct ($dbObj, $message, $room_fid, $privat){	
		
		// call parent Constructor from class EtChatConfig
		parent::__construct();
		
		$dbObj->sqlSet("INSERT INTO {$this->_prefix}etchat_messages ( etchat_user_fid, etchat_text, etchat_text_css, etchat_timestamp, etchat_fid_room, etchat_privat, etchat_user_ip)
		VALUES ( 1, '".$message."', 'color:#".$_SESSION['etchat_'.$this->_prefix.'syscolor'].";font-weight:normal;font-style:normal;', '".date('U')."', ".$room_fid.", ".$privat.", '".$_SERVER['REMOTE_ADDR']."')");
		
		// unfortunately the PDO::lastInsertId() just works on MySQL and SQLITE, but not on PGSQL
		if (!empty($dbObj->lastId)) $this->lastInsertedId = $dbObj->lastId;
		else {
			$lastID = $dbObj->sqlGet("SELECT max(etchat_id) from {$this->_prefix}etchat_messages");
			$this->lastInsertedId = $lastID[0][0];
		}
	}
}