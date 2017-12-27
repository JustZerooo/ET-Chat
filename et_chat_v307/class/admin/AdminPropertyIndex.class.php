<?php
/**
 * Class PropertyIndex
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminPropertyIndex extends DbConectionMaker
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
		
		$this->configTabData2Session();
		$this->dbObj->close();
			
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->admin[0]->admin_prop[0];
		
		
		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin"){
			
			$handle = opendir("styles/");
			while($files = readdir($handle))
			{
				if($files != "." && $files != "..")
				{
					if (is_dir("styles/".$files) && $files!="admin_tpl" && $files!="install_tpl") {
                        if ($_SESSION['etchat_'.$this->_prefix.'style']==$files) $print_styles.= "<option value=\"".$files."\" selected>".$files."</option>\n";
                        else $print_styles.= "<option value=\"".$files."\">".$files."</option>\n";
					}
				}		
			}				
			
			$handle = opendir("lang/");
			while($files = readdir($handle))
			{
				if (!is_dir("lang/".$files) && stripos($files, '.xml')!==false && substr($files,0,5)=='lang_') {

					$xml_file = file_get_contents('lang/'.$files);
					$p = new XMLParser($xml_file);
					$p->Parse();
					if ($files == $_SESSION['etchat_'.$this->_prefix.'lang_xml_file']) $print_lang_files.= "<option value=\"".$files."\" selected>".$p->document->tagAttrs['lang']."</option>";
					else $print_lang_files.=  "<option value=\"".$files."\">".$p->document->tagAttrs['lang']."</option>";
				}
			}

			// initialize Template
			include_once("styles/admin_tpl/indexProperty.tpl.html");
			
		}else{
			echo $lang->error[0]->tagData;
			return false;
		}
		
	}
}