<?php
/**
 * Class UserCheckerAndInserter, checks the user and insert him/her to db, if possible
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class UserCheckerAndInserter extends EtChatConfig
{
	/**
	* DB-Connection Obj
	* @var ConnectDB
	*/
	private $dbObj;
	
	/**
	* array with all user data
	* @var array
	*/
	protected $_user_exists;
	
	/**
	* user name
	* @var string
	*/
	protected $_user;
	
	/**
	* user pw
	* @var string
	*/
	protected $_pw;
	
	/**
	* user sex
	* @var string
	*/
	protected $_gender;
	
	/**
	* XMLParser Obj
	* @var XMLParser
	*/
	protected $_lang;
	
	/**
	* this var is a status var and will be occupied with different values in subjection of needs, so it can get value "1" for ok, or just an other error message
	* @var string
	*/
	public $status;
	
	/**
	* Constructor
	*
	* @param  ConnectDB $dbObj, Obj with the db connection handler
	* @param  array $user_exists 
	* @param  string $user 
	* @param  string $pw 
	* @param  string $gender
	* @param  XMLParser $lang 
	* @uses ConnectDB::sqlSet()	
	* @return void
	*/
	public function __construct ($dbObj, $user_exists, $user, $pw, $gender, $lang){ 
		
		// call parent Constructor from class EtChatConfig
		parent::__construct();
		
		$this->dbObj = $dbObj;
		
		// set the class vars
		$this->_user_exists=$user_exists;
		$this->_user=$user;
		$this->_pw=$pw;
		$this->_gender=$gender;
		$this->_lang=$lang;
		
		// if the user name is just exists in the user table
		if (is_array($this->_user_exists)){
			
			// update needed user params
			$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_user SET etchat_usersex = '".$this->_gender{0}."' WHERE etchat_user_id = ".$this->_user_exists[0][0]);
			
			// need pw input?
			if ($this->_pw=="") $this->userWithoutPw();
			else $this->userWithPw();
		}
		else $this->createNewUser();
		
		
	}
	
	/**
	* CreateNewUser, if there is no such user name in user tab, creates a new dataset
	*
	* @uses ConnectDB::sqlSet()	
	* @return void
	*/
	private function createNewUser(){	
		$this->dbObj->sqlSet("INSERT INTO {$this->_prefix}etchat_user ( etchat_username, etchat_usersex ) VALUES ( '".$this->_user."', '".$this->_gender{0}."')");
		$user_neu=$this->dbObj->sqlGet("SELECT etchat_user_id, etchat_username, etchat_userprivilegien FROM {$this->_prefix}etchat_user WHERE etchat_username = '".$this->_user."' LIMIT 1");
		$_SESSION['etchat_'.$this->_prefix.'user_id'] = $user_neu[0][0];
		$_SESSION['etchat_'.$this->_prefix.'username'] = $user_neu[0][1];
        $_SESSION['etchat_'.$this->_prefix.'user_priv'] = $user_neu[0][2];
		$this->status=1;
	}
	
	/**
	* UserWithPw, user name and user pw were committed from login form
	*
	* @return void
	*/
	private function userWithPw(){
		if ($this->_user_exists[0][2]==md5($this->_pw)){
			$_SESSION['etchat_'.$this->_prefix.'user_id'] = $this->_user_exists[0][0];
			$_SESSION['etchat_'.$this->_prefix.'username'] = $this->_user_exists[0][1];
			$_SESSION['etchat_'.$this->_prefix.'user_priv'] = $this->_user_exists[0][3];
			if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=='admin' ||
				$_SESSION['etchat_'.$this->_prefix.'user_priv']=='mod') setcookie("cookie_anzahl_logins_in_XX_sek",1);
			$this->status=1;
		}
		else $this->status = $this->_lang->pw_falsch[0]->tagData;
		
	}
	
	/**
	* UserWithoutPw, this user has a pw in db, so the status is "pw" to make a invitation in login-form to insert a pw
	*
	* @return void
	*/
	private function userWithoutPw(){
		if (!empty($this->_user_exists[0][2])) {
		
			// if the user shpul get the invisible feeld in PW enter
			$this->status = ($this->_user_exists[0][3]=="admin") ? "pw+invisible" : "pw";

		}
		else {
			$_SESSION['etchat_'.$this->_prefix.'user_id'] = $this->_user_exists[0][0];
			$_SESSION['etchat_'.$this->_prefix.'username'] = $this->_user_exists[0][1];
			$_SESSION['etchat_'.$this->_prefix.'user_priv'] = $this->_user_exists[0][3];
			if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=='admin' ||
				$_SESSION['etchat_'.$this->_prefix.'user_priv']=='mod') setcookie("cookie_anzahl_logins_in_XX_sek",1);
			$this->status = 1;
		}
	}
}