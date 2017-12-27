<?php
/**
 * Class Sitemaker creates the sites from long tables
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2009 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 1.0
 */
 
/*
$sitemakerObj = new Sitemaker($anzahl_der_messages_pro_seite, $counted[0][0]);
$sitemakerObj->make($seite, $absolut_dir.$kat."-#site#.html", "Seite", "von");
$print_site_count = $sitemakerObj->get();
*/


 class Sitemaker{

	/**
	* Created output to print
	* @var String
	*/
	private $print_site_count;
	
	/**
	* Messages on one site
	* @var int
	*/
	private $anzahl_der_messages_pro_seite;
	
	/**
	* Counted datasets
	* @var int
	*/
	private $counted;

	/**
	* Make link or just ID for JS
	* @var Bool
	*/
	public $href=false;
	
	/**
	* Constructor
	*
	* @return void
	*/
	public function __construct($anz, $count){
		$this->anzahl_der_messages_pro_seite = $anz; 
		$this->counted = $count; 
	}

	/**
	* Creates the arrows and so on
	*
	* @return void
	*/
 	public function make($seite, 
	$tpl, 
	$site_text = "Site",
	$of_text = "of",
	$minus_stop = "&lt;&lt;&lt;",
	$minus = "&lt;&lt;&lt;",
	$plus = "&gt;&gt;&gt;",
	$plus_stop = "&gt;&gt;&gt;"
	){
		
		$anzahl_der_seiten_ermittelt = (int)(($this->counted/$this->anzahl_der_messages_pro_seite)+0.99999999999999);
		
		if ($seite<1) $seite=1;
		if ($seite>$anzahl_der_seiten_ermittelt) $seite=$anzahl_der_seiten_ermittelt ;

		if ($this->href) {
			$href_inc_minus = str_replace ( '#site#', ($seite-1), $tpl );
			$href_inc_plus= str_replace ( '#site#', ($seite+1), $tpl );
		}
		else {
			$href_inc_minus ="#";
			$href_inc_plus ="#";
		}
		
		if ($seite==1) $this->print_site_count =  $minus_stop."&nbsp;&nbsp;";
		else $this->print_site_count = "<a class=\"sitemaker\" href=\"".$href_inc_minus."\" id=\"".str_replace ( '#site#', ($seite-1), $tpl )."\" title=\"Site -\">".$minus."</a>&nbsp;&nbsp;";


		$this->print_site_count .= $site_text."\n<form action=\"\" style=\"display:inline;\">";
		$this->print_site_count .="\n<div style=\"display:inline;\"><select id=\"site_selecter\" class=\"sitemaker_select\">\n";

                 for ($i=1; $i<=$anzahl_der_seiten_ermittelt; $i++) {
                       if ($seite == $i) $this->print_site_count .="<option value=\"".$i."\" selected=\"selected\">$i</option>\n";
                       else $this->print_site_count .= "<option value=\"".$i."\">$i</option>\n";
                 }

                 $this->print_site_count .="</select></div></form>";

		$this->print_site_count .= "&nbsp;".$of_text."&nbsp;".$anzahl_der_seiten_ermittelt."&nbsp;&nbsp;";

		if (($this->counted/$this->anzahl_der_messages_pro_seite) <= $seite) $this->print_site_count .= $plus;
		else $this->print_site_count .= "<a class=\"sitemaker\" href=\"".$href_inc_plus."\" id=\"".str_replace ( '#site#', ($seite+1), $tpl )."\"  title=\"Site +\">".$plus_stop."</a>";

	}

	/**
	* Print the result
	*
	* @return void
	*/
	function show(){
		echo $this->print_site_count;
	}
	
	/**
	* Return the result
	*
	* @return String
	*/
	function get(){
		return $this->print_site_count;
	}
} 

?>