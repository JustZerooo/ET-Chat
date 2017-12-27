<?php
/**
 * Class AdminRegUserIndex - Admin area
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminRegUserIndex extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
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
		$lang=$langObj->getLang()->admin[0]->admin_reg_user[0];
		
		
		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin"){
			$counted=$this->dbObj->sqlGet("SELECT count(etchat_user_id) FROM {$this->_prefix}etchat_user WHERE etchat_userprivilegien='user'");
			
			if (empty($_GET['site'])) $_GET['site']=1;
			
			if (empty($_GET['order']) && empty($_SESSION['etchat_'.$this->_prefix.'order_reg_user'])) 
				$_SESSION['etchat_'.$this->_prefix.'order_reg_user']="etchat_username";
			
			if ($_GET['order'] == 1) $_SESSION['etchat_'.$this->_prefix.'order_reg_user']="etchat_username";
			if ($_GET['order'] == 2) $_SESSION['etchat_'.$this->_prefix.'order_reg_user']="etchat_username DESC";
			if ($_GET['order'] == 3) $_SESSION['etchat_'.$this->_prefix.'order_reg_user']="etchat_reg_timestamp";
			if ($_GET['order'] == 4) $_SESSION['etchat_'.$this->_prefix.'order_reg_user']="etchat_reg_timestamp DESC";
			if ($_GET['order'] == 5) $_SESSION['etchat_'.$this->_prefix.'order_reg_user']="etchat_reg_ip";
			if ($_GET['order'] == 6) $_SESSION['etchat_'.$this->_prefix.'order_reg_user']="etchat_reg_ip DESC";
			
			$print_order_by = ($_SESSION['etchat_'.$this->_prefix.'order_reg_user']=="etchat_username") ? "<option value=\"1\" selected>".$lang->name[0]->tagData." ".$lang->asc[0]->tagData."</value>" : "<option value=\"1\">".$lang->name[0]->tagData." ".$lang->asc[0]->tagData."</value>";
			$print_order_by.= ($_SESSION['etchat_'.$this->_prefix.'order_reg_user']=="etchat_username DESC") ? "<option value=\"2\" selected>".$lang->name[0]->tagData." ".$lang->desc[0]->tagData."</value>" : "<option value=\"2\">".$lang->name[0]->tagData." ".$lang->desc[0]->tagData."</value>";
			$print_order_by.= ($_SESSION['etchat_'.$this->_prefix.'order_reg_user']=="etchat_reg_timestamp") ? "<option value=\"3\" selected>".$lang->reg_date[0]->tagData." ".$lang->asc[0]->tagData."</value>" : "<option value=\"3\">".$lang->reg_date[0]->tagData." ".$lang->asc[0]->tagData."</value>";
			$print_order_by.= ($_SESSION['etchat_'.$this->_prefix.'order_reg_user']=="etchat_reg_timestamp DESC") ? "<option value=\"4\" selected>".$lang->reg_date[0]->tagData." ".$lang->desc[0]->tagData."</value>" : "<option value=\"4\">".$lang->reg_date[0]->tagData." ".$lang->desc[0]->tagData."</value>";		
			$print_order_by.= ($_SESSION['etchat_'.$this->_prefix.'order_reg_user']=="etchat_reg_ip") ? "<option value=\"5\" selected>".$lang->reg_ip[0]->tagData." ".$lang->asc[0]->tagData."</value>" : "<option value=\"5\">".$lang->reg_ip[0]->tagData." ".$lang->asc[0]->tagData."</value>";
			$print_order_by.= ($_SESSION['etchat_'.$this->_prefix.'order_reg_user']=="etchat_reg_ip DESC") ? "<option value=\"6\" selected>".$lang->reg_ip[0]->tagData." ".$lang->desc[0]->tagData."</value>" : "<option value=\"6\">".$lang->reg_ip[0]->tagData." ".$lang->desc[0]->tagData."</value>";		
			
			$pro_seite = 100;
			$site=intval($_GET['site'])-1;
			$von = $site*$pro_seite;
		
			if ($this->_usedDatabase == "mysql") $limit = "LIMIT $von, $pro_seite";
			if ($this->_usedDatabase == "pgsql") $limit = "LIMIT $pro_seite OFFSET $von";
			
			$feld=$this->dbObj->sqlGet("SELECT etchat_user_id, etchat_username, etchat_userpw, etchat_userprivilegien, etchat_reg_timestamp, etchat_reg_ip FROM {$this->_prefix}etchat_user WHERE etchat_userprivilegien='user' order by ".$_SESSION['etchat_'.$this->_prefix.'order_reg_user']." ".$limit );
			$this->dbObj->close();
			
			$sitemakerObj = new Sitemaker($pro_seite, $counted[0][0]);
			$sitemakerObj->href=true;
			$sitemakerObj->make($_GET['site'], "./?AdminRegUserIndex&site=#site#", $lang->site[0]->tagData, $lang->site_of[0]->tagData);
			$print_sitemaker = $sitemakerObj->get();
			
			
			// Checksum to prevent this: http://www.sedesign.de/sed/forum/forum_entry.php?id=7154
			$_SESSION['etchat_'.$this->_prefix.'CheckSum4RegUserEdit'] = rand(1,999999999);
			
			
			if (is_array($feld)){
				$print_user_list=$print_sitemaker."&nbsp;&nbsp; ".$lang->sort[0]->tagData." <form id=\"order_form\" style=\"display:inline\"><select id=\"order_by\">".$print_order_by."</select></form><form id=\"checkers\" action=\"./?AdminRegUserEdit\" method=\"post\">
				<input type=\"hidden\" id=\"userids\" name=\"userids\" />
				<table><tr><td>&nbsp;</td><td><b>".$lang->name[0]->tagData."</b></td><td><b>".$lang->reg_date[0]->tagData."</b></td><td><b>".$lang->reg_ip[0]->tagData."</b></td><td>&nbsp;&nbsp;&nbsp;</td><td></td></tr>";
				foreach($feld as $datasets)
					$print_user_list.="<tr><td><input type=\"checkbox\" name=\"userid\" value=\"".$datasets[0]."\"></td><td><b>".$datasets[1]."</b></td><td> ".$datasets[4]."&nbsp;&nbsp;&nbsp;</td><td> ".$datasets[5]."</td><td>&nbsp;&nbsp;&nbsp;</td><td>".$lang->make2[0]->tagData." <a href=\"./?AdminRegUserEdit&admin&cs4rue=".$_SESSION['etchat_'.$this->_prefix.'CheckSum4RegUserEdit']."&id=".$datasets[0]."\">admin</a> | <a href=\"./?AdminRegUserEdit&mod&cs4rue=".$_SESSION['etchat_'.$this->_prefix.'CheckSum4RegUserEdit']."&id=".$datasets[0]."\">mod</a></td></tr>";
				$print_user_list.="</table>
				<input type=\"button\" value=\"".$lang->set_all[0]->tagData."\" id=\"marking_all_button\" onclick=\"marking_all();\">&nbsp;<input type=\"button\" value=\"".$lang->del[0]->tagData."\" onclick=\"del_all();\"></form>".$lang->text[0]->tagData;
			} else {
				if ($this->_allow_nick_registration)
					$print_user_list="<br /><br /><b>".$lang->nouser[0]->tagData."</b><br />".$lang->nouser2[0]->tagData."<br /><br />";
				else
					$print_user_list="<br /><br />".$lang->nouser[0]->tagData."<br /><br />";	
			}
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
	* @param  String $print_sitemaker
	* @param  XMLParser $lang, Obj with the needed lang tag from XML lang-file
	* @return void
	*/
	private function initTemplate($lang, $print_user_list){
		// Include Template
		include_once("styles/admin_tpl/indexRegUser.tpl.html");
	}
}