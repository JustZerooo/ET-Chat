<?php
/**
 * Class InstallMake - Install area
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class InstallMake extends DbConectionMaker
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

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		if ($this->_usedDatabase == "mysql") $sql_dump="install/mysql_db.sql";
		if ($this->_usedDatabase == "pgsql") $sql_dump="install/postgres_db.sql";

		if (file_exists($sql_dump)){
			$sql=explode("-- limit --", file_get_contents($sql_dump));
			for($a=0; $a<(count($sql)); $a++){
				$zeile=trim($sql[$a]);
				if (!empty( $zeile )) {
					$zeile = str_replace("###prefix###", $this->_prefix, $zeile);
					$this->dbObj->sqlSet($zeile);
				}
			}
			$this->dbObj->close();
			
			include_once("styles/install_tpl/installed.tpl.html");
		}
		else 
			echo "Install directory was not found.";
		
	}
}