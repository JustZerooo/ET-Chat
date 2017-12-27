<?php
/**
 * Class Smileys, generate smiles list from db table
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class Smileys extends DbConectionMaker
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
		
		// Disable Browser Chache
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		// get smileys from db
		$smil_array=$this->dbObj->sqlGet("SELECT etchat_smileys_sign, etchat_smileys_img FROM {$this->_prefix}etchat_smileys");

		// create HTML List with smileys
		foreach ($smil_array as $smil)
			echo "<img src=\"".$smil[1]."\" id=\"".$smil[0]."\" style=\"cursor:pointer;\">\n";
		
		// close DB connect
		$this->dbObj->close();
	}
}