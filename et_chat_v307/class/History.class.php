<?php
/**
 * Class History, shows the history of the chatmessages
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class History extends DbConectionMaker
{

	/**
	* Constructor
	*
	* @uses ConnectDB::sqlGet()	
	* @uses ConnectDB::close()	
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @uses StaticMethods::filtering()
	* @uses RoomAllowed 
	* @uses RoomAllowed::$room_status if the room is open/closed/pw-protected
	* @return void
	*/
	public function __construct (){ 
		
		// call parent Constructor from class DbConectionMaker
		parent::__construct(); 

		session_start();

		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		header('content-type: application/json; charset=utf-8');
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang()->history_php[0];
		
		$privates=false;

		if(!empty($_POST['roomid'])) {
			if ($_POST['roomid']!="priv")
				$raumauswahl = "AND ({$this->_prefix}etchat_messages.etchat_fid_room = ".(int)$_POST['roomid']." OR {$this->_prefix}etchat_messages.etchat_fid_room = 0)";
			else
				$privates=true;
		}
		else {
			$raumauswahl = "AND ({$this->_prefix}etchat_messages.etchat_fid_room = 1 OR {$this->_prefix}etchat_messages.etchat_fid_room = 0)";
			$_POST['roomid']=1;
		}
		
		
		if($privates)
			$counted=$this->dbObj->sqlGet("
			SELECT count(etchat_id)
			FROM ({$this->_prefix}etchat_messages JOIN {$this->_prefix}etchat_rooms ON {$this->_prefix}etchat_messages.etchat_fid_room = {$this->_prefix}etchat_rooms.etchat_id_room) INNER JOIN {$this->_prefix}etchat_user ON {$this->_prefix}etchat_messages.etchat_user_fid = {$this->_prefix}etchat_user.etchat_user_id
			WHERE ({$this->_prefix}etchat_messages.etchat_user_fid=".$_SESSION['etchat_'.$this->_prefix.'user_id']." AND {$this->_prefix}etchat_messages.etchat_privat>0) OR {$this->_prefix}etchat_messages.etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id']);
		else
			$counted=$this->dbObj->sqlGet("
			SELECT count(etchat_id)
			FROM ({$this->_prefix}etchat_messages LEFT JOIN {$this->_prefix}etchat_rooms ON {$this->_prefix}etchat_messages.etchat_fid_room = {$this->_prefix}etchat_rooms.etchat_id_room ".$raumauswahl.") INNER JOIN {$this->_prefix}etchat_user ON {$this->_prefix}etchat_messages.etchat_user_fid = {$this->_prefix}etchat_user.etchat_user_id
			WHERE  {$this->_prefix}etchat_messages.etchat_privat=0 ".$raumauswahl);
			
		$pro_seite = 40;
		$site=$_POST['site']-1;
		$von = $site*$pro_seite;
		
		if ($this->_usedDatabase == "mysql") $limit = "LIMIT $von, $pro_seite";
		if ($this->_usedDatabase == "pgsql") $limit = "LIMIT $pro_seite OFFSET $von";


		
		if($privates)
			$feld=$this->dbObj->sqlGet("
			SELECT {$this->_prefix}etchat_messages.etchat_id, {$this->_prefix}etchat_user.etchat_username, {$this->_prefix}etchat_messages.etchat_text, {$this->_prefix}etchat_messages.etchat_timestamp, {$this->_prefix}etchat_rooms.etchat_roomname, {$this->_prefix}etchat_messages.etchat_privat, {$this->_prefix}etchat_messages.etchat_user_fid, {$this->_prefix}etchat_messages.etchat_text_css
			FROM ({$this->_prefix}etchat_messages JOIN {$this->_prefix}etchat_rooms ON {$this->_prefix}etchat_messages.etchat_fid_room = {$this->_prefix}etchat_rooms.etchat_id_room) INNER JOIN {$this->_prefix}etchat_user ON {$this->_prefix}etchat_messages.etchat_user_fid = {$this->_prefix}etchat_user.etchat_user_id
			WHERE ({$this->_prefix}etchat_messages.etchat_user_fid=".$_SESSION['etchat_'.$this->_prefix.'user_id']." AND {$this->_prefix}etchat_messages.etchat_privat>0) OR {$this->_prefix}etchat_messages.etchat_privat=".$_SESSION['etchat_'.$this->_prefix.'user_id']."
			ORDER BY {$this->_prefix}etchat_messages.etchat_id DESC $limit");
		else
			$feld=$this->dbObj->sqlGet("
			SELECT {$this->_prefix}etchat_messages.etchat_id, {$this->_prefix}etchat_user.etchat_username, {$this->_prefix}etchat_messages.etchat_text, {$this->_prefix}etchat_messages.etchat_timestamp, {$this->_prefix}etchat_rooms.etchat_roomname, {$this->_prefix}etchat_messages.etchat_privat, {$this->_prefix}etchat_messages.etchat_user_fid, {$this->_prefix}etchat_messages.etchat_text_css
			FROM ({$this->_prefix}etchat_messages LEFT JOIN {$this->_prefix}etchat_rooms ON {$this->_prefix}etchat_messages.etchat_fid_room = {$this->_prefix}etchat_rooms.etchat_id_room ".$raumauswahl.") INNER JOIN {$this->_prefix}etchat_user ON {$this->_prefix}etchat_messages.etchat_user_fid = {$this->_prefix}etchat_user.etchat_user_id
			WHERE  {$this->_prefix}etchat_messages.etchat_privat=0 ".$raumauswahl."
			ORDER BY {$this->_prefix}etchat_messages.etchat_id DESC $limit");
		

		
		echo "<div id=\"history_seiten\" style=\"margin: 2px;\">";
		
		$sitemakerObj = new Sitemaker($pro_seite, $counted[0][0]);
		$sitemakerObj->make($_POST['site'], "historysite_#site#", $lang->site[0]->tagData, $lang->site_of[0]->tagData);
		$sitemakerObj->show();
		
		echo "
		<form style=\"display:inline;\">
		&nbsp;&nbsp;&nbsp; ".$lang->room[0]->tagData.": <select name=\"raum_in_history\" id=\"raum_in_history\" size=\"1\">
		<option value=\"priv\">".$lang->priv[0]->tagData."</option>";

		$rooms=$this->dbObj->sqlGet("SELECT etchat_id_room, etchat_roomname, etchat_room_goup FROM {$this->_prefix}etchat_rooms");
		
		foreach($rooms as $each_room){

			$room_allowed=new RoomAllowed($each_room[2], $each_room[0]);

			if ($room_allowed->room_status==1){
				$selected =($_POST['roomid']==$each_room[0]) ? "selected" : "";
				echo "<option value=\"".$each_room[0]."\" ".$selected.">".$each_room[1]."</option>";
			}
		}

		echo"</select>";

		if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin" || $_SESSION['etchat_'.$this->_prefix.'user_priv']=="mod") echo"&nbsp;&nbsp;&nbsp;".$lang->export[0]->tagData." <a href=\"#\" id=\"export_excel\">Excel</a> | <a href=\"#\" id=\"export_csv\">CSV</a> | <a href=\"#\" id=\"export_xml\">XML</a>";

		echo"</form>
		</div>
		<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" style=\"border:1px solid black;\" width=\"98%\">";
		echo "<tr class=\"kopf\">

		<td style=\"padding:2px\"><b>ID</b></td>
		<td style=\"padding:2px\"><b>".$lang->user[0]->tagData."</b></td>
		<td style=\"padding:2px\"><b>".$lang->date[0]->tagData."</b></td>
		<td style=\"padding:2px\"><b>".$lang->text[0]->tagData."</b></td>
		<td style=\"padding:2px\"><b>".$lang->room[0]->tagData."</b></td>
		</tr>";

		$sml = $this->dbObj->sqlGet("SELECT etchat_smileys_sign, etchat_smileys_img FROM {$this->_prefix}etchat_smileys");

		if (is_array($feld)){
			for ($a=0; $a < count($feld); $a++){
				if($feld[$a][5]==0){
					if($a%2==1) echo "<tr class=\"ungerade\" id=\"tr".$feld[$a][0]."\" >";
					else echo "<tr class=\"gerade\" id=\"tr".$feld[$a][0]."\"  >";
					}
				if ($feld[$a][5]!=0 && $feld[$a][6]==$_SESSION['etchat_'.$this->_prefix.'user_id']) {
					echo "<tr class=\"privat_von\" id=\"tr".$feld[$a][0]."\"  >";
					$private_at=$this->dbObj->sqlGet("SELECT etchat_username from {$this->_prefix}etchat_user where etchat_user_id = ".(int)$feld[$a][5]);
					$private_at_user = (is_array($private_at)) ? "&nbsp;<span style=\"font-size: 1.2em;\"><b>>>></b></span>&nbsp;".$private_at[0][0] : "";
				}
				if ($feld[$a][5] == $_SESSION['etchat_'.$this->_prefix.'user_id']) {
					echo "<tr class=\"privat_nach\" id=\"tr".$feld[$a][0]."\"  >";
					$private_at_user = "";
				}
				
				$message = StaticMethods::filtering($feld[$a][2], $sml, $this->_prefix);
				if (substr($message,0,8) == "/window:")
					$message = "<img src=\"./img/privat_win.png\" /> ".substr($message,8,strlen($message));
				
				echo "
				<td style=\"padding:2px\">".$feld[$a][0]."</td>
				<td style=\"padding:2px\">".$feld[$a][1].$private_at_user."</td>
				<td style=\"padding:2px\">".date("d.m.Y (H:i)",$feld[$a][3])."</td>
				<td style=\"padding:2px\">".$message."</td>
				<td style=\"padding:2px\">".$feld[$a][4]."</td>";
				echo "</tr>";
			}
		}
		else echo "<tr><td colspan=\"5\">".$lang->empty[0]->tagData."</td></tr>";
		echo "</table>";
		
		$this->dbObj->close();
	}
}
