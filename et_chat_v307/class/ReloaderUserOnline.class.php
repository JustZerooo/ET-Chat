<?php
/**
 * Class ReloaderUserOnline, generate userOnline-JSON
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class ReloaderUserOnline extends DbConectionMaker
{
	/**
	* Constructor
	*
	* @uses ConnectDB::sqlGet()
	* @uses ConnectDB::sqlSet()	
	* @uses ConnectDB::close()	
	* @return void
	*/
	public function __construct (){
	
		// call parent Constructor from class DbConectionMaker
		parent::__construct();
		
		// starts session
		session_start();
		
		// Disable Browser Chache
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		// JSON Content
		header('content-type: application/json; charset=utf-8');
		
		// delete all old datasets from the etchat_useronline table (session table)
		if (!empty($_SESSION['etchat_'.$this->_prefix.'config_reloadsequenz']))
			$this->dbObj->sqlSet("DELETE FROM {$this->_prefix}etchat_useronline WHERE etchat_onlinetimestamp < ".(date('U')-(((int)$_SESSION['etchat_'.$this->_prefix.'config_reloadsequenz']/1000)*4)));

		// Get all Session from the etchat_useronline with all user sessions
		$feld=$this->dbObj->sqlGet("SELECT
		etchat_user_online_user_name,
		etchat_user_online_room_name,
		etchat_fid_room,
		etchat_onlineuser_fid,
		etchat_onlineip,
		etchat_user_online_user_priv,
		etchat_user_online_room_goup,
		etchat_user_online_user_sex,
		etchat_user_online_user_status_img,
		etchat_user_online_user_status_text 
		FROM {$this->_prefix}etchat_useronline
		ORDER BY etchat_fid_room, etchat_user_online_user_name");

		// Get only empty rooms becouse this rooms are not cointained in the session-tab etchat_useronline
		$roomarray=$this->dbObj->sqlGet("SELECT etchat_id_room, etchat_roomname, etchat_room_goup FROM {$this->_prefix}etchat_rooms
		WHERE etchat_id_room NOT IN (SELECT DISTINCT etchat_fid_room FROM {$this->_prefix}etchat_useronline)");
		
		if (!is_array($feld)) $this->errorOutput(0);
		else{
			// Sometimes the user not instandly setted in the DB. So if your own session ist not found hust retorn '0' and the chat.js will make a new request.
			if (!$this->checkIfUserJustInserted($feld)) $this->errorOutput(0);
			else{
				// all the user online data will be settet to the tmp-session Var. If by the next turn there are no changes in the online-list, the response of
				// reloaderUserOnline.php will be empty, so no unnecessary traffic will be made
				if (!$this->anyChangesSinceLastPolling($feld,$roomarray)) $this->dbObj->close();
				// make the JSON-response
				else $this->makeJsonOutput($feld,$roomarray);
			}
		}
	}
	
	/**
	* Checks if there where any changes since the last request of this document
	*
	* @param  array $feld Dataset from etchat_useronline
	* @param  array $roomarray Dataset from etchat_rooms if there any empty rooms
	* @return bool
	*/
	private function anyChangesSinceLastPolling($feld,$roomarray){
		
		// create one string with all DB-params
		for ($a=0; $a < count($feld); $a++)
			$rel .= $feld[$a][1].$feld[$a][2].$feld[$a][3].$feld[$a][5].$feld[$a][8];
		for ($a=0; $a < count($roomarray); $a++)
			$rel .= $roomarray[$a][0].$roomarray[$a][1];
		
		// add the blocking parameters to the same string
		if (is_array ($_SESSION['etchat_'.$this->_prefix.'block_all'])) $rel .= implode("-",$_SESSION['etchat_'.$this->_prefix.'block_all']);
		if (is_array ($_SESSION['etchat_'.$this->_prefix.'block_priv'])) $rel .= implode("-",$_SESSION['etchat_'.$this->_prefix.'block_priv']);
		if (is_array ($_SESSION['etchat_'.$this->_prefix.'roompw_array'])) $rel .= implode("-",$_SESSION['etchat_'.$this->_prefix.'roompw_array']);

		// equalize the actual made string with the string saved at last turn/pull
		if($_SESSION['etchat_'.$this->_prefix.'reload_user_anz'] == $rel) return false;
		else {
		
			// Save the actual string to the session
			$_SESSION['etchat_'.$this->_prefix.'reload_user_anz'] = $rel;
			return true;
		}
	}
	
	/**
	* Checks if the user is actualy in the DB - etchat_useronline - session table
	*
	* @param  array $feld Dataset from etchat_useronline
	* @return bool
	*/
	private function checkIfUserJustInserted($feld){
	
		$benutzer_id_in_db = 0;
		for ($a=0; $a < count($feld); $a++)	
			if ($feld[$a][3]==$_SESSION['etchat_'.$this->_prefix.'user_id']) $benutzer_id_in_db++;

		if($benutzer_id_in_db!=1) return false;
		else return true;

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
	* Send a JSON Message at chat.js, and close db connect
	*
	* @param  array $feld Dataset from etchat_useronline Tab
	* @param  array $roomarray dataset from etchat_rooms Tab contains just all empty room.
	* @uses RoomAllowed 
	* @uses RoomAllowed::$room_status if the room is open/closed/pw-protected
	* @return void
	*/
	private function makeJsonOutput($feld,$roomarray){
	
		// creating the JSON output vor userOnline
		echo"{ \"userOnline\" : [\n";
		
		// turn for creating every user data
		for ($a=0; $a < count($feld); $a++){

			// strikethrough the user name if this user ist blocked by you
			if (is_array ($_SESSION['etchat_'.$this->_prefix.'block_all']) && in_array($feld[$a][3], $_SESSION['etchat_'.$this->_prefix.'block_all'])) $feld[$a][0]="<strike>".$feld[$a][0];
			if (is_array ($_SESSION['etchat_'.$this->_prefix.'block_priv']) && in_array($feld[$a][3], $_SESSION['etchat_'.$this->_prefix.'block_priv'])) $feld[$a][0]="<strike>".$feld[$a][0];

			// who ist allowed to visit this room
			if($feld[$a][2]!=$last_room_id){
				$room_allowed = new RoomAllowed($feld[$a][6], $feld[$a][2]);
				$room_status = $room_allowed->room_status;
				$last_room_id = $feld[$a][2];
			}
	
			// Put the chat status in yourSession
			if ($_SESSION['etchat_'.$this->_prefix.'user_id']==$feld[$a][3]) $_SESSION['etchat_'.$this->_prefix.'userstatus'] = $feld[$a][8];

			if ($a>0) echo "\n,\n";

			echo"{";
			echo"\"user\": \"".addslashes($feld[$a][0])."\",\"user_id\": \"".$feld[$a][3]."\",";
			echo"\"user_priv\": \"".$feld[$a][5]."\",\"user_sex\": \"".$feld[$a][7]."\",\"user_simg\": \"".$feld[$a][8]."\",";
			echo"\"user_stext\": \"".$feld[$a][9]."\",\"room_id\": \"".$feld[$a][2]."\",\"room_allowed\": \"".$room_status."\",";
			echo"\"room\": \"".addslashes($feld[$a][1])."\"";
			
			// this data is only for admin and mod.  Its importand for blacklist
			if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin" || $_SESSION['etchat_'.$this->_prefix.'user_priv']=="mod") 
				echo",\"user_ip\": \"".addslashes(str_replace("@"," - ",$feld[$a][4]))."\"";
			echo"}";
		}

		echo "\n]";
		
		// now all the empty rooms. so the room the no any user in
		if (is_array($roomarray)){
			
			echo ",\n\n \"all_empty_rooms\" : [\n";
			
			// turn for creating every empty room data
			for ($a=0; $a < count($roomarray); $a++){

				// who ist allowed to visit this room
				$room_allowed2 = new RoomAllowed($roomarray[$a][2], $roomarray[$a][0]);

				if ($a>0) echo "\n,\n";
				echo"{\"room_id\": \"".(addslashes($roomarray[$a][0]))."\",\"room_allowed\": \"".$room_allowed2->room_status."\",\"room\": \"".(addslashes($roomarray[$a][1]))."\"}";
			}
			echo "\n]";
		}		
		echo "\n}";
	}
}