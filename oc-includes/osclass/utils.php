<?php

/*
 *      OSCLass – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2010 OSCLASS
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Converts a string to lowercase respecting the charset.
 */
function osc_lowerCase($string) {
    if (is_null($string))
        return false;
    return mb_convert_case(__($string), MB_CASE_LOWER, "UTF-8");
}

/**
 * Makes an HTTP redirection and ensures the correct script abortion.
 */
function osc_redirectTo($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Tries to redirect to the previous page and falls to the one passed by parameter if the referer does not exist.
 */
function osc_redirectToReferer($default) {
    $url = $default;
    if (isset($_SERVER['HTTP_REFERER']))
        $url = $_SERVER['HTTP_REFERER'];
    header('Location: ' . $url);
    exit;
}

/**
 * Tries to delete the directory recursivaly.
 * @return true on success.
 */
function osc_deleteDir($path) {
    if (!is_dir($path))
        return false;

    $fd = @opendir($path);
    if (!$fd)
        return false;

    while ($file = @readdir($fd)) {
        if ($file != '.' && $file != '..') {
            if (!is_dir($path . '/' . $file)) {
                if (!@unlink($path . '/' . $file)) {
                    closedir($fd);
                    return false;
                } else {
                    osc_deleteDir($path . '/' . $file);
                }
            } else {
                osc_deleteDir($path . '/' . $file);
            }
        }
    }
    closedir($fd);

    return @rmdir($path);
}

/**
 * Unpack a ZIP file into the specific path in the second parameter.
 * @return true on success.
 */
function osc_packageExtract($zipPath, $path) {
    if (!@mkdir($path, 0666)) {
        return false;
    }
    
    chmod($path, 0777);

    $zip = new ZipArchive;
    if ($zip->open($zipPath) === true) {
        $zip->extractTo($path);
        $zip->close();
        return true;
    } else {
        return false;
    }
}

/**
 * Serialize the data (usefull at plugins activation)
 * @return the data serialized
 */
function osc_serialize($data) {

    if (!is_serialized($data)) {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }
    }

    return $data;
}

/**
 * Unserialize the data (usefull at plugins activation)
 * @return the data unserialized
 */
function osc_unserialize($data) {
    if (is_serialized($data)) { // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize($data);
    }

    return $data;
}

/**
 * Checks is $data is serialized or not
 * @return bool False if not serialized and true if it was.
 */
function is_serialized($data) {
    // if it isn't a string, it isn't serialized
    if (!is_string($data))
        return false;
    $data = trim($data);
    if ('N;' == $data)
        return true;
    if (!preg_match('/^([adObis]):/', $data, $badions))
        return false;
    switch ($badions[1]) {
        case 'a' :
        case 'O' :
        case 's' :
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                return true;
            break;
    }
    return false;
}

/**
 * Check whether serialized data is of string type.
 * @return bool False if not a serialized string, true if it is.
 */
function is_serialized_string($data) {
    // if it isn't a string, it isn't a serialized string
    if (!is_string($data))
        return false;
    $data = trim($data);
    if (preg_match('/^s:[0-9]+:.*;$/s', $data)) // this should fetch all serialized strings
        return true;
    return false;
}

/**
 * VERY BASIC
 * Perform a POST request, so we could launch fake-cron calls and other core-system calls without annoying the user
 */
function osc_doRequest($url, $_data) {

    if (function_exists('fputs')) {
        // convert variables array to string:
        $data = array();
        while (list($n, $v) = each($_data)) {
            $data[] = "$n=$v";
        }
        $data = implode('&', $data);
        // format --> test1=a&test2=b etc.
        // parse the given URL
        $url = parse_url($url);
        if ($url['scheme'] != 'http') {
            //die('Only HTTP request are supported !');
        }

        // extract host and path:
        $host = $url['host'];
        $path = $url['path'];

        // open a socket connection on port 80
        $fp = fsockopen($host, 80);

        // send the request headers:
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Referer: OSClass\r\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
        fputs($fp, "Content-length: " . strlen($data) . "\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        // close the socket connection:
        fclose($fp);
    }
}

function osc_sendMail($params) {
    require_once 'phpmailer/class.phpmailer.php';

    $prefManager = Preference::newInstance();
    $preferences = $prefManager->toArray();

    $mail = new PHPMailer;
    $mail->CharSet = "utf-8";

    if (isset($preferences['mailserver_auth']) && $preferences['mailserver_auth']) {
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Username = ( isset($params['username']) ) ? $params['username'] : $preferences['mailserver_username'];
        $mail->Password = ( isset($params['password']) ) ? $params['password'] : $preferences['mailserver_password'];
    }

    $mail->Host = ( isset($params['host']) ) ? $params['host'] : $preferences['mailserver_host'];
    $mail->Port = ( isset($params['port']) ) ? $params['port'] : $preferences['mailserver_port'];
    $mail->From = ( isset($params['from']) ) ? $params['from'] : $preferences['contactEmail'];
    $mail->FromName = ( isset($params['from_name']) ) ? $params['from_name'] : $preferences['pageTitle'] ;
    $mail->Subject = ( isset($params['subject']) ) ? $params['subject'] : '' ;
    $mail->Body = ( isset($params['body']) ) ? $params['body'] : '' ;
    $mail->AltBody = ( isset($params['alt_body']) ) ? $params['alt_body'] : '' ;
    $to = ( isset($params['to']) ) ? $params['to'] : '' ;
    $to_name = ( isset($params['to_name']) ) ? $params['to_name'] : '' ;
    if ( isset($params['add_bbc']) )
        $mail->AddBCC($params['add_bbc']);

    $mail->IsHTML(true);
    $mail->AddAddress($to, $to_name);

    if (!$mail->Send()) {
        //echo $mail->ErrorInfo;
		return false;
    } else {
		return true;
	}
}


function osc_mailBeauty($text, $params) {

	$text = str_ireplace($params[0], $params[1], $text);

	$preferences = Preference::newInstance()->toArray();
	$kwords = array('{WEB_URL}', '{WEB_TITLE}', '{CURRENT_DATE}', '{HOUR}');
	$rwords = array(ABS_WEB_URL, $preferences['pageTitle'], date('Y-m-d H:i:s'), date('H:i'));

	$text = str_ireplace($kwords, $rwords, $text);
	return $text;

}




function osc_copy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755)) {
	$result=false;
	if (is_file($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if (!file_exists($dest)) {
				cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
			}
			$__dest=$dest."/".basename($source);
		} else {
			$__dest=$dest;
		}
		if(function_exists('copy')) {
			$result=copy($source, $__dest);
		} else {
			$result=osc_copyemz($source, $__dest);
		}
		chmod($__dest,$options['filePermission']);

	} elseif(is_dir($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if ($source[strlen($source)-1]=='/') {
				//Copy only contents
			} else {
				//Change parent itself and its contents
				$dest=$dest.basename($source);
				@mkdir($dest);
				chmod($dest,$options['filePermission']);
			}
		} else {
			if ($source[strlen($source)-1]=='/') {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			} else {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
			}
		}

		$dirHandle=opendir($source);
		while($file=readdir($dirHandle)) {
			if($file!="." && $file!="..") {
				if(!is_dir($source."/".$file)) {
					$__dest=$dest."/".$file;
				} else {
					$__dest=$dest."/".$file;
				}
				//echo "$source/$file ||| $__dest<br />";
				$result=osc_copy($source."/".$file, $__dest, $options);
			}
		}
		closedir($dirHandle);

	} else {
		$result=false;
	}
	return $result;
}



function osc_copyemz($file1,$file2){
	$contentx =@file_get_contents($file1);
	$openedfile = fopen($file2, "w");
	fwrite($openedfile, $contentx);
	fclose($openedfile);
	if ($contentx === FALSE) {
		$status=false;
	} else {
		$status=true;
	}
                   
	return $status;
} 




function osc_dbdump($db_filename = null) {

	global $db_file;
	if($db_filename==null) {
		$db_file = APP_PATH."/OSClass.mysqlbackup".date('YmdHis').".sql";
	} else {
		$db_file = $db_filename;
	}
	$f = fopen($db_file, "a");

	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if (!$link) {
	   echo 'Could not connect: ' . mysql_error();
	} else {
		$db_selected = mysql_select_db(DB_NAME, $link);
		if (!$db_selected) {
			echo 'Can\'t use $mysql_database : ' . mysql_error();
		} else {
			$sql="show tables;";
			$result= mysql_query($sql);
			if($result) {
				fwrite($f, "/* OSCLASS MYSQL Autobackup (".date('Y-m-d H:i:s').") */\n");
				fclose($f);
				while($row = mysql_fetch_row($result)) {
					osc_dump_table_structure($row[0]);
					osc_dump_table_data($row[0]);
				}
			} else {
				fwrite($f, "/* no tables in ".DB_NAME." */\n");
				fclose();
			}
			mysql_free_result($result);
			mysql_close();
		}
	}


}

function osc_dump_table_structure($table) {

	global $db_file;
	$f = fopen($db_file, "a");

	fwrite($f, "/* Table structure for table `$table` */\n");

	// DANGEROUS LINE
	//fwrite($f, "DROP TABLE IF EXISTS `$table`;\n\n";

	$sql="show create table `$table`; ";
	$result=mysql_query($sql);
	if($result) {
		if($row = mysql_fetch_assoc($result)) {
			fwrite($f, $row['Create Table'].";\n\n");
		}
	}
	mysql_free_result($result);
	fclose($f);
}

function osc_dump_table_data($table) {

	global $db_file;
	$f = fopen($db_file, "a");

	$output = "";
	$sql="select * from `$table`;";
	$result=mysql_query($sql);
	if($result) {
		$num_rows= mysql_num_rows($result);
		$num_fields= mysql_num_fields($result);

		if( $num_rows > 0) {
			fwrite($f, "/* dumping data for table `$table` */\n");

			$field_type=array();
			$i=0;
			while( $i < $num_fields) {
				$meta= mysql_fetch_field($result, $i);
				array_push($field_type, $meta->type);
				$i++;
			}

			fwrite($f, "insert into `$table` values\n");
			$index=0;
			while( $row= mysql_fetch_row($result)) {
				fwrite($f, "(");
				for($i=0; $i < $num_fields; $i++) {
					if(is_null( $row[$i])) {
						fwrite($f, "null");
					} else {
						switch( $field_type[$i]) {
							case 'int':
								fwrite($f, $row[$i]);
								break;
							case 'string':
							case 'blob' :
							default:
								fwrite($f, "'".mysql_real_escape_string($row[$i])."'");

						}
					}
					if($i < $num_fields-1) {
						fwrite($f, ",");
					}
				}
				fwrite($f, ")");

				if($index < $num_rows-1) {
					fwrite($f, ",");
				} else {
					fwrite($f, ";");
				}
				fwrite($f, "\n");

				$index++;
			}
		}
	}
	mysql_free_result($result);
	fwrite($f, "\n");
	fclose($f);
}


function osc_downloadFile($sourceFile, $downloadedFile) {

	set_time_limit(0);
	ini_set('display_errors',true);
			
	$fp = fopen (APP_PATH.'/oc-content/downloads/'.$downloadedFile, 'w+');
	$ch = curl_init($sourceFile);
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);

}

function osc_zipFolder($archive_folder, $archive_name) {

	$zip = new ZipArchive;
	if ($zip -> open($archive_name, ZipArchive::CREATE) === TRUE) {
		$dir = preg_replace('/[\/]{2,}/', '/', $archive_folder."/");
   
		$dirs = array($dir);
		while (count($dirs)) {
			$dir = current($dirs);
			$zip -> addEmptyDir(str_replace(APP_PATH.'/', '', $dir));
      
			$dh = opendir($dir);
			while (false !== ($_file = readdir($dh))) {
				
				if ($_file != '.' && $_file != '..') {
					if (is_file($dir.$_file)) {
						$zip -> addFile($dir.$_file, str_replace(APP_PATH.'/', '', $dir.$_file));
					} elseif (is_dir($dir.$_file)) {
						$dirs[] = $dir.$_file."/";
					}
				}
			}
			closedir($dh);
			array_shift($dirs);
		}   
		$zip -> close();
		return true;
	} else {
		return false;
	}

}

?>