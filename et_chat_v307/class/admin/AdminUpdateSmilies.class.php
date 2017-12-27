<?php
/**
 * Class AdminUpdateSmilies - Admin area
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminUpdateSmilies extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses ConnectDB::sqlSet()	
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
		$lang=$langObj->getLang()->admin[0]->admin_smilies[0];
		
		
		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin"){
			
			// Test if the sign exists in the DB
			$res = $this->dbObj->sqlGet("select etchat_smileys_id FROM {$this->_prefix}etchat_smileys where etchat_smileys_sign = '".$_POST['smileys_sing']."'");
			if (is_array($res)){
				$this->dbObj->close();
				// Include error Template
				include_once("styles/admin_tpl/errorSmileySignExists.tpl.html");
			}else{
				$this->dbObj->sqlSet("UPDATE {$this->_prefix}etchat_smileys SET etchat_smileys_sign = '".$_POST['smileys_sing']."' WHERE etchat_smileys_id = ".(int)$_POST['id']);
				$this->dbObj->close();
				header("Location: ./?AdminSmiliesIndex");
			}

		}else{
			echo $lang->error[0]->tagData;
			return false;
		}
	}
}