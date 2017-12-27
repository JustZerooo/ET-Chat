<?php
/**
 * CheckRoomPw, checks the PW for PW-protected rooms
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class CheckRoomPw extends DbConectionMaker
{
	/**
	* Constructor
	*
	* @uses ConnectDB::sqlGet()	
	* @return void
	*/
	public function __construct (){
	
		// call parent Constructor from class DbConectionMaker
		parent::__construct();
	
		session_start();
		
		// all documentc requested per AJAX should have this part to turn off the browser and proxy cache for any XHR request
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		$freigabe=$this->dbObj->sqlGet("SELECT etchat_id_room FROM {$this->_prefix}etchat_rooms WHERE etchat_id_room = ".(int)$_POST['roomid']." AND etchat_room_pw = '".addslashes($_POST['layerpw'])."'");
		
		if (!is_array($freigabe)) echo "wrong";
		else{
			$_SESSION['etchat_'.$this->_prefix.'roompw_array'][]=$freigabe[0][0];
			echo "1";
		}
		
		// close DB connection
		$this->dbObj->close();
	}
}