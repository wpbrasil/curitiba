<?php
/*
 * This scripts changes all occurrences of a URL in your WordPress database.
 * 
 * It will compare the $newUrl against the URL saved on you wp_options table (site_url), or in the domain column of the wp_sites table for a Multi Site installation.
 * 
 * It will handle serialized data and Objects stored in the database.
 * 
 * 
 */
ini_set("register_globals","off");


/** Start Editing here **/

$db_name = 'wordcamp';
$db_user = 'root';
$db_pass = '';
$db_host = 'localhost';
$tablePrefix = 'wp_';

// Here is the URL you want to change your installation to
$newUrl = '192.168.0.220/wordcamp'; // without http to the root of you wordPress instalation (eg. 192.168.1.100/wordpress, localhost/wordpress, mysite.com or www.mysite.com)

/** Stop editing **/



global $counter;
$counter = 0;

function check_and_unserialize($serialized) {
  
  if (preg_match_all('/O:(\d+):"(\S+?)"/', $serialized, $m)) {
    // print $serialized; 
    //$len = $m[1][0];
    //$class = substr($serialized, 4 + strlen($len), (int)$len);
    // print_r($m); die;
    $class = $m[2][0];
    eval ("if (!class_exists($class)) { class $class {}  }");
  }
  
  $unserialized = unserialize($serialized);
  return $unserialized;
	 
}

function replace_recursive($data) {
    global $oldUrl, $newUrl, $counter;  
    if (is_array($data)) {
        foreach ($data as $key => $info) {
            if ( !is_array($info) && !is_object($info)) {
                if (strpos($info, $oldUrl) !== false) {
                    $data[$key] = str_replace($oldUrl, $newUrl, $info);
                    $counter ++;
                }
            } else {
                $data[$key] = replace_recursive($data[$key]);
            }
        }
        
    } elseif (is_object($data)) {
    
        foreach ($data as $key => $info) {
            if ( !is_array($info) && !is_object($info)) {
                if (strpos($info, $oldUrl) !== false) {
                    $data->$key = str_replace($oldUrl, $newUrl, $info);
                    $counter ++;
                }
            } else {
                $data->$key = replace_recursive($data->$key);
            }
        }
    }
    
    return $data;
}

function is_serialized( $data ) {
    // if it isn't a string, it isn't serialized
    if ( !is_string( $data ) )
        return false;
    $data = trim( $data );
    if ( 'N;' == $data )
        return true;
    if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
        return false;
    switch ( $badions[1] ) {
        case 'a' :
        case 'O' :
        case 's' :
            if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                return true;
            break;
    }
    return false;
}


$con = mysql_connect($db_host, $db_user, $db_pass);
if (!$con)
  die('Could not connect: ' . mysql_error());

mysql_select_db($db_name, $con);

mysql_query("SET NAMES 'utf8'");

$oldUrl = mysql_query("SELECT option_value FROM {$tablePrefix}options WHERE option_name = 'siteurl'");
if ($oldUrl) $oldUrl = mysql_fetch_object($oldUrl);
if (is_object($oldUrl)) $oldUrl = $oldUrl->option_value;

if (!$oldUrl) {
    // Multi Site installation
    $oldUrl = mysql_query("SELECT domain FROM {$tablePrefix}site WHERE id = 1");
    if ($oldUrl) $oldUrl = mysql_fetch_object($oldUrl);
    if (is_object($oldUrl)) $oldUrl = $oldUrl->domain;
    if (!$oldUrl) die('Nao foi encontrada a Url antiga do site');
} 

$oldUrl = str_replace('http://', '', $oldUrl);

if ($oldUrl == $newUrl) die('Site uses the same URL, nothing to change. Exiting.');

$result = mysql_query('SHOW TABLES');
$tables = array();
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    array_push($tables, $row[0]); 
}

foreach ($tables as $table) {
	
	$query_fields = mysql_query("DESC $table");

	// gets the primary key
	while ($row = mysql_fetch_array($query_fields)) {
		if ($row['Key'] == 'PRI') {
			$primaryKey = $row['Field'];
			break;
		}
	}
    
	
	$query_fields = mysql_query("DESC $table");
	
	while ($field = mysql_fetch_array($query_fields)) {
		
		if (strpos($field['Type'], 'int') === false) {
			
            
			echo '.';
			$fieldName = $field['Field'];
			
            $records_query = mysql_query("SELECT $fieldName, $primaryKey FROM $table");
			
			if ($records_query) {
				
				while ($record = mysql_fetch_object($records_query)) {
					
					if (strpos($record->$fieldName, $oldUrl) !== false) {

						
						if (is_serialized($record->$fieldName)) {


							
							$uns = check_and_unserialize($record->$fieldName);


							
							if (is_array($uns)) {


                                $uns = replace_recursive($uns);
	                            $newRecord = serialize($uns);
	                            $newRecord = addslashes($newRecord);
	                            
	                            mysql_query("UPDATE $table SET $fieldName = '$newRecord' WHERE $primaryKey = {$record->$primaryKey}");
	                            echo mysql_error();
							}
							
						} else {
							if (strpos($record->$fieldName, $oldUrl) !== false) {
	                            $newRecord = addslashes(str_replace($oldUrl, $newUrl, $record->$fieldName));
							}
							mysql_query("UPDATE $table SET $fieldName = '$newRecord' WHERE $primaryKey = {$record->$primaryKey}");
							echo mysql_error();
                            $counter ++;
						}
					}	
				}
			}	
		}
	}
}

echo '\nFinished. ', $counter, ' strings replaced';

?>
