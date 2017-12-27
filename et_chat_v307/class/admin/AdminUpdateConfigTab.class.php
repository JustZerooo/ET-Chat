<?php
/**
 * Class UpdateConfigTab
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminUpdateConfigTab extends DbConectionMaker
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

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		// Sets charset and content-type for index.php
		header('content-type: text/html; charset=utf-8');
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->admin[0]->admin_prop[0];
		
		
		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin"){
			
			$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_config SET etchat_config_reloadsequenz = ".(int)$_POST['reload'].",
			etchat_config_messages_im_chat = ".(int)$_POST['anz_mess'].",
			etchat_config_style = '".$_POST['style']."',
			etchat_config_loeschen_nach = ".(int)$_POST['loeschen_nach'].",
			etchat_config_lang = '".$_POST['lang']."'
			WHERE etchat_config_id = 1");
			$this->dbObj->close();
			header("Location: ./?AdminPropertyIndex");
			
		}else{
			echo $lang->error[0]->tagData;
			return false;
		}
	}
}