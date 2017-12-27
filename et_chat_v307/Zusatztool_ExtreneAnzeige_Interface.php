<?php

// like $GLOBALS["path"] = "./et_chat_v3.06/";
$GLOBALS["path"] = "./";

function __autoload($class_name) {
		require_once ($GLOBALS["path"].'class/'.$class_name.'.class.php');		
}

class ExternUserView extends DbConectionMaker
{
	/**
	* Constructor
	*
	* @uses ConnectDB::sqlGet()
	* @uses ConnectDB::close()	
	* @return void
	*/
	public function __construct (){
	
		// call parent Constructor from class DbConectionMaker
		parent::__construct();
		
		unset($GLOBALS["path"]);

		$erg=$this->dbObj->sqlGet("SELECT count(etchat_onlineid) FROM {$this->_prefix}etchat_useronline WHERE 
		etchat_onlinetimestamp > ".(date('U')-30)."
		and (etchat_user_online_user_status_img is null or etchat_user_online_user_status_img <> 'status_invisible')");

		echo $erg[0][0]." User sind online. <br />";

		$erg_user=$this->dbObj->sqlGet("SELECT etchat_user_online_user_name, etchat_user_online_user_priv FROM {$this->_prefix}etchat_useronline 
		WHERE etchat_onlinetimestamp > ".(date('U')-30)." 
		and (etchat_user_online_user_status_img is null or etchat_user_online_user_status_img <> 'status_invisible')
		order by etchat_user_online_user_name");

		if(is_array($erg_user)) foreach($erg_user as $us) echo $us[0]."<br>";
		else echo "Niemand im Chat";

		// close DB connect
		$this->dbObj->close();
		
	}
}

// initialise
new ExternUserView;

?>