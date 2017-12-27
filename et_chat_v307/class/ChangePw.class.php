<?php
/**
 * ChangePw, sets new Pw
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class ChangePw extends DbConectionMaker
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
		
		$userprivilegien = $this->dbObj->sqlGet("select etchat_userprivilegien from {$this->_prefix}etchat_user WHERE etchat_user_id = ".(int)$_SESSION['etchat_'.$this->_prefix.'user_id']);
		
		if ($userprivilegien[0][0]=="admin" || $userprivilegien[0][0]=="mod" || $userprivilegien[0][0]=="user"){
			
			if(!empty($_POST['modpw'])){
				$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_user SET etchat_userpw = '".md5($_POST['modpw'])."' WHERE etchat_user_id = ".(int)$_SESSION['etchat_'.$this->_prefix.'user_id']);
				echo "1";
			} else 
				echo "Error! You shouldn't be here.";
		}

		else if ($this->_allow_nick_registration && $_SESSION['etchat_'.$this->_prefix.'user_priv']=="gast" && !empty($_POST['user_pw'])){
			
			if (isset($_COOKIE['cookie_etchat_nik_registered'])){
				// create new LangXml Object
				$langObj = new LangXml();
				$lang=$langObj->getLang()->changepw_php[0];
				echo $lang->warning[0]->tagData;
			}
			else{	
				setcookie("cookie_etchat_nik_registered", "1", time()+(24*3600), "/");
				//setcookie("cookie_etchat_nik_registered", "1");
				$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_user SET etchat_userpw = '".md5($_POST['user_pw'])."', etchat_userprivilegien='user', etchat_reg_timestamp=now(), etchat_reg_ip='".$_SERVER['REMOTE_ADDR']."' WHERE etchat_user_id = ".(int)$_SESSION['etchat_'.$this->_prefix.'user_id']);
				echo "1";
			}
		}
		// close DB connection
		$this->dbObj->close();
	}
}