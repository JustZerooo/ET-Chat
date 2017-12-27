<?php
/**
 * ChangeStatus for user blocking in the session
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class ChangeStatus extends DbConectionMaker
{
	/**
	* Constructor
	*
	* @uses ConnectDB::sqlSet()	
	* @uses ConnectDB::close()	
	* @return void
	*/
	public function __construct (){
	
		// call parent Constructor from class DbConectionMaker
		parent::__construct();
	
		session_start();
		
		// all documentc requested per AJAX should have this part to turn off the browser and proxy cache for any XHR request
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		// Set Session Var for getting Sys Messages
		if(isset($_POST['sys_messages'])){
			$_SESSION['etchat_'.$this->_prefix.'sys_messages']=$_POST['sys_messages'];
			return false;
		}
		
		// Test if this status is allowed to be used by this user
		if (!$this->checkRightsOfStatus($_POST['img'])) {
			$_POST['img']=""; 
			$_POST['text']="";
			echo "Not allowed operation.";
			return false;
		}
		
		$_POST['img']= htmlspecialchars(preg_replace("/[^a-zA-Z_0-9\-. ]/i", "", $_POST['img']), ENT_QUOTES, "UTF-8");
		$_POST['text']= htmlspecialchars(preg_replace('/[\x00-\x1F]/', '', $_POST['text']), ENT_QUOTES, "UTF-8");

		// no image is just online status
		if($_POST['img']=="status_online"){$_POST['img']=""; $_POST['text']="";}

		// if the image is not on the server, and also to prevent the sql-injections
		if (!empty($_POST['img']) && !file_exists("./img/".$_POST['img'].".png")) {$_POST['img']=""; $_POST['text']="";}
		
		// change status in the session table
		$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_useronline SET 
			etchat_user_online_user_status_img = '".addslashes($_POST['img'])."',
			etchat_user_online_user_status_text = '".addslashes(urldecode($_POST['text']))."'
			WHERE etchat_onlineuser_fid = ".(int)$_SESSION['etchat_'.$this->_prefix.'user_id']);
		
		// close DB connection
		$this->dbObj->close();
		
		echo "1";
	}
	
	/**
	* Test if this status is allowed to be used by this user
	*
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @return bool
	*/
	private function checkRightsOfStatus($statusImagename){
		
		// http://www.sedesign.de/sed/forum/forum_entry.php?id=7599
		if(substr($statusImagename, 0, 7)!='status_') return false;
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang();
		
		foreach($lang->chat_js[0]->status as $status_value) 
			if ($status_value->tagAttrs['imagename']==$statusImagename && $status_value->tagAttrs['rights']!='all')	
				$thisStatusImagenameRights[]=$status_value->tagAttrs['rights'];
		
		if (!is_array($thisStatusImagenameRights)) return true;
		else{
			if (in_array($_SESSION['etchat_'.$this->_prefix.'user_priv'], $thisStatusImagenameRights)) return true;
			else return false;
		}
	}
}