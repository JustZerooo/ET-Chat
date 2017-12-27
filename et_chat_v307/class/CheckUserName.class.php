<?php
/**
 * Class CheckUserName, chat login class
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class CheckUserName extends DbConectionMaker
{
	/**
	* LangXml Obj for login page
	* @var LangXml
	*/
	public $lang;

	/**
	* bridge for an foreign user application used or not
	* @var bool
	*/
	protected $user_application;
	
	/**
	* Constructor, contains all once needed operations and steps to check the user, login him and s.o.
	*
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @uses Blacklist object creation
	* @uses Blacklist::userInBlacklist() checks if in the Blacklist
	* @uses Blacklist::killUserSession()
	* @uses UserCheckerAndInserter object creation
	* @uses UserCheckerAndInserter::$status status check for insertion
	* @uses ConnectDB::sqlGet()
	* @uses ConnectDB::sqlSet()	
	* @uses ConnectDB::close()	
	* @return void
	*/
	public function __construct ($user_application=false, $username='', $gender=''){
	
		// call parent Constructor from class DbConectionMaker
		parent::__construct();
		
		// starts session in index.php
		@session_start();
		
		// all documentc requested per AJAX should have this part to turn off the browser and proxy cache for any XHR request
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		$this->user_application = $user_application;
		
		
		// for extern user application 
		if (!$this->user_application){
			// Sets charset and content-type
			header('content-type: application/json; charset=utf-8');
			$username = trim($_POST['username']);
			$gender = trim($_POST['gender']);
			
		}else{
			// Set all Data from [prefix]_etchat_config Table to Session-Vars. So needs only to be run once on login page.
			$this->configTabData2Session();
			
			// something like cron-job to delete wasteful/old data from db
			$this->dbObj->sqlSet("delete FROM {$this->_prefix}etchat_messages where etchat_timestamp < ".(date('U')-($_SESSION['etchat_'.$this->_prefix.'loeschen_nach']*3600*24)));
			$this->dbObj->sqlSet("delete FROM {$this->_prefix}etchat_blacklist where etchat_blacklist_time < ".date('U'));
			$this->dbObj->sqlSet("delete FROM {$this->_prefix}etchat_kick_user where etchat_kicked_user_time < ".date('U'));
		}
		
		if (empty($gender))$gender="n";
		
		// cookies are explicit duty
		if (!$this->user_application)
			if(!isset($_COOKIE[$this->_prefix.'cookie_test'])){ $this->errorMessage("Please aktivate your cookies."); return false;}
		
		// check if the request comes from index.php
		if (!$this->user_application)
			if(!isset($_POST[$_SESSION[$this->_prefix.'set_check']])){ $this->errorMessage("no hacks and bots"); return false;}
		
		// create new LangXml Object
		$langObj = new LangXml();
		$this->lang=$langObj->getLang()->checkusername_php[0];

		// checking for to many tries for login 
		if (!$this->user_application)
			if (!$this->loginCounter()) return false;
		
		// open the style.CSS and get the user text-color and system text-color from the header part of any et-chat css styles
		$style_lines = file("styles/".$_SESSION['etchat_'.$this->_prefix.'style']."/style.css");
		foreach($style_lines as $line){
			if (substr($line, 0, 10)=="Textfarbe:") {
				$ft = explode(":", $line);
				$_SESSION['etchat_'.$this->_prefix.'textcolor'] = trim($ft[1]);
			}
			if (substr($line, 0, 12)=="Systemfarbe:") {
				$fs = explode(":", $line);
				$_SESSION['etchat_'.$this->_prefix.'syscolor'] = trim($fs[1]);
			}
		}

		
		// create new BlacklistChecker Object
		$blackListObj = new Blacklist($this->dbObj);
		
		// Is the curent user IP in zhe Blacklist or has the user browser an actual "black cookie"?
		if ($blackListObj->userInBlacklist()){
			setcookie("cookie_anzahl_logins_in_XX_sek",1);
			if (!$this->user_application) $this->errorMessage("blacklist"); 
			$blackListObj->killUserSession();
			if ($this->user_application) header('Location: ./?AfterBlacklistInsertion');
			return false; 
		}
		
		// delete all old datasets from the etchat_useronline table (session table)
		$this->dbObj->sqlSet("DELETE FROM {$this->_prefix}etchat_useronline WHERE etchat_onlinetimestamp < ".(date('U')-(($_SESSION['etchat_'.$this->_prefix.'config_reloadsequenz']/1000)*4)));

		// abort, if $username empty or to long
		if (empty($username) || strlen($username)>100) { $this->errorMessage(""); return false; }
		
		// convert username with htmlspecialchars  >  preg_replace('/[\x00-\x1F]/', .... )   to prevent hacking over Live HTTP Headrs (since v307 beta 6)
		//$username = htmlspecialchars(str_replace("\\","/",preg_replace('/[\x00-\x1F]/', '', $username)), ENT_QUOTES, "UTF-8");
		$username = htmlspecialchars(str_replace("\\","/",preg_replace('/[\x00-\x1F\x90\x8F\x81\xA0\x9D]/', '', $username)), ENT_QUOTES, "UTF-8");
		
		// abort, if this username is occupied now
		if (!$this->user_application)
			if ($this->userInChatNow($username)) { $this->errorMessage($this->lang->name_busy[0]->tagData); return false; }
		
		// Dataset with Userparameter from etchat_user tab. The dataset is empty if there no such user with this name
		$user_exists = $this->dbObj->sqlGet("SELECT etchat_user_id, etchat_username, etchat_userpw, etchat_userprivilegien FROM {$this->_prefix}etchat_user WHERE etchat_username = '".$username."' order by etchat_userpw DESC");
		
		// create new CheckerAndInserterObj Object (changed since v307-Beta10)
		$userCheckerAndInserterObj = new UserCheckerAndInserter($this->dbObj, $user_exists, $username, $_POST['pw'], preg_replace("/[^a-z]/i", "n", $gender), $this->lang);
		
		//  Status 1 means that the loggining was sucessfull
		if ($userCheckerAndInserterObj->status==1) $this->messageOnEnter();
		else $this->errorMessage($userCheckerAndInserterObj->status);
	}
	
	/**
	* Checking how many logins the user have done in the last 3 Mutes and saves it to zhe cookie. 
	* If  more then is set in  var _limit_logins_in_three_minutes, then the user
	* will get a black-cookie for 3 minutes and an error message
	*
	* @return bool
	*/
	private function loginCounter (){
		if(!isset($_COOKIE['cookie_last_login'])) {
			setcookie("cookie_last_login", date('U'));
			setcookie("cookie_anzahl_logins_in_XX_sek",1);
			return true;
		}
		else{
			if (date('U')-$_COOKIE['cookie_last_login'] < 180) {
				$c_anzahl_logins=$_COOKIE['cookie_anzahl_logins_in_XX_sek']+1;
				setcookie("cookie_anzahl_logins_in_XX_sek", $c_anzahl_logins);
				
				if ($_COOKIE['cookie_anzahl_logins_in_XX_sek']>($this->_limit_logins_in_three_minutes-1)) {
				setcookie("cookie_last_login", date('U'));
				$this->errorMessage($this->lang->many_logins[0]->tagData);
				return false;
				}
			else return true;
			}
			else {
				setcookie("cookie_last_login", date('U'));
				setcookie("cookie_anzahl_logins_in_XX_sek", 1);
				return true;
			}
		}
	}
	
	/**
	* Checks if this username is occupied now
	*
	* @param  string $user Username
	* @uses ConnectDB::sqlGet()
	* @return bool
	*/
	private function userInChatNow($user){
		// if a returned dataset is an array, then the Username is now in etchat_useronline tab, so in the chat session
		if(is_array($this->dbObj->sqlGet("SELECT etchat_username FROM {$this->_prefix}etchat_useronline, {$this->_prefix}etchat_user WHERE etchat_username = '".$user."' AND etchat_user_id = etchat_onlineuser_fid LIMIT 1"))){
			setcookie("cookie_anzahl_logins_in_XX_sek",1);
			return true;
		}
		else return false;
	}
	
	/**
	* Print a error message, and close db connect
	*
	* @param  string $message Outputmessage is status != 1, so the user cant be loggin to chat
	* @uses ConnectDB::close
	* @return bool
	*/
	private function errorMessage($message){
		echo $message; 
		$this->dbObj->close();
		return false;
	}
	
	/**
	* The login was sucessfull, so send a message on entrance to the system and close db connection
	* @uses RoomEntrance object creation
	* @uses ConnectDB::close()
	* @return bool
	*/
	private function messageOnEnter(){
		// create new RoomEntrance Object
		new RoomEntrance($this->dbObj, $this->lang);

		// important in order to prevent hacking via Live HTTP Headers (thanks an the iranian script kiddies)
		// (since v307 beta 7)
		unset($_SESSION[$this->_prefix.'set_check']);
		
		$this->dbObj->close();
		
		// it was sucessfull so give it to JavaScript in login.js as AJAX Response
		if (!$this->user_application) echo "1";
		else header('Location: ./?Chat');
		return true;
	}
}