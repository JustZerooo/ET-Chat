<?php
/**
 * Class AfterSpamlistInsertion, shows the page after the user is inserted into the blacklist bavouse of spam
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AfterSpamlistInsertion extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses ConnectDB::close()	
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @return void
	*/
	public function __construct (){ 
		
		// call parent Constructor from class DbConectionMaker
		parent::__construct(); 

		session_start();

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		$this->configTabData2Session();
		
		$this->dbObj->close();
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->admin[0]->spam[0];
		
		// initialize template
		$this->initTemplate($lang);
	}
	
	/**
	* Initializer for template
	*
	* @return void
	*/
	private function initTemplate($lang){
		// Include Template
		include_once("styles/".$_SESSION['etchat_'.$this->_prefix.'style']."/spamlisted.tpl.html");
	}
}