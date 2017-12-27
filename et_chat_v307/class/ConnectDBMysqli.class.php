<?php
/**
 * Class ConnectDBMysqli, database connectivity class based on Mysqli Extension
 *
 * All database connectivity in whole chat have to use this class to communicate whith the DB
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.6$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Beta 1.0
 */
 
class ConnectDBMysqli extends EtChatConfig{
	
	/**
	* MySQLi Obj with DB connect
	* @var MySQLi
	*/
	protected $_connid;
	
	/**
	* last inserted id in the db after any sql-manipulation-statements
	* @var int
	*/
	public $lastId;
	
	/**
	* Constructor,  creates a db connectivity
	*
	* @uses MySQLi object creation
	* @return void
	*/
	public function __construct (){
	
		// call parent Constructor from class EtChatConfig
		parent::__construct();
	
		$this->_connid = new mysqli($this->_sqlhost, $this->_sqluser, $this->_sqlpass, $this->_database);

		// check connection 
		if ($this->_connid->connect_error) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			return false;
		}

	}
	
	/**
	* for making sql-select-queries
	*
	* @param  string $sql 
	* @uses MySQLi::query()	
	* @uses MySQLi::fetch_array()
	* @return array, with the datasets
	*/
	public function sqlGet($sql){
	
		// set query
		$result = $this->_connid->query($sql);
		
		$a=0;

		while($row = $result->fetch_array(MYSQLI_NUM)) {
			$b=0;
			foreach ($row as $field) {
				$resultArray[$a][$b]=$field;
				$b++;
			}
			$a++;
		}
		
		if (!is_array($resultArray)) return 0;
		return $resultArray;
	}
	
	/**
	* for making sql-manipulation-queries
	*
	* @param  string $sql 
	* @uses MySQLi::query()
	* @uses MySQLi::$insert_id
	* @return int, number of manipulated datasets
	*/
	public function sqlSet($sql){
		
		// set query
		$datasets = $this->_connid->query($sql);
		
		// get last table ID after manipulation
		$this->lastId = $this->_connid->insert_id;
		return $datasets;
	}
	
	/**
	* close db connection
	*
	* @uses MySQLi::close()
	* @return void
	*/
	public function close(){
		$this->_connid->close();
	}
}