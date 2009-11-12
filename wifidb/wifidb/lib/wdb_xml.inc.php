<?php
$xml_ver = array(
					"online"	=>
						array(
							"xml2ary"			=>	"1.0",
							"_del_p"			=>	"1.0",
							"ary2xml"			=>	"1.0",
							"ins2ary"			=>	"1.0",
							),
					"inhouse"	=>
						array(
							"import_xml"		=>	"1.0",
							"share_wpt"			=>	"1.0",
							"remove_share_wpt"	=>	"1.0",
							"update_wpt"		=>	"1.0"
							)
				);
class WDB_XML
{
	######################################################
	###   http://mysrc.blogspot.com/2007/02/php-xml-to-array-and-backwards.html
	######################################################
	function xml2ary(&$string) {
	    $parser = xml_parser_create();
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parse_into_struct($parser, $string, $vals, $index);
	    xml_parser_free($parser);

	    $mnary=array();
	    $ary=&$mnary;
	    foreach ($vals as $r) {
	        $t=$r['tag'];
	        if ($r['type']=='open') {
	            if (isset($ary[$t])) {
	                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
	                $cv=&$ary[$t][count($ary[$t])-1];
	            } else $cv=&$ary[$t];
	            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
	            $cv['_c']=array();
	            $cv['_c']['_p']=&$ary;
	            $ary=&$cv['_c'];

	        } elseif ($r['type']=='complete') {
	            if (isset($ary[$t])) { // same as open
	                if (isset($ary[$t][0])) $ary[$t][]=array(); else $ary[$t]=array($ary[$t], array());
	                $cv=&$ary[$t][count($ary[$t])-1];
	            } else $cv=&$ary[$t];
	            if (isset($r['attributes'])) {foreach ($r['attributes'] as $k=>$v) $cv['_a'][$k]=$v;}
	            $cv['_v']=(isset($r['value']) ? $r['value'] : '');

	        } elseif ($r['type']=='close') {
	            $ary=&$ary['_p'];
	        }
	    }    
	    
	    WDB_XML::_del_p($mnary);
	    return $mnary;
	}

	// _Internal: Remove recursion in result array
	function _del_p(&$ary) {
	    foreach ($ary as $k=>$v) {
	        if ($k==='_p') unset($ary[$k]);
	        elseif (is_array($ary[$k])) WDB_XML::_del_p($ary[$k]);
	    }
	}

	// Array to XML
	function ary2xml($cary, $d=0, $forcetag='') {
	    $res=array();
	    foreach ($cary as $tag=>$r) {
	        if (isset($r[0])) {
	            $res[]=WDB_XML::ary2xml($r, $d, $tag);
	        } else {
	            if ($forcetag) $tag=$forcetag;
	            $sp=str_repeat("\t", $d);
	            $res[]="$sp<$tag";
	            if (isset($r['_a'])) {foreach ($r['_a'] as $at=>$av) $res[]=" $at=\"$av\"";}
	            $res[]=">".((isset($r['_c'])) ? "\n" : '');
	            if (isset($r['_c'])) $res[]=WDB_XML::ary2xml($r['_c'], $d+1);
	            elseif (isset($r['_v'])) $res[]=$r['_v'];
	            $res[]=(isset($r['_c']) ? $sp : '')."</$tag>\n";
	        }
	        
	    }
	    return implode('', $res);
	}

	// Insert element into array
	function ins2ary(&$ary, $element, $pos) {
	    $ar1=array_slice($ary, 0, $pos); $ar1[]=$element;
	    $ary=array_merge($ar1, array_slice($ary, $pos));
	}
	####
	###################################################

	
	
	
	function import_xml($xml_file = '')
	{
		include_once($GLOBALS['half_path'].'/lib/security.inc.php');
		$sec = new security();
		
		$db = $GLOBALS['db'];
		$conn = $GLOBALS['conn'];
		$share_cache = $GLOBALS['share_cache'];
		
		$error = '';
		
		$buffer = file_get_contents($xml_file);
		$edit = preg_replace("/[\r\n\r\n]/","",$buffer);
		$xml=WDB_XML::xml2ary($edit);

		$count_gpx = count($xml['gpx']['_c']['wpt'])-1;
	#	echo $count."<BR>";
		$User = $sec->login_check();
	#	echo $User." <-----";
		if($User)
		{
			$User_cache = $User."_waypoints";
			if($count_gpx > 0)
			{
				$cnt_wpt = 0;
				foreach($xml['gpx']['_c']['wpt'] as $wpt)
				{
					$share = 0;
					$share_id = 0;
					$cat = "geocache";
					$u_date = "0000-00-00 00:00:00";
					
					$desc_exp = explode("by", $wpt['_c']['desc']['_v']);
					$desc_exp2 = explode(",",$desc_exp[1]);
					
					$author = addslashes($desc_exp2[0]);
					$name = addslashes($desc_exp[0]);
					$c_date = date("Y-m-d G:i:s");
					
					$URLname = addslashes($wpt['_c']['urlname']['_v']);
					$URL = addslashes($wpt['_c']['url']['_v']);
					$lat = addslashes($wpt['_a']['lat']);
					$long = addslashes($wpt['_a']['lon']);
					$type = addslashes($wpt['_c']['type']['_v'].":::".$wpt['_c']['sym']['_v']);
					
					$gcid = addslashes($wpt['_c']['name']['_v']);
			#		echo $gcid."<BR>";
					$notes = "No Notes";
					$link = addslashes($wpt['_c']['url']['_v']);
					
					$sql0 = "INSERT INTO `$db`.`$User_cache` (`id`, `name`, `gcid`, `notes`, `cat`, `type`, `lat`, `long`, `link`, `share`, `share_id`, `c_date`, `u_date`) VALUES (NULL, '$name','$gcid','$notes','$cat','$type','$lat','$long','$link','$share','$share_id','$c_date','$u_date')";
			#	echo $sql0."<BR>";
					if(mysql_query($sql0, $conn))
					{
					#	echo "Inserted!<BR>\r\n";
					}else
					{
						$error .= mysql_error($conn).":::";
					}
					$cnt_wpt++;
				}
				return array($User, $cnt_wpt);
			}else
			{
				echo "The file that you have uploaded is not of the correct Formatting, go back and get a good file you bad user...";
				return 0;
			}
		}else
		{
			return "login";
		}
	}

	function share_wpt($id)
	{
		include_once($GLOBALS['half_path'].'/lib/security.inc.php');
		$sec = new security();
		$id+0;
		$db = $GLOBALS['db'];
		$conn = $GLOBALS['conn'];
		$share_cache = $GLOBALS['share_cache'];
		$User = $sec->login_check();
	#	echo $User." <-----";
		if($User)
		{
			$User_cache = $User."_waypoints";
			$select = "SELECT * FROM `$db`.`$User_cache` WHERE `id` = '$id'";
			$return = mysql_query($select, $conn);
			$pri_wpt = mysql_fetch_array($return);
			
			$author = $User;
			$name = $pri_wpt['name'];
			$gcid = $pri_wpt['gcid'];
			$notes = $pri_wpt['notes'];
			$cat = $pri_wpt['cat'];
			$type = $pri_wpt['type'];
			$lat = $pri_wpt['lat'];
			$long = $pri_wpt['long'];
			$link = $pri_wpt['link'];
			$c_date = $pri_wpt['c_date'];
			$u_date = date("Y-m-d G:i:s");
			
			$sql1 = "INSERT INTO `$db`.`$share_cache` (`id`, `author`, `name`, `gcid`, `notes`, `cat`, `type`, `lat`, `long`, `link`, `c_date`, `u_date`, `pvt_id`) VALUES (NULL, '$author', '$name', '$gcid', '$notes', '$cat', '$type', '$lat', '$long', '$link', '$c_date', '$u_date', '$id')";
			if(mysql_query($sql1, $conn))
			{
				$select = "SELECT `id` FROM `$db`.`$share_cache` WHERE `gcid` LIKE '$gcid' AND `name` LIKE '$name'";
		#		echo $select."<BR>";
				$return = mysql_query($select, $conn);
				$shr_wpt = mysql_fetch_array($return);
				$share_id = $shr_wpt['id'];
		#		echo $share_id."<BR>";
				$update_user_share_flag = "UPDATE `$db`.`$User_cache` SET `share` = '1', `share_id` = '$share_id', `u_date` = '$u_date' WHERE `id` = '$id'";
		#		echo $update_user_share_flag."<BR>";
				if(mysql_query($update_user_share_flag, $conn))
				{
					return 1;
				}else
				{
					return mysql_error($conn);
				}
			}else
			{
				return mysql_error($conn);
			}
		}else
		{
			return "login";
		}
	}

	function remove_share_wpt($id)
	{
		include_once($GLOBALS['half_path'].'/lib/security.inc.php');
		$sec = new security();
		
		$id+0;
		$db = $GLOBALS['db'];
		$conn = $GLOBALS['conn'];
		$share_cache = $GLOBALS['share_cache'];
		
		$u_date = date("Y-m-d G:i:s");
		$User = $sec->login_check();
	#	echo $User." <-----";
		if($User)
		{
			$User_cache = $User."_waypoints";
			$remove = "DELETE FROM `$db`.`$share_cache` WHERE `$share_cache`.`pvt_id` = '$id' LIMIT 1";
			if(mysql_query($remove, $conn))
			{
				$update_user_share_flag = "UPDATE `$db`.`$User_cache` SET `share` = '0',`share_id` = '0', `u_date` = '$u_date' WHERE `$User_cache`.`id` = '$id' LIMIT 1";
				if(mysql_query($update_user_share_flag, $conn))
				{
					return 1;
				}else
				{
					return mysql_error($conn);
				}
			}else
			{
				return mysql_error($conn);
			}
		}else
		{
			return "login";
		}
	}

	function update_wpt($id = 0, $name = '', $gcid = '', $notes = '', $cat = '', $type = '' , $lat = '', $long = '', $link = '')
	{
		include_once($GLOBALS['half_path'].'/lib/security.inc.php');
		$sec = new security();
		
		$db = $GLOBALS['db'];
		$conn = $GLOBALS['conn'];
		$share_cache = $GLOBALS['share_cache'];
		$u_date = date("Y-m-d G:i:s");
		
		$User = $sec->login_check();
	#	echo $User." <-----";
		if($User)
		{
			$User_cache = $User."_waypoints";
			$sql0 = "UPDATE `$db`.`$User_cache` SET `name` = '$name', `gcid` = '$gcid', `notes` = '$notes', `cat` = '$cat', `type` = '$type', `lat` = '$lat', `long` = '$long', `link` = '$link', `u_date` = '$u_date' WHERE `$User_cache`.`id` = '$id' LIMIT 1";
			if(mysql_query($sql0, $conn))
			{
				$select = "SELECT `share`, `share_id` FROM `$db`.`$User_cache` WHERE `id` = '$id'";
				
				$return = mysql_query($select, $conn);
				$shr_wpt = mysql_fetch_array($return);
				$share_id = $shr_wpt['share_id'];
				$share = $shr_wpt['share'];
				if($share == 1)
				{
					$sql1 = "UPDATE `$db`.`$share_cache` SET `author` = '$username', `name` = '$name ', `gcid` = '$gcid ', `notes` = '$notes', `cat` = '$cat', `type` = '$type', `lat` = '$lat', `long` = '$long', `link` = '$link', `u_date` = '$u_date' WHERE `$share_cache`.`id` = '$share_id' LIMIT 1";
					if(mysql_query($sql1, $conn))
					{
						return 1;
					}else
					{
						return mysql_error($conn);
					}
				}
				return 1;
			}else
			{
				return mysql_error($conn);
			}
		}else
		{
			return "login";
		}
	}
	
}
?>