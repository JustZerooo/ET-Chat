<?php
/**
 * Class Index, login page creator
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class Index extends DbConectionMaker
{
	/**
	* LangXml Obj for login page
	* @var LangXml
	*/
	public $lang;
	
	/**
	* Unix timestamp for login form. This serve as a test for bot blocking.
	* @var int
	*/
	public $aktuell_date_u;
	
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
			
		// starts session in index.php
		session_start();
		
		// Sets  cookie with Unix timestamp. This serve as a test for bot blocking.
		setcookie($this->_prefix."cookie_test", date('U'));
		
		// Sets charset and content-type for index.php
		header('content-type: text/html; charset=utf-8');
		
		// Set all Data from [prefix]_etchat_config Table to Session-Vars. So needs only to be run once on login page.
		$this->configTabData2Session();
		
		// something like cron-job to delete wasteful/old data from db
		$this->dbObj->sqlSet("delete FROM {$this->_prefix}etchat_messages where etchat_timestamp < ".(date('U')-($_SESSION['etchat_'.$this->_prefix.'loeschen_nach']*3600*24)));
		$this->dbObj->sqlSet("delete FROM {$this->_prefix}etchat_blacklist where etchat_blacklist_time < ".date('U'));
		$this->dbObj->sqlSet("delete FROM {$this->_prefix}etchat_kick_user where etchat_kicked_user_time < ".date('U'));

		// close db connection
		$this->dbObj->close();
		
		// create new LangXml Object
		$langObj = new LangXml;
		$this->lang=$langObj->getLang()->index_php[0];
		
		$this->aktuell_date_u=date('U');
		$_SESSION[$this->_prefix.'set_check']=md5($this->aktuell_date_u);
		
		// initialize index template
		$this->initTemplate();
	}
	
	/**
	* Initializer for template
	*
	* @return void
	*/
	private function initTemplate(){
		// Include Template
		include_once("styles/".$_SESSION['etchat_'.$this->_prefix.'style']."/index.tpl.html");
	}
	
}