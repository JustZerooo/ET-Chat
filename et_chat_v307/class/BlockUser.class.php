<?php
/**
 * BlockUser, for user blocking in the session
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
class BlockUser extends EtChatConfig
{
	/**
	* Constructor
	*
	* @return void
	*/
	public function __construct (){
	
		session_start();
		
		// call parent Constructor from class EtChatConfig
		parent::__construct();
		
		// all documentc requested per AJAX should have this part to turn off the browser and proxy cache for any XHR request
		header('Cache-Control: no-store, no-cache, must-revalidate, pre-check=0, post-check=0, max-age=0');
		
		if(!is_array($_SESSION['etchat_'.$this->_prefix.'block_priv'])) $_SESSION['etchat_'.$this->_prefix.'block_priv'] = array();
		if(!is_array($_SESSION['etchat_'.$this->_prefix.'block_all'])) $_SESSION['etchat_'.$this->_prefix.'block_all'] = array();

		// Block all messages
		if (isset($_POST['block_all'])){
			// Der User ist bereits blokiert und wird wieder freigegeben
			// The user is blocked now, so hi will be decontrolled
			if (in_array($_POST['block_all'], $_SESSION['etchat_'.$this->_prefix.'block_all'])){
				$key_from_all = array_search($_POST['block_all'], $_SESSION['etchat_'.$this->_prefix.'block_all']);
				$_SESSION['etchat_'.$this->_prefix.'block_all'][$key_from_all]=99999999999;

				// Falls der User bereits in privat gesperrt ist, wird dieser Schlüssel gelöscht
				// If the user is blocked now by privat option, this key will be deleted
				$key_from_priv = array_search($_POST['block_all'], $_SESSION['etchat_'.$this->_prefix.'block_priv']);
				$_SESSION['etchat_'.$this->_prefix.'block_priv'][$key_from_priv]=99999999999;
			}
			// Der User wird erst blokiert
			// The user will be blocked by first time
			else {
        	    $_SESSION['etchat_'.$this->_prefix.'block_all'][] = $_POST['block_all'];

				// Falls der User bereits in privat gesperrt ist, wird dieser Schlüssel gelöscht
				// If the user is blocked now by privat option, this key will be deleted
				$key_from_priv = array_search($_POST['block_all'], $_SESSION['etchat_'.$this->_prefix.'block_priv']);
				$_SESSION['etchat_'.$this->_prefix.'block_priv'][$key_from_priv]=99999999999;
             }

		}
		// Block private messages
		if (isset($_POST['block_priv'])){
			if (in_array($_POST['block_priv'], $_SESSION['etchat_'.$this->_prefix.'block_priv'])){
				$key_from_priv = array_search($_POST['block_priv'], $_SESSION['etchat_'.$this->_prefix.'block_priv']);
				$_SESSION['etchat_'.$this->_prefix.'block_priv'][$key_from_priv]=99999999999;


				// Falls der User bereits in all gesperrt ist, wird dieser Schlüssel gelöscht
				// If the user is blocked now by "all" option, this key will be deleted
				$key_from_all = array_search($_POST['block_priv'], $_SESSION['etchat_'.$this->_prefix.'block_all']);
				$_SESSION['etchat_'.$this->_prefix.'block_all'][$key_from_all]=99999999999;
			}
			else {
				$_SESSION['etchat_'.$this->_prefix.'block_priv'][] = $_POST['block_priv'];

				// Falls der User bereits in all gesperrt ist, wird dieser Schlüssel gelöscht
				// If the user is blocked now by "all" option, this key will be deleted
				$key_from_all = array_search($_POST['block_priv'], $_SESSION['etchat_'.$this->_prefix.'block_all']);
				$_SESSION['etchat_'.$this->_prefix.'block_all'][$key_from_all]=99999999999;
			}
		}

		// Make output
		if (isset($_POST['show'])){
			if (in_array($_POST['show'], $_SESSION['etchat_'.$this->_prefix.'block_priv'])) echo "priv";
			if (in_array($_POST['show'], $_SESSION['etchat_'.$this->_prefix.'block_all'])) echo "all";
		}
	}
}