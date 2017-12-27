<?php
/**
 * Class ReloaderMessages, the AJAX Request for getting and setting new messges,  pull session and more,
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2010 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 2.0
 */
 
class ReloaderMessages extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses ConnectDB::sqlGet()	
	* @uses Blacklist object creation
	* @uses Blacklist::userInBlacklist() checks if in the Blacklist
	* @uses Blacklist::allowedToAndSetCookie()
	* @uses Blacklist::killUserSession()
	* @uses MessageInserter object creation
	* @uses MessageInserter::$status break if status is "spam"
	* @return void
	*/
	public function __construct (){ 
		
		// call parent Constructor from class DbConectionMaker
		parent::__construct(); 

		session_start();

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		header('content-type: application/json; charset=utf-8');

		// Checks if the user is in the kick list
		if ($this->checkKicklist()) { $this->errorOutput("kick"); return false; } 
		
		// create new Blacklist Object
		$blackListObj = new Blacklist($this->dbObj);
		
		// Is the curent user IP in zhe Blacklist or has the user browser an actual "black cookie"?
		if ($blackListObj->userInBlacklist()){
			if ($blackListObj->allowedToAndSetCookie()){ 
				$blackListObj->killUserSession();
				$this->errorOutput("blacklist");
				return false;
			}
		}
		
		// Get room array
		$raum_array = $this->getRoomArray();
		// it kannt happen, but if somebody tries to fool the JavaScript in chat.js ;-)
		if (!is_array($raum_array)) return false;
		
		// all needed Userdata for current user
		$user_array=$this->dbObj->sqlGet("SELECT etchat_user_id, etchat_username, etchat_userprivilegien, etchat_usersex FROM {$this->_prefix}etchat_user where etchat_user_id = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);

		// Update etchat_useronline if the session exists or create a new dataset in the table
		$this->refreshUserSession($user_array, $raum_array, $blackListObj->user_param_all);
		
		if (empty($_POST['privat'])) $_POST['privat']=0;

		// Make message
		if (isset($_POST['message']) && !empty($_POST['message']) && trim($_POST['message'])!="/window:" && !empty($_SESSION['etchat_'.$this->_prefix.'user_id'])){
			
			// create new MessageInserter Object
			$inserterObj = new MessageInserter($this->dbObj, $raum_array);
			
			// if $inserterObj->status="spam" then the user is now inserted in Blacklist and just send "spam" message to the JacaScript at AJAX
			if (!empty($inserterObj->status)) { 
				$this->errorOutput($inserterObj->status); 
				return false; 
			}
		}
		
		// selects all needed Messages to display and make a JSON output from it
		$this->makeJsonOutput($this->selectMessagesForTheUser());
	}


	/**
	* Creates the JSON-Output for AJAX-Request 
	*
	* @param Array $feld this array contains the messages to be transmitted to the user
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::close()	
	* @uses StaticMethods::filtering()
	* @return void
	*/
	private function makeJsonOutput($feld){
	
		$ausgabeJSON_Inhalt=array();

		// Get the smileys list
		$sml = $this->dbObj->sqlGet("SELECT etchat_smileys_sign, etchat_smileys_img FROM {$this->_prefix}etchat_smileys");

		// JSON creation
		if (is_array($feld)){
			$ausgabeJSON_Anfang = "{\"data\" : [";

			for ($a=0; $a < count($feld); $a++){
				// Blocking if the opponent user that is in the blocklist of user own session
				if (!$this->blockiere($feld[$a][6],$feld[$a][5])){
					
					// outputed messages counter, is used as a continuous message id in chat.js (changed since v307-Beta10)
					$message2send = addslashes(StaticMethods::filtering(stripslashes($feld[$a][2]), $sml, $this->_prefix));
					
					// private messages in extra window
					if (substr($message2send, 0, 8)=="/window:" && $feld[$a][5]!=0) {
						$message2send = substr($message2send, 8, strlen($message2send));
						$normal_message_counter = "";
					}
					else {
						$_SESSION['etchat_'.$this->_prefix.'count']++;
						$normal_message_counter = $_SESSION['etchat_'.$this->_prefix.'count'];
					}
					
					$ausgabeJSON_Inhalt[] = "{\"id\":\"".$normal_message_counter."\",\"user\":\"".(addslashes($feld[$a][1]))."\",\"user_id\":\"".(addslashes($feld[$a][6]))."\",\"message\":\"".$message2send."\",\"time\":\"".date("H:i",$feld[$a][3])."\",\"privat\":\"".$feld[$a][5]."\",\"css\":\"".$feld[$a][7]."\",\"priv\":\"".$feld[$a][8]."\",\"sex\":\"".$feld[$a][9]."\"}";
				}
			}

			$ausgabeJSON_Ende ="]}";
		}

		// close DB connection
		$this->dbObj->close();
		
		// make JSON-Output
		if (count($ausgabeJSON_Inhalt)>0) echo $ausgabeJSON_Anfang.implode(",", $ausgabeJSON_Inhalt).$ausgabeJSON_Ende;
	}
	

	
	/**
	* Every pull refreshes the user data in the session table, etchat_useronline
	*
	* @param Array $user_array requested data from user table
	* @param Array $raum_array requested data from room table
	* @param String $user_param_all User IP data for Blacklist
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::sqlSet()	
	* @return void
	*/
	private function refreshUserSession($user_array, $raum_array, $user_param_all){
	
		$user_onlineid = $this->dbObj->sqlGet("SELECT etchat_onlineid FROM {$this->_prefix}etchat_useronline where etchat_onlineuser_fid = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);

		// if the usersession was created and is now existing
		if(is_array($user_onlineid))
			$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_useronline SET
				etchat_onlineuser_fid = ".$user_array[0][0].",
				etchat_onlinetimestamp = ".date('U').",
				etchat_onlineip = '".$user_param_all."',
				etchat_fid_room = ".$raum_array[0][0].",
				etchat_user_online_room_goup = ".$raum_array[0][2].",
				etchat_user_online_room_name = '".$raum_array[0][1]."',
				etchat_user_online_user_name = '".$user_array[0][1]."',
				etchat_user_online_user_priv = '".$user_array[0][2]."',
				etchat_user_online_user_sex = '".$user_array[0][3]."'
				WHERE etchat_onlineid = ".$user_onlineid[0][0]);
				
		// the user session is not yet existing, so create it
		else {
			$this->dbObj->sqlSet("INSERT INTO {$this->_prefix}etchat_useronline ( etchat_onlineuser_fid, etchat_onlinetimestamp, etchat_onlineip, etchat_fid_room, etchat_user_online_room_goup, etchat_user_online_room_name, etchat_user_online_user_name, etchat_user_online_user_priv, etchat_user_online_user_sex)
				VALUES ( '".$user_array[0][0]."', ".date('U').", '".$user_param_all."', ".$raum_array[0][0].", ".$raum_array[0][2].", '".$raum_array[0][1]."', '".$user_array[0][1]."', '".$user_array[0][2]."', '".$user_array[0][3]."')");
			
			// if user shoul be invisible on enter
			if ($_SESSION['etchat_'.$this->_prefix.'invisible_on_enter'])
				$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_useronline SET 
					etchat_user_online_user_status_img = 'status_invisible', etchat_user_online_user_status_text = ''
					WHERE etchat_onlineuser_fid = ".(int)$_SESSION['etchat_'.$this->_prefix.'user_id']);
					
			//unset($_SESSION['etchat_'.$this->_prefix.'invisible_on_enter']);
		}
	}

	/**
	* Get all room from DB with all information
	*
	* @uses ConnectDB::sqlGet()	
	* @uses RoomAllowed 
	* @uses RoomAllowed::$room_status if the room is open/closed/pw-protected
	* @return Array 
	*/
	private function getRoomArray(){

		// Get room Array
		$raum_array=$this->dbObj->sqlGet("SELECT etchat_id_room, etchat_roomname, etchat_room_goup, etchat_room_message FROM {$this->_prefix}etchat_rooms where etchat_id_room =".(int)$_POST['room']);

		// Checks if the posted roomID exists now, it could be just deleted by admin
		if (!is_array($raum_array)) {
			$_POST['room'] = 1;
			$raum_array=$this->dbObj->sqlGet("SELECT etchat_id_room, etchat_roomname, etchat_room_goup, etchat_room_message FROM {$this->_prefix}etchat_rooms where etchat_id_room = 1");
		}
		else{
			// who ist allowed to visit this room
			$room_allowed=new RoomAllowed($raum_array[0][2], $raum_array[0][0]);
			if ($room_allowed->room_status!=1){
				$_POST['room'] = 1;
				unset($_POST['message']);
				$raum_array=$this->dbObj->sqlGet("SELECT etchat_id_room, etchat_roomname, etchat_room_goup FROM {$this->_prefix}etchat_rooms where etchat_id_room = 1");
			}
		}
		
		return $raum_array;
	}


	/**
	* Checks if the user is in the kicklist now
	*
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::sqlSet()
	* @return bool
	*/
	private function checkKicklist(){	
		
		// Get all data from the kick tab
		$kicklist=$this->dbObj->sqlGet("SELECT id from {$this->_prefix}etchat_kick_user where etchat_kicked_user_id = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);
		
		if (is_array($kicklist)){
			
			// delete the user from kicklist
			$this->dbObj->sqlSet("delete from {$this->_prefix}etchat_kick_user where etchat_kicked_user_id = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);

			$rechte_zum_kicken=$this->dbObj->sqlGet("select etchat_userprivilegien FROM {$this->_prefix}etchat_user where etchat_user_id = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);
			
			if ($rechte_zum_kicken[0][0]!="admin" && $rechte_zum_kicken[0][0]!="mod") return true;
			else return false;
		}
		else return false;
	}

	
	/**
	* Print a error message, and close db connect
	*
	* @param  string $message Outputmessage, usualy "0" (if any error)
	* @uses ConnectDB::close()
	* @return void
	*/
	private function errorOutput($message=0){
		echo $message; 
		$this->dbObj->close();
	}

	
	/**
	* Creates a dataset with all needed messages for the user
	*
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::sqlSet()
	* @return Array
	*/
	private function selectMessagesForTheUser(){
			
			
		// on first message / on entrance
		if (empty($_SESSION['etchat_'.$this->_prefix.'last_id'])) {
		
			if (isset($_SESSION['etchat_'.$this->_prefix.'sys_messages']) && !$_SESSION['etchat_'.$this->_prefix.'sys_messages']){
				$where_sys_messages = "(etchat_user_fid<>1 or (etchat_user_fid=1 and etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id'].")) and ";
				$this->_messages_shown_on_entrance--;
			}	
		
			// checks if the own last_id is realy the last one (new since v307-Beta10)
			$counted_ids=$this->dbObj->sqlGet("SELECT count(etchat_id) FROM {$this->_prefix}etchat_messages WHERE etchat_id > ".$_SESSION['etchat_'.$this->_prefix.'my_first_mess_id']. " and (etchat_fid_room = ".(int)$_POST['room']." or etchat_fid_room = 0) and (etchat_privat=0 or etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id'].")");
			if (is_array($counted_ids) && $counted_ids[0][0]>=$this->_messages_shown_on_entrance) $this->_messages_shown_on_entrance+=$counted_ids[0][0];
			
			// get all messages
			$feld=$this->dbObj->sqlGet("SELECT etchat_id, etchat_username, etchat_text, etchat_timestamp, etchat_fid_room, etchat_privat, etchat_user_id, etchat_text_css, etchat_userprivilegien, etchat_usersex 
				FROM {$this->_prefix}etchat_messages, {$this->_prefix}etchat_user where (etchat_fid_room = ".(int)$_POST['room']." or etchat_fid_room = 0 or etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id'].") and ".$where_sys_messages."
				(etchat_privat=0 or etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id']." or etchat_user_fid=".$_SESSION['etchat_'.$this->_prefix.'user_id'].") and etchat_user_id=etchat_user_fid ORDER BY etchat_id DESC LIMIT ".$this->_messages_shown_on_entrance);
			
			// Set last DB id
			$_SESSION['etchat_'.$this->_prefix.'last_id'] = $feld[0][0];
			
			$feld = array_reverse($feld);
		}
		else {
			
			if (isset($_SESSION['etchat_'.$this->_prefix.'sys_messages']) && !$_SESSION['etchat_'.$this->_prefix.'sys_messages']){
				$where_sys_messages = "(etchat_user_fid<>1 or (etchat_user_fid=1 and etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id'].")) and ";
			}	
		
			// get all messages
			$feld=$this->dbObj->sqlGet("SELECT etchat_id, etchat_username, etchat_text, etchat_timestamp, etchat_fid_room, etchat_privat, etchat_user_id, etchat_text_css, etchat_userprivilegien, etchat_usersex 
				FROM {$this->_prefix}etchat_messages, {$this->_prefix}etchat_user WHERE (etchat_fid_room = ".(int)$_POST['room']." or etchat_fid_room = 0 or etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id'].")
				and etchat_id > ".$_SESSION['etchat_'.$this->_prefix.'last_id']." and ".$where_sys_messages."
				(etchat_privat=0 or etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id']." or etchat_user_fid=".$_SESSION['etchat_'.$this->_prefix.'user_id'].")
				and etchat_user_id=etchat_user_fid ORDER BY etchat_id ");
			
			if (is_array($feld)) $_SESSION['etchat_'.$this->_prefix.'last_id']= $feld[(count($feld)-1)][0];
			else
			// DE
			// Das ist wichtig hier die last_id aus der DB auszulesen sogar wenn für das Raum in bem sich der User befindet keine
			// neuen Messages gab. Sonst bleibt das last_id das alte und beim Raumwechsel kanns passieren, dass alle sonstigen Messages
			// aus dem Raum in den gewächselt wurde, ausgegeben werden.
			
			// EN
			// It is importent to get the last_id from the DB, even there is no messges for the user. Othewise it kan happen that
			// when the user is going to the other chat room hi's got all messges from this room
			{
				$id=$this->dbObj->sqlGet("SELECT etchat_id FROM {$this->_prefix}etchat_messages ORDER BY etchat_id DESC LIMIT 1");
				$_SESSION['etchat_'.$this->_prefix.'last_id']=$id[0][0];
			}
		}
		return $feld;
	}


	/**
	* Blocking if the opponent user that is in the blocklist of user own session
	*
	* @param int $user_id 
	* @param int $privat_id 
	* @return void
	*/
	private function blockiere($user_id, $privat_id){
		if (is_array ($_SESSION['etchat_'.$this->_prefix.'block_all']) && in_array($user_id, $_SESSION['etchat_'.$this->_prefix.'block_all'])) return true;
		if (is_array ($_SESSION['etchat_'.$this->_prefix.'block_priv']) && in_array($user_id, $_SESSION['etchat_'.$this->_prefix.'block_priv']) && $privat_id==$_SESSION['etchat_'.$this->_prefix.'user_id']) return true;
	}

	
}		