<?php
/**
 * UnregisterPw, remove password-protection
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2013 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Beta 13
 */
 
class UnregisterPw extends DbConectionMaker
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
				
		$userprivilegien = $this->dbObj->sqlGet("select etchat_userprivilegien, etchat_userpw from {$this->_prefix}etchat_user WHERE etchat_user_id = ".(int)$_SESSION['etchat_'.$this->_prefix.'user_id']);
		
		if ($userprivilegien[0][0]=="admin" || $userprivilegien[0][0]=="mod" || $userprivilegien[0][0]=="user"){
			if($userprivilegien[0][1]==md5($_POST['user_pw'])){
				$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_user SET 
					etchat_userpw = NULL,  
					etchat_userprivilegien = 'gast',
					etchat_reg_timestamp = '0000-00-00 00:00:00',
					etchat_reg_ip = NULL
					WHERE etchat_user_id = ".(int)$_SESSION['etchat_'.$this->_prefix.'user_id']);
				echo "1";
			} else {
				// create new LangXml Object
				$langObj = new LangXml();
				$lang=$langObj->getLang()->unregisterpw_php[0];	
				echo "<b>".$lang->warning[0]->tagData."</b><br>".$lang->warning[1]->tagData;
			}
		} else {
			echo "No, no... ;-)";
		}
		// close DB connection
		$this->dbObj->close();
	}
}