<?php
/**
 * MessagesForJs, creates the JavaScript output with the messages in the choosen language
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class MessagesForJs extends EtChatConfig
{
	/**
	* Constructor
	*
	* @uses LangXml object creation
	* @uses LangXml::getLang() parser method
	* @return void
	*/
	public function __construct (){
	
		// call parent Constructor from class EtChatConfig
		parent::__construct();
		
		session_start();
		
		// all documentc requested per AJAX should have this part to turn off the browser and proxy cache for any XHR request
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		// Sets charset and content-type
		header('content-type: text/html; charset=utf-8');
		
		// create new LangXml Object
		$langObj = new LangXml();
		$lang=$langObj->getLang();
		
		$_SESSION['etchat_'.$this->_prefix.'chek_user_ajax_getted_data'] = array();
		
		echo "var lang_start_prop_link_1='".$lang->chat_js[0]->start_prop_link[0]->tagData."';\n";
		echo "var lang_start_prop_link_2='".$lang->chat_js[0]->start_prop_link[1]->tagData."';\n";
		echo "var lang_start_prop_link_3='".$lang->chat_js[0]->start_prop_link[2]->tagData."';\n";
		echo "var lang_start_prop_link_4='".$lang->chat_js[0]->start_prop_link[3]->tagData."';\n";
		echo "var lang_start_prop_link_5='".$lang->chat_js[0]->start_prop_link[4]->tagData."';\n";
		echo "var lang_start_prop_link_6='".$lang->chat_js[0]->start_prop_link[5]->tagData."';\n";
		echo "var lang_start_prop_link_7='".$lang->chat_js[0]->start_prop_link[6]->tagData."';\n";
		echo "var lang_start_prop_link_8='".$lang->chat_js[0]->start_prop_link[7]->tagData."';\n";
		echo "var lang_start_prop_link_9='".$lang->chat_js[0]->start_prop_link[8]->tagData."';\n";
		echo "var lang_start_prop_link_10='".$lang->chat_js[0]->start_prop_link[9]->tagData."';\n";
		
		echo "var lang_warning_user_away_1_1='".$lang->chat_js[0]->warning_user_away_1[0]->warning[0]->tagData."';\n";
		echo "var lang_warning_user_away_1_2='".$lang->chat_js[0]->warning_user_away_1[0]->warning[1]->tagData."';\n";
		
		echo "var lang_warning_user_away_2_1='".$lang->chat_js[0]->warning_user_away_2[0]->warning[0]->tagData."';\n";
		echo "var lang_warning_user_away_2_2='".$lang->chat_js[0]->warning_user_away_2[0]->warning[1]->tagData."';\n";
		
		echo "var lang_remove_pw_win_title='".$lang->chat_js[0]->remove_pw_win[0]->title[0]->tagData."';\n";
		echo "var lang_remove_pw_win_text_1='".$lang->chat_js[0]->remove_pw_win[0]->text[0]->tagData."';\n";
		echo "var lang_remove_pw_win_text_2='".$lang->chat_js[0]->remove_pw_win[0]->text[1]->tagData."';\n";
		echo "var lang_remove_pw_win_text_3='".$lang->chat_js[0]->remove_pw_win[0]->text[2]->tagData."';\n";
		echo "var lang_remove_pw_win_button='".$lang->chat_js[0]->remove_pw_win[0]->button[0]->tagData."';\n";
		
		echo "var lang_start_reg_link='".$lang->chat_js[0]->start_prop_register[0]->start_prop_link[0]->tagData."';\n";
		echo "var lang_start_reg_title='".$lang->chat_js[0]->start_prop_register[0]->title[0]->tagData."';\n";
		echo "var lang_start_reg_pw1='".$lang->chat_js[0]->start_prop_register[0]->pw1[0]->tagData."';\n";
		echo "var lang_start_reg_pw2='".$lang->chat_js[0]->start_prop_register[0]->pw2[0]->tagData."';\n";
		echo "var lang_start_reg_button_register='".$lang->chat_js[0]->start_prop_register[0]->button_register[0]->tagData."';\n";
		echo "var lang_start_reg_button_cancel='".$lang->chat_js[0]->start_prop_register[0]->button_cancel[0]->tagData."';\n";
		echo "var lang_start_reg_befor_registering='".$lang->chat_js[0]->start_prop_register[0]->befor_registering[0]->tagData."';\n";
		echo "var lang_start_reg_after_registering='".$lang->chat_js[0]->start_prop_register[0]->after_registering[0]->tagData."';\n";
		echo "var lang_start_reg_after_registering_link='".$lang->chat_js[0]->start_prop_register[0]->after_registering_link[0]->tagData."';\n";
		echo "var lang_start_reg_error='".$lang->chat_js[0]->start_prop_register[0]->error[0]->tagData."';\n";
		
		echo "var lang_start_1='".$lang->chat_js[0]->start[0]->tagData."';\n";
		
		echo "var lang_AjaxReadRequest_1='".$lang->chat_js[0]->ajaxreadrequest[0]->tagData."';\n";
		$_SESSION['etchat_'.$this->_prefix.'chek_user_ajax_getted_data'][] = $lang->chat_js[0]->ajaxreadrequest[0]->tagData;
		
		echo "var lang_receiveResultJSON_1='".$lang->chat_js[0]->receiveresultjson[0]->tagData."';\n";
		echo "var lang_receiveResultJSON_2='".$lang->chat_js[0]->receiveresultjson[1]->tagData."';\n";
		echo "var lang_receiveResultJSON_priv_1='".$lang->chat_js[0]->receiveresultjson_priv[0]->tagData."';\n";
		echo "var lang_receiveResultJSON_priv_2='".$lang->chat_js[0]->receiveresultjson_priv[1]->tagData."';\n";
		echo "var lang_receiveResultJSON_priv_3='".$lang->chat_js[0]->receiveresultjson_priv[2]->tagData."';\n";
		echo "var lang_send_1='".$lang->chat_js[0]->send[0]->tagData."';\n";
		echo "var lang_historyWindow_1='".$lang->chat_js[0]->historywindow[0]->tagData."';\n";
		echo "var lang_updateUserOnlineAnzeige_1='".$lang->chat_js[0]->updateuseronlineanzeige[0]->tagData."';\n";
		echo "var lang_updateUserOnlineAnzeige_2='".$lang->chat_js[0]->updateuseronlineanzeige[1]->tagData."';\n";
		echo "var lang_updateUserOnlineAnzeige_3='".$lang->chat_js[0]->updateuseronlineanzeige[2]->tagData."';\n";
		echo "var lang_changeUserEvent_privat_1='".$lang->chat_js[0]->changeuserevent[0]->privat[0]->tagData."';\n";
		echo "var lang_changeUserEvent_privat_2='".$lang->chat_js[0]->changeuserevent[0]->privat[1]->tagData."';\n";
		echo "var lang_titleAlert='".$lang->chat_js[0]->title_alert[0]->tagData."';\n";
		
		echo "var lang_changeUserEvent_room_1='".$lang->chat_js[0]->changeuserevent[0]->room[0]->tagData."';\n";
		$_SESSION['etchat_'.$this->_prefix.'chek_user_ajax_getted_data'][] = $lang->chat_js[0]->changeuserevent[0]->room[0]->tagData;
		
		echo "var lang_changeUserEvent_infoblock_1='".$lang->chat_js[0]->changeuserevent[0]->infoblock[0]->tagData."';\n";
		echo "var lang_changeUserEvent_infoblock_2='".$lang->chat_js[0]->changeuserevent[0]->infoblock[1]->tagData."';\n";
		echo "var lang_changeUserEvent_infoblock_3='".$lang->chat_js[0]->changeuserevent[0]->infoblock[2]->tagData."';\n";
		echo "var lang_changeUserEvent_infoblock_4='".$lang->chat_js[0]->changeuserevent[0]->infoblock[3]->tagData."';\n";
		echo "var lang_changeUserEvent_infoblock_5='".$lang->chat_js[0]->changeuserevent[0]->infoblock[4]->tagData."';\n";
		echo "var lang_changeUserEvent_infoblock_6='".$lang->chat_js[0]->changeuserevent[0]->infoblock[5]->tagData."';\n";
		echo "var lang_changeUserEvent_infoblock_7='".$lang->chat_js[0]->changeuserevent[0]->infoblock[6]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_1='".$lang->chat_js[0]->changeuserevent[0]->adminu[0]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_2='".$lang->chat_js[0]->changeuserevent[0]->adminu[1]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_3='".$lang->chat_js[0]->changeuserevent[0]->adminu[2]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_4='".$lang->chat_js[0]->changeuserevent[0]->adminu[3]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_1='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[0]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_2='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[1]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_3='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[2]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_4='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[3]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_5='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[4]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_6='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[5]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_7='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[6]->tagData."';\n";
		echo "var lang_changeUserEvent_adminu_opt_8='".$lang->chat_js[0]->changeuserevent[0]->adminu_opt[7]->tagData."';\n";
		echo "var lang_changeUserEvent_notallowedroom_1='".$lang->chat_js[0]->changeuserevent[0]->notallowedroom[0]->tagData."';\n";
		echo "var lang_changeUserEvent_notallowedroom_2='".$lang->chat_js[0]->changeuserevent[0]->notallowedroom[1]->tagData."';\n";
		echo "var lang_changeUserEvent_pwroom_1='".$lang->chat_js[0]->changeuserevent[0]->pwroom[0]->tagData."';\n";
		echo "var lang_changeUserEvent_pwroom_2='".$lang->chat_js[0]->changeuserevent[0]->pwroom[1]->tagData."';\n";
		echo "var lang_statuslink='".$lang->chat_js[0]->statuslink[0]->tagData."';\n";
		echo "var lang_status_imgname = new Array();\nvar lang_status_text = new Array();\nvar lang_status_rights = new Array();\n";
		foreach($lang->chat_js[0]->status as $status_value) echo "lang_status_imgname.push('".$status_value->tagAttrs['imagename']."');\n";
		foreach($lang->chat_js[0]->status as $status_value) echo "lang_status_text.push('".$status_value->tagData."');\n";
		foreach($lang->chat_js[0]->status as $status_value) echo "lang_status_rights.push('".$status_value->tagAttrs['rights']."');\n";

	}
}
