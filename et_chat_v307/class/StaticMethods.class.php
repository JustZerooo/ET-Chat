<?php
/**
 * Class StaticMethods, contans only the simple methond for static use
 *
 * LICENSE: CREATIVE COMMONS PUBLIC LICENSE  "Namensnennung — Nicht-kommerziell 2.0"
 *
 * @copyright  2010 <SEDesign />
 * @license    http://creativecommons.org/licenses/by-nc/2.0/de/
 * @version    $3.0.7$
 * @link       http://www.sedesign.de/de_produkte_chat-v3.html
 * @since      File available since Alpha 2.0
 */

class StaticMethods{

	/**
	* Message filter, replaces smileys with images and "bad words"
	*
	* @param string $str, message text
	* @param Array  $sml, Smileys dataset
	* @return String
	*/
	static function filtering($str, $sml, $_prefix){
		
		//to remove all non printable characters in a string, otherwise JS error
		$str = preg_replace('/[\x00-\x1F]/', '', $str);
		
		//replace smileys
		for ($a=0; $a<count($sml); $a++){
                 $img = getimagesize("./".$sml[$a][1]);
                 //$str = str_replace($sml[$a][0], "<img src=\"".$sml[$a][1]."\" ".$img[3].">", $str, $count);
				 $str = str_replace($sml[$a][0], "<img src=\"".$sml[$a][1]."\" ".$img[3]." id=\"smilchat_".$sml[$a][0]."\" style=\"cursor:pointer\">", $str, $count);
				 if ($count>0) $count_all+=$count;
		}
		
		if ($count_all > 8) $str = strip_tags($str);
		
		// create links from URIs
		if (stripos($str, ']http://')===false &&  stripos($str, ']https://')===false)
				$str = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a target=\"_blank\" href=\"$1\">$1</a>",$str);
		else {
				$str = str_replace("http://www.youtube.com/watch?v=", "", $str);
				$str = str_replace("https://www.youtube.com/watch?v=", "", $str);
		}

			
		// Bad Word Filter
		
		if (!isset($_SESSION['etchat_'.$_prefix.'_badwords']) && file_exists("./lang/bad_words.xml")){
			$xml = @file_get_contents("./lang/bad_words.xml");
			$parser = new XMLParser($xml);
			$parser->Parse();

			foreach($parser->document->word as $bword){
				
				$exceptions = array();
				
				if (is_array($bword->except))
					foreach ($bword->except as $except)
						$exceptions[] = $except->tagData;
				
				$_SESSION['etchat_'.$_prefix.'_badwords'][] = array(
					'in' => chop(trim($bword->tagAttrs['in'])),
					'out' => chop(trim($bword->tagAttrs['out'])),
					'except' => $exceptions
				);
			}
		}
		
		if (isset($_SESSION['etchat_'.$_prefix.'_badwords'])){
			
			foreach($_SESSION['etchat_'.$_prefix.'_badwords'] as $key =>$bword){
				if (count($bword['except'])>0)
					foreach($bword['except'] as $key_ex => $ex){
						$str = str_replace($ex, "<norepl>".$ex."</norepl>" , $str);
					}
			}
			
			foreach($_SESSION['etchat_'.$_prefix.'_badwords'] as $bword){
				$bad_word = $bword['in'];
				$good_word = $bword['out'];
				
				$pattern = '/(?<!\<norepl\>)'.$bad_word.'(?!\<\/norepl\>)/ui'; 
				$str = preg_replace($pattern, $good_word, $str); 
			}
			
			$str = str_ireplace("<norepl>", "", $str);
			$str = str_ireplace("</norepl>", "", $str);
		}
		
		
		$video = '<object width="425" height="344"><param name="wmode" value="transparent" name="movie" value="http://www.youtube.com/v/$1"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed wmode="transparent" src="http://www.youtube.com/v/$1" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="425" height="344"></embed></object>';
		
		if (substr($str, 0, 8)!="/window:"){
			if (stripos($str, '[img]')!==false && stripos($str, '[/img]')!==false){
				$image_path = preg_replace('/\[img\](.*?)\[\/img\]/', '$1', $str); 
					if (!empty($image_path) && stripos($str, '?admin')===false)
						$str="<img src=\"$image_path\" style=\"max-width:500px;max-height:300px;\">";
			}
			$str = preg_replace('/\[video\](.*?)\[\/video\]/', $video, $str);  
		}
		
		return $str;
	}
}