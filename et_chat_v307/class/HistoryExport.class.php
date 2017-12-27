<?php
/**
 * Class HistoryExport, exports history of the chatmessages
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class HistoryExport extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::close()	
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @return void
	*/
	public function __construct (){ 
		
		// call parent Constructor from class DbConectionMaker
		parent::__construct(); 

		session_start();

		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']!="admin" && $_SESSION['etchat_'.$this->_prefix.'user_priv']!="mod") return false;
		
		$exportFormat = $_GET['format'];
		
		header('Expires: 0');
		header('Pragma: no-cache');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		
		
		$privates=false;

		if(!empty($_GET['roomid'])) {
			if ($_GET['roomid']!="priv")
				$raumauswahl = "AND ({$this->_prefix}etchat_messages.etchat_fid_room = ".(int)$_GET['roomid']." OR {$this->_prefix}etchat_messages.etchat_fid_room = 0)";
			else 
				$privates=true;
			
		}
		else {
			$raumauswahl = "AND ({$this->_prefix}etchat_messages.etchat_fid_room = 1 OR {$this->_prefix}etchat_messages.etchat_fid_room = 0)";
			$_GET['roomid']=1;
		}

		if($privates)
			$feld=$this->dbObj->sqlGet("
			SELECT {$this->_prefix}etchat_messages.etchat_id, {$this->_prefix}etchat_user.etchat_username, {$this->_prefix}etchat_messages.etchat_text, {$this->_prefix}etchat_messages.etchat_timestamp, {$this->_prefix}etchat_rooms.etchat_roomname, {$this->_prefix}etchat_messages.etchat_privat, {$this->_prefix}etchat_messages.etchat_user_fid, {$this->_prefix}etchat_messages.etchat_text_css
			FROM ({$this->_prefix}etchat_messages JOIN {$this->_prefix}etchat_rooms ON {$this->_prefix}etchat_messages.etchat_fid_room = {$this->_prefix}etchat_rooms.etchat_id_room) INNER JOIN {$this->_prefix}etchat_user ON {$this->_prefix}etchat_messages.etchat_user_fid = {$this->_prefix}etchat_user.etchat_user_id
			WHERE ({$this->_prefix}etchat_messages.etchat_user_fid=".$_SESSION['etchat_'.$this->_prefix.'user_id']." AND {$this->_prefix}etchat_messages.etchat_privat>0) OR {$this->_prefix}etchat_messages.etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id']."
			ORDER BY {$this->_prefix}etchat_messages.etchat_id DESC");
		else
			$feld=$this->dbObj->sqlGet("
			SELECT {$this->_prefix}etchat_messages.etchat_id, {$this->_prefix}etchat_user.etchat_username, {$this->_prefix}etchat_messages.etchat_text, {$this->_prefix}etchat_messages.etchat_timestamp, {$this->_prefix}etchat_rooms.etchat_roomname, {$this->_prefix}etchat_messages.etchat_privat, {$this->_prefix}etchat_messages.etchat_user_fid, {$this->_prefix}etchat_messages.etchat_text_css
			FROM ({$this->_prefix}etchat_messages LEFT JOIN {$this->_prefix}etchat_rooms ON {$this->_prefix}etchat_messages.etchat_fid_room = {$this->_prefix}etchat_rooms.etchat_id_room ".$raumauswahl.") INNER JOIN {$this->_prefix}etchat_user ON {$this->_prefix}etchat_messages.etchat_user_fid = {$this->_prefix}etchat_user.etchat_user_id
			WHERE  {$this->_prefix}etchat_messages.etchat_privat=0 ".$raumauswahl."
			ORDER BY {$this->_prefix}etchat_messages.etchat_id DESC");
		
		$this->dbObj->close();
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->history_php[0];
		
		if ($exportFormat=="csv"){
			header('Content-Type: text/csv');
			header("Content-Type: application/octet-stream");
			header( "Content-Disposition: attachment; filename=history.csv" );
			$this->printCSV($feld, $lang);
		}
		if ($exportFormat=="xls"){
			header("Content-Type: application/vnd.ms-excel; charset=utf-8");
			header( "Content-Disposition: attachment; filename=history.xls" );
			$this->printXLS($feld, $lang);
		}
		if ($exportFormat=="xml"){
			header("Content-Type: text/xml");
			header( "Content-Disposition: attachment; filename=history.xml" );
			$this->printXML($feld, $lang);
		}
		
	}
	
	/**
	* Creates CSV output
	*
	* @param Array $feld
	* @param LangObj $lang
	* @return bool
	*/
	private function printCSV($feld, $lang){
		
		echo"ID\t".$lang->user[0]->tagData."\t".$lang->date[0]->tagData."\t".$lang->text[0]->tagData."\t".$lang->room[0]->tagData."\n";
		
		if (is_array($feld))
			for ($a=0; $a < count($feld); $a++)
				echo $feld[$a][0]."\t".html_entity_decode(strip_tags($feld[$a][1]), ENT_QUOTES, "UTF-8")."\t".date("d.m.Y (H:i)",$feld[$a][3])."\t".html_entity_decode(strip_tags($feld[$a][2]), ENT_QUOTES, "UTF-8")."\t".html_entity_decode(strip_tags($feld[$a][4]), ENT_QUOTES, "UTF-8")."\n";
	}
	
	/**
	* Creates XLS output
	*
	* @param Array $feld
	* @param LangObj $lang
	* @return bool
	*/
	private function printXLS($feld, $lang){
	
		echo"<html>
		<head>
		<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
		</head>
		<body>
		<table border=\"1\"><tr><td>ID</td><td>".$lang->user[0]->tagData."</td><td>".$lang->date[0]->tagData."</td><td>".$lang->text[0]->tagData."</td><td>".$lang->room[0]->tagData."</td></tr>";

		if (is_array($feld)){
			for ($a=0; $a < count($feld); $a++){
				echo "<tr>";
				echo "<td>".$feld[$a][0]."</td><td>".html_entity_decode(strip_tags($feld[$a][1]), ENT_QUOTES, "UTF-8")."</td><td>".date("d.m.Y (H:i)",$feld[$a][3])."</td><td>".html_entity_decode(strip_tags($feld[$a][2]), ENT_QUOTES, "UTF-8")."</td><td>".html_entity_decode(strip_tags($feld[$a][4]), ENT_QUOTES, "UTF-8")."</td>";
				echo "</tr>";
			}
		}
		
		echo"</table>
		</body>
		</html>";
	}
	
	/**
	* Creates XML output
	*
	* @param Array $feld
	* @param LangObj $lang
	* @return bool
	*/
	private function printXML($feld, $lang){
	
		echo "<?xml version='1.0' encoding='utf-8'?>";
		echo "<etchat>\n";
		if (is_array($feld)){
			for ($a=0; $a < count($feld); $a++){
				echo "<dataset>\n";
				echo "<id>".$feld[$a][0]."</id>\n<user>".html_entity_decode(strip_tags($feld[$a][1]), ENT_QUOTES, "UTF-8")."</user>\n<date>".date("d.m.Y (H:i)",$feld[$a][3])."</date>\n<message>".html_entity_decode(strip_tags($feld[$a][2]), ENT_QUOTES, "UTF-8")."</message>\n<room>".html_entity_decode(strip_tags($feld[$a][4]), ENT_QUOTES, "UTF-8")."</room>\n";
				echo "</dataset>\n";
			}
		}
		echo "</etchat>";	
	}
	
}