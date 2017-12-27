<?php
/**
 * Class AdminInsertSmilies - Admin area
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminInsertSmilies extends DbConectionMaker
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

			$uploaddir = './smilies/';
			$checkfile = "./smilies/".$_FILES['smiliefile']['name'];
			
			if(file_exists($checkfile)){
				$nowname = time()."_".$_FILES['smiliefile']['name'];
				$notes ="".$lang->file_exists[0]->tagData." ".time().".".$_FILES['smiliefile']['name']."<br>";
			}else{
				$nowname = $_FILES['smiliefile']['name'];
				$notes ="";
			}

			// Test if the sign exists in the DB
			$res = $this->dbObj->sqlGet("select etchat_smileys_id FROM {$this->_prefix}etchat_smileys where etchat_smileys_sign = '".$_POST['sign']."'");
			if (is_array($res)){
				$print_result.= $lang->sign_exists[0]->tagData."<br>";
				$print_result.= "<a href='./?AdminSmiliesIndex'>".$lang->back[0]->tagData."</a>";
			}else{
			
				$is_image = getimagesize($_FILES['smiliefile']['tmp_name']);
				if (is_array($is_image)) {
					move_uploaded_file($_FILES['smiliefile']['tmp_name'], $uploaddir . $nowname);
					$this->dbObj->sqlSet("INSERT INTO {$this->_prefix}etchat_smileys(etchat_smileys_sign,etchat_smileys_img) VALUES ('".$_POST['sign']."', 'smilies/".$nowname."')");
					$print_result.= $lang->isupload[0]->tagData."<br>";
					$print_result.= $notes;
					$print_result.= "<br><a href='./?AdminCreateNewSmilies'>".$lang->smilie[0]->tagData."</a>";
					$print_result.= "<br /><a href='./?AdminSmiliesIndex'>".$lang->back[0]->tagData."</a>";
				} else {
					@unlink($_FILES['smiliefile']['tmp_name']);
					$print_result.= $lang->noupload[0]->tagData;
					//print_r($_FILES);
					$print_result.= "<br /><br /><a href='./?AdminSmiliesIndex'>".$lang->back[0]->tagData."</a>";
				}
			}	
			// Include Template
			include_once("styles/admin_tpl/insertSmiliesMessage.tpl.html");
		}else{
			echo $lang->error[0]->tagData;
			return false;
		}
	}
}