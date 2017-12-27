<?php
/**
 * Class AdminCreateNewUser - Admin area
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminCreateNewUser extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @uses ConnectDB::sqlGet()	
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
		$lang=$langObj->getLang()->admin[0]->admin_user[0];
		
		
		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin"){
			
			$feld=$this->dbObj->sqlGet("SELECT etchat_user_id, etchat_username, etchat_userpw, etchat_userprivilegien FROM {$this->_prefix}etchat_user WHERE etchat_userprivilegien='admin' OR etchat_userprivilegien='mod'");
			$this->dbObj->close();
			
			if (is_array($feld)){
				$print_user_list="<table>";
				foreach($feld as $datasets)
					$print_user_list.="<tr><td>".$datasets[1]."</td><td>(<i>".$datasets[3]."</i>)</td><td>&nbsp;&nbsp;&nbsp;</td><td><a href=\"./?AdminUserEdit&id=".$datasets[0]."\">".$lang->edit[0]->tagData."</a></td></tr>";
				$print_user_list.="</table>";
			} else $print_user_list=$lang->noadmins[0]->tagData;
			// initialize Template
			$this->initTemplate($lang, $print_user_list);
			
		}else{
			echo $lang->error[0]->tagData;
			return false;
		}
		
	}
	
	/**
	* Initializer for template
	*
	* @param  String $print_user_list
	* @param  XMLParser $lang, Obj with the needed lang tag from XML lang-file
	* @return void
	*/
	private function initTemplate($lang, $print_user_list){
		// Include Template
		include_once("styles/admin_tpl/createNewUser.tpl.html");
	}
}