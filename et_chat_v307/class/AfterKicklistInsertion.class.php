<?php
/**
 * Class AfterKicklistInsertion, shows the page after the user was kicked
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AfterKicklistInsertion extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses ConnectDB::sqlSet()	
	* @uses ConnectDB::close()	
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @uses LangXml::getLang() parser method
	* @return void
	*/
	public function __construct (){ 
		
		// call parent Constructor from class DbConectionMaker
		parent::__construct(); 

		session_start();

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		$this->dbObj->sqlSet("DELETE FROM {$this->_prefix}etchat_useronline WHERE etchat_onlineuser_fid = ".$_SESSION['etchat_'.$this->_prefix.'user_id']);
		$this->dbObj->close();
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->admin[0]->kick[0];
		
		// initialize template
		$this->initTemplate($lang);
		
		@session_unset();
		@session_destroy();
		
	}
	
	/**
	* Initializer for template
	*
	* @return void
	*/
	private function initTemplate($lang){
		// Include Template
		include_once("styles/".$_SESSION['etchat_'.$this->_prefix.'style']."/kicked.tpl.html");
	}
}
