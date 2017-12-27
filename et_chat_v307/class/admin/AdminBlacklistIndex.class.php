<?php
/**
 * Class BlacklistIndex
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */

class AdminBlacklistIndex extends DbConectionMaker
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
        $lang=$langObj->getLang()->admin[0]->admin_blacklist[0];
        
        
        if ($_SESSION['etchat_'.$this->_prefix.'user_priv']=="admin"){
            
            // Checksum to prevent this: http://www.sedesign.de/sed/forum/forum_entry.php?id=7154
            $_SESSION['etchat_'.$this->_prefix.'CheckSum4RegUserEdit'] = rand(1,999999999);
            
            $this->dbObj->sqlSet("delete FROM {$this->_prefix}etchat_blacklist where etchat_blacklist_time < ".date('U'));
            $feld=$this->dbObj->sqlGet("SELECT etchat_blacklist_ip, etchat_blacklist_id, etchat_username, etchat_userprivilegien, etchat_blacklist_time FROM {$this->_prefix}etchat_user, {$this->_prefix}etchat_blacklist WHERE etchat_user_id = etchat_blacklist_userid");
            $this->dbObj->close();
            
            // initialize Template
            $this->initTemplate($lang,$feld);
            
        }else{
            echo $lang->error[0]->tagData;
            return false;
        }
    }
    
    /**
    * Initializer for template
    *
    * @return void
    */
    private function initTemplate($lang, $feld){
        // Include Template
        include_once("styles/admin_tpl/indexBlacklist.tpl.html");
    }
}