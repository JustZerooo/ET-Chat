<?php
/**
 * Class InstallIndex
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class InstallIndex extends EtChatConfig
{

	/**
	* Constructor
	*
	* @return void
	*/
	public function __construct (){ 
		
		// call parent Constructor from class EtChatConfig
		parent::__construct(); 
		
		
		$install_error = "";
		
		
		if ($this->_usedDatabaseExtension=="pdo"){
			if (!extension_loaded('pdo')) $install_error .= "<div style=\"color:red\"> Keine PDO-Erweiterung gefunden.(PDO-Erweiterung sollte installiert sein!)</div>";
			if (!extension_loaded('pdo_'.$this->_usedDatabase)) $install_error .= "<div style=\"color:red\"> Keine pdo_".$this->_usedDatabase."-Erweiterung gefunden.(pdo_".$this->_usedDatabase." sollte installiert sein!)</div>";
		}
		
		if ($this->_usedDatabaseExtension=="mysqli")
			if (!extension_loaded('mysqli')) $install_error .= "<div style=\"color:red\"> Keine MySQLi-Erweiterung gefunden.(MySQLi sollte installiert sein!)</div>";
		
		if (empty($install_error)) 
			$start_install = "<a href=\"./?InstallMake\">Installation starten &gt;&gt;&gt;</a>";
		else 
			$start_install = "<b>Die Installation kann nicht durchgeführt werden.</b><br /><br /> Ursache/n:<br />".$install_error."<br /><br />Bitte korrigieren Sie die Einstellungen Ihres Webservers um den ET-Chat zu installieren.";
		
		
		if (file_exists("./install"))
			include_once("styles/install_tpl/index.tpl.html");
		else 
			echo "Install directory was not found.";
	}
}