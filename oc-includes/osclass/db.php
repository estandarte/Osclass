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

class DB
{
    private $db = null ;
    private $db_errno = 0;
    private $db_error = 0;
    private $dbHost = null ;
    private $dbUser = null ;
    private $dbPassword = null ;
    private $dbName = null ;
    private $dbLogLevel = null ;
    private $msg = "" ;
    
    
    function __construct($dbHost, $dbUser, $dbPassword, $dbName, $dbLogLevel) {
        $this->dbHost = $dbHost ;
        $this->dbUser = $dbUser ;
        $this->dbPassword = $dbPassword ;
        $this->dbName = $dbName ;
        $this->dbLogLevel = $dbLogLevel ;
        
        $this->osc_dbConnect() ;
    }
    
    function __destruct() {
        $this->osc_dbClose() ;
    }
    
    //logging
    function debug($msg, $ok = true)
    {
        if($this->dbLogLevel != LOG_NONE) {
            $this->msg .= date("d/m/Y - H:i:s") . " " ;
            if($this->dbLogLevel == LOG_WEB) {
                if ($ok) $this->msg .= "<span style='background-color: #D0F5A9;' >[ OPERATION OK ] " ;
                else $this->msg .= "<span style='background-color: #F5A9A9;' >[ OPERATION FAILED ] " ;
            }
            
            $this->msg .= str_replace("\n", " ", $msg) ;
            
            if($this->dbLogLevel == LOG_WEB) { $this->msg .= '</span><br />' ; }
            $this->msg .= "\n" ;
        }
    }
    
    function print_debug() {
        switch($this->dbLogLevel) {
            case(LOG_WEB):
                if(!defined('IS_AJAX')) {
                    echo $this->msg ; 
                }
            break;
            case(LOG_COMMENT):  echo '<!-- ' . $this->msg . ' -->' ;
            break;
        }
    }
    
    /**
     * Establish a connection to the MySQL database.
     *
     * @param string server ip or name
     * @param string database user
     * @param string database password
     * @param string datatabase name
     */
    function osc_dbConnect() {
    	$this->db = @new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
        if ($this->db->connect_error) {
            $this->debug('Error connecting to \'' . $this->dbName . '\' (' . $this->db->connect_errno . ': ' . $this->db->connect_error . ')', false) ;
        }
        
        $this->debug('Connected to \'' . $this->dbName . '\': [DBHOST] = ' . $this->dbHost . ' | [DBUSER] = ' . $this->dbUser . ' | [DBPWD] = ' . $this->dbPassword) ;
    	$this->db->set_charset('UTF8');
    }
    
    /**
     * Close the database connection.
     */
    function osc_dbClose() {
        if (!$this->db->close()) {
            $this->debug('Error releasing the connection to \'' . $this->dbName . '\'', false) ;
        }
        
        $this->debug('Connection with \'' . $this->dbName . '\' released properly') ;
        $this->print_debug() ;
    }
    
    /**
     * Executes a SQL statement in the database.
     */
    function osc_dbExec() 
    {
    	$sql = null;
    	$argv = func_get_args();
    	switch(func_num_args()) {
    		case 0: return; break;
    		case 1: $sql = $argv[0]; break;
    		default:
    			$format = array_shift($argv);
    			foreach($argv as &$arg)
    				$arg = $this->db->real_escape_string($arg);
    			unset($arg);
    
    			$sql = vsprintf($format, $argv);
    			break;
    	}
    	
    	$result = $this->db->query($sql);
    	if(!$result) {
    	    $this->debug($sql . ' | ' . $this->db->error . ' (' . $this->db->errno . ')', false) ;
    	} else {
    	    $this->debug($sql) ;
    	}
    
    	return $result;
    }
    
    function osc_dbFetchValue() {
    	$result = null;
    
    	$sql = null;
    	$argv = func_get_args();
    	switch(func_num_args()) {
    	    case 0: return $results; break;
    		case 1: $sql = $argv[0]; break;
    		default:
    			$format = array_shift($argv);
    			$sql = vsprintf($format, $argv);
    			break;
    	}
    	
    	if($qry = $this->db->query($sql)) {
    	    $this->debug($sql) ;
    		$row = $qry->fetch_array();
    		$result = $row[0];
    		$qry->free();
    	} else {
    	    $this->debug($sql . ' | ' . $this->db->error . ' (' . $this->db->errno . ')', false) ;
    	}
    	
    	return $result;
    }
    
    /**
     * @return array with values resulting of execution of query passed by parameter.
     */
    function osc_dbFetchValues() {
    	$results = array();
    
    	$sql = null;
    	$argv = func_get_args();
    	switch(func_num_args()) {
    		case 0: return $results; break;
    		case 1: $sql = $argv[0]; break;
    		default:
    			$format = array_shift($argv);
    			$sql = vsprintf($format, $argv);
    			break;
    	}
    	
    	if($qry = $this->db->query($sql)) {
    	    $this->debug($sql) ;
    		while($result = $qry->fetch_array())
    			$results[] = $result[0];
    		$qry->free();
    	} else {
    	    $this->debug($sql . ' | ' . $this->db->error . ' (' . $this->db->errno . ')', false) ;
    	}
    	return $results;
    }
    
    function osc_dbFetchResult() {
    	$result = null;
    
    	$sql = null;
    	$argv = func_get_args();
    	switch(func_num_args()) {
    		case 0: return $results; break;
    		case 1: $sql = $argv[0]; break;
    		default:
    			$format = array_shift($argv);
    			$sql = vsprintf($format, $argv);
    			break;
    	}
    	
    	$qry = $this->db->query($sql);
    	if($qry) {
    	    $this->debug($sql) ;
    		$result = $qry->fetch_assoc();
    		$qry->free();
    	} else {
    	    $this->debug($sql . ' | ' . $this->db->error . ' (' . $this->db->errno . ')', false) ;
    	}
    	
    	return $result;
    }
    
    function osc_dbFetchResults() {
    	$results = array();
    
    	$sql = null;
    	$argv = func_get_args();
    	switch(func_num_args()) {
    		case 0: return $results; break;
    		case 1: $sql = $argv[0]; break;
    		default:
    			$format = array_shift($argv);
    			$sql = vsprintf($format, $argv);
    			break;
    	}
    
    	if($qry = $this->db->query($sql)) {
    	    $this->debug($sql) ;
    		while($result = $qry->fetch_assoc())
    			$results[] = $result;
    		$qry->free();
    	} else {
    	    $this->debug($sql . ' | ' . $this->db->error . ' (' . $this->db->errno . ')', false) ;
    	}
    	
    	return $results;
    }
    
    /**
     * Import (executes) the SQL passed as parameter making some proper adaptations.
     */
    function osc_dbImportSQL($sql, $needle = '')
    {
    	$sql = str_replace('/*TABLE_PREFIX*/', DB_TABLE_PREFIX, $sql);
    	$sentences = explode( $needle . ';', $sql);
    	foreach($sentences as $s) {
            $s = trim($s);
            if( !empty($s) ) {
                $s = trim($s) . $needle;
                if( $this->db->query($s) ) {
                    $this->debug($s) ;
                } else {
                    $this->debug($s . ' | ' . $this->db->error . ' (' . $this->db->errno . ')', false) ;
                }
            }
    	}
    }
    
    function autocommit($b_value) {
        $this->db->autocommit($b_value) ;
    }
    
    function commit() {
        $this->db->commit() ;
    }
    
    function rollback() {
        $this->db->rollback() ;
    }
    
    function get_last_id() {
        return($this->db->insert_id) ;
    }
    
    function get_affected_rows() {
        return($this->db->affected_rows) ;
    }
    
    
    /**
     * Given some queries, it will check against the installed database if the information is the same
     *
     * @param mixed array or string with the SQL queries.
     * @return BOOLEAN true on success, false on fail
     */
    function osc_updateDB($queries = '') {
    
        if(!is_array($queries)) {
            $queries = explode(";", $queries);
        }

        // Prepare and separate the queries
        $struct_queries = array();
        $data_queries = array();    
        foreach($queries as $query) {
            if(preg_match('|CREATE DATABASE ([^ ]*)|', $query, $match)) {
                array_unshift($struct_queries, $query);
            } else if(preg_match('|CREATE TABLE ([^ ]*)|', $query, $match)) {
                $struct_queries[trim(strtolower($match[1]), '`')] = $query;
            } else if(preg_match('|INSERT INTO ([^ ]*)|', $query, $match)) {
                $data_queries[] = $query;
            } else if(preg_match('|UPDATE ([^ ]*)|', $query, $match)) {
                $data_queries[] = $query;
            }
        }

        // Get tables from DB (already installed)
        $tables = $this->osc_dbFetchResults('SHOW TABLES');
        foreach($tables as $v) {
            $table = current($v);
            if(array_key_exists(strtolower($table), $struct_queries)) {
                
                // Get the fields from the query
                if(preg_match('|\((.*)\)|ms', $struct_queries[strtolower($table)], $match)) {
                    $fields = explode("\n", trim($match[1]));
                    
                    // Detect if it's a "normal field definition" or a index one
                    $normal_fields = $indexes = array();
                    foreach($fields as $field) {
                        if(preg_match('|([^ ]+)|', trim($field), $field_name)) {
                            switch (strtolower($field_name[1])) {
                                case '':
                                case 'on':
                                case 'foreign':
                                case 'primary':
                                case 'index':
                                case 'fulltext':
                                case 'unique':
                                case 'key':
                                    $indexes[] = trim($field, ", \n");
                                    break;
                                default :
                                    
                                    $normal_fields[strtolower($field_name[1])] = trim($field, ", \n");
                                    break;
                            }
                        }
                    }
                    
                    // Take fields from the DB (already installed)
                    $tbl_fields = $this->osc_dbFetchResults('DESCRIBE '.$table);
                    foreach($tbl_fields as $tbl_field) {
                        //Every field should we on the definition, so else SHOULD never happen, unless a very aggressive plugin modify our tables
                        if(array_key_exists(strtolower($tbl_field['Field']), $normal_fields)) {
                            // Take the type of the field
                            if(preg_match("|".$tbl_field['Field']." (ENUM\s*\(([^\)]*)\))|i", $normal_fields[strtolower($tbl_field['Field'])], $match) || preg_match("|".$tbl_field['Field']." ([^ ]*( unsigned)?)|i", $normal_fields[strtolower($tbl_field['Field'])], $match)) {
						        $field_type = $match[1];
						        // Are they the same?
						        if(strtolower($field_type)!=strtolower($tbl_field['Type']) && str_replace(' ', '', strtolower($field_type))!=str_replace(' ', '', strtolower($tbl_field['Type']))) {
						            $struct_queries[] = "ALTER TABLE ".$table." CHANGE COLUMN ".$tbl_field['Field']." ".$normal_fields[strtolower($tbl_field['Field'])];
						        }
						    }
						    // Have we changed the default value?
						    if(preg_match("| DEFAULT '(.*)'|i", $normal_fields[strtolower($tbl_field['Field'])], $default_match)) {
			        			$struct_queries[] = "ALTER TABLE ".$table." ALTER COLUMN ".$tbl_field['Field']." SET DEFAULT ".$default_match[1];
						    }
						    // Remove it from the list, so it will not be added
						    unset($normal_fields[strtolower($tbl_field['Field'])]);
                        }
                    }
                    // For the rest of normal fields (they are not in the table) we add them.
                    foreach($normal_fields as $k => $v) {
                        $struct_queries[] = "ALTER TABLE ".$table." ADD COLUMN ".$v;
                    }

                    // Go for the index part
                    $tbl_indexes = $this->osc_dbFetchResults("SHOW INDEX FROM ".$table);
                    if($tbl_indexes) {
                        unset($indexes_array);
                        foreach($tbl_indexes as $tbl_index) {
                            $indexes_array[$tbl_index['Key_name']]['columns'][] = array('fieldname' => $tbl_index['Column_name'], 'subpart' => $tbl_index['Sub_part']);
						    $indexes_array[$tbl_index['Key_name']]['unique'] = ($tbl_index['Non_unique'] == 0)?true:false;
                        }
                        foreach($indexes_array as $k => $v) {
                            $string = '';
						    if ($k=='PRIMARY') {
							    $string .= 'PRIMARY KEY ';
						    } else if($v['unique']) {
							    $string .= 'UNIQUE KEY ';
						    } else {
    						    $string .= 'INDEX ';
                            }

						    $columns = '';
						    // For each column in the index
						    foreach ($v['columns'] as $column) {
							    if ($columns != '') $columns .= ', ';
							    // Add the field to the column list string
							    $columns .= $column['fieldname'];
							    if ($column['subpart'] != '') {
								    $columns .= '('.$column['subpart'].')';
							    }
						    }
						    // Add the column list to the index create string
						    $string .= '('.$columns.')';
						    $var_index = array_search($string, $indexes);
                            if (!($var_index===false)) {
                                unset($indexes[$var_index]);
						    } else {
    						    $var_index = array_search(str_replace(', ', ',', $string), $indexes);
                                if (!($var_index===false)) {
                                    unset($indexes[$var_index]);
                                }
						    }
                        }
                    }
                    // For the rest of the indexes (they are in the new definition but not in the table installed
                    foreach($indexes as $index) {
                        if(strtolower(substr(trim($index),0,2))!='on') {// && strtolower(substr(trim($index),0,7))!='foreign') {
                            $struct_queries[] = "ALTER TABLE ".$table." ADD ".$index;
                        //} else {
                            //$struct_queries[] = "ALTER TABLE ".$table." ".$index;
                        }
				    }
				    // No need to create the table, so we delete it SQL
				    unset($struct_queries[strtolower($table)]);
				}
            }
        }

        $queries = array_merge($struct_queries, $data_queries);
        foreach($queries as $query) {
            $this->osc_dbExec($query);
        }

        return $queries;
    }
    
}

function getConnection($dbHost = null, $dbUser = null, $dbPassword = null, $dbName = null, $dbLogLevel = null) 
{
    static $instance ;
    
    //DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DEBUG_LEVEL
    if(defined('DB_HOST') && $dbHost == null)                 $dbHost = DB_HOST ;
    if(defined('DB_USER') && $dbUser == null)                 $dbUser = DB_USER ;
    if(defined('DB_PASSWORD') && $dbPassword == null)         $dbPassword = DB_PASSWORD ;
    if(defined('DB_NAME') && $dbName == null)                 $dbName = DB_NAME ;
    if(defined('DEBUG_LEVEL') && $dbLogLevel == null)         $dbLogLevel = DEBUG_LEVEL ;
    
    if(!isset($instance[$dbName . "_" . $dbHost])) {
        if(!isset($instance)) {
            $instance = array();
        }
        
        $instance[$dbName . "_" . $dbHost] = new DB($dbHost, $dbUser, $dbPassword, $dbName, $dbLogLevel);
    }

    return ($instance[$dbName . "_" . $dbHost]);
}

