<?php
/**
 * Class AdminDeleteRoom
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminUpdateRoom extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @uses ConnectDB::sqlSet()	
	* @uses ConnectDB::close()	
	* @return void
	*/
	public function __construct (){ 
		
		// call parent Constructor from class DbConectionMaker
		parent::__construct(); 

		session_start();

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		// Sets charset and content-type for index.php
		header('content-type: text/html; charset=utf-8');
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->admin[0]->admin_rooms[0];
		
		
		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin" && !empty($_POST['id'])){
			
			if ($_POST['room_priv']==3)
				$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_rooms SET etchat_roomname = '".$_POST['room']."', etchat_room_goup = ".$_POST['room_priv'].", etchat_room_pw = '".$_POST['roompw']."', etchat_room_message = '".$_POST['roommessage']."' WHERE etchat_id_room = ".(int)$_POST['id']);
			else 
				$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_rooms SET etchat_roomname = '".$_POST['room']."', etchat_room_goup = ".$_POST['room_priv'].",  etchat_room_pw = NULL, etchat_room_message = '".$_POST['roommessage']."' WHERE etchat_id_room = ".(int)$_POST['id']);
			
			$this->dbObj->close();
			header("Location: ./?AdminRoomsIndex");
			
		}else{
			echo $lang->error[0]->tagData;
			return false;
		}
	}
}