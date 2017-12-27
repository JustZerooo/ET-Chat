<?php
/**
 * Insert2Blacklist, insert the user to the Blacklist
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class Insert2Blacklist extends DbConectionMaker
{
	/**
	* Constructor
	*
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::sqlSet()	
	* @uses ConnectDB::close()	
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @uses Blacklist object creation
	* @uses Blacklist::insertUser()
	* @return void
	*/
	public function __construct (){
	
		// call parent Constructor from class DbConectionMaker
		parent::__construct();
		
		session_start();

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->admin[0]->add2blacklist[0];
		
		if($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin" || $_SESSION['etchat_'.$this->_prefix.'user_priv']=="mod"){
				
			$ip=$this->dbObj->sqlGet("SELECT etchat_onlineip FROM {$this->_prefix}etchat_useronline WHERE etchat_onlineuser_fid = ".(int)$_POST['user_id']);
			
			if (is_array($ip)){	
				if ($_POST['time']>0) {
					// create new Blacklist Object
					$blObj = new Blacklist($this->dbObj);
					$blObj->insertUser((int)$_POST['user_id'],(int)$_POST['time']);
				}else{
					$this->dbObj->sqlSet("INSERT INTO {$this->_prefix}etchat_kick_user (etchat_kicked_user_id, etchat_kicked_user_time) VALUES (".(int)$_POST['user_id'].", ".(date("U")+30).")");
				}
			}else{
				echo $lang->user_away[0]->tagData;
			}
			
			$this->dbObj->close();

		}else{
			echo $lang->session_lost[0]->tagData;
		}
	}
}
