<?php

if (!extension_loaded('mysqli')) {
    die('MySQLi extension is required. Enable mysqli in PHP.');
}

if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', 1);
}
if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', 2);
}
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', 3);
}

if (!function_exists('mysql_connect')) {
    function mysql_compat_get_link($link_identifier = null) {
        global $__mysql_compat_connection;

        if ($link_identifier instanceof mysqli) {
            return $link_identifier;
        }

        if (isset($__mysql_compat_connection) && $__mysql_compat_connection instanceof mysqli) {
            return $__mysql_compat_connection;
        }

        return null;
    }

    function mysql_connect($server = null, $username = null, $password = null) {
        global $__mysql_compat_connection;

        mysqli_report(MYSQLI_REPORT_OFF);
        $link = @mysqli_connect($server, $username, $password);
        if ($link instanceof mysqli) {
            $__mysql_compat_connection = $link;
            return $link;
        }

        return false;
    }

    function mysql_select_db($database_name, $link_identifier = null) {
        $link = mysql_compat_get_link($link_identifier);
        if (!$link) {
            return false;
        }

        return @mysqli_select_db($link, $database_name);
    }

    function mysql_query($query, $link_identifier = null) {
        $link = mysql_compat_get_link($link_identifier);
        if (!$link) {
            return false;
        }

        return @mysqli_query($link, $query);
    }

    function mysql_error($link_identifier = null) {
        $link = mysql_compat_get_link($link_identifier);
        if (!$link) {
            return 'No MySQL connection available.';
        }

        return (string) @mysqli_error($link);
    }

    function mysql_real_escape_string($unescaped_string, $link_identifier = null) {
        $link = mysql_compat_get_link($link_identifier);
        if (!$link) {
            return addslashes((string) $unescaped_string);
        }

        return @mysqli_real_escape_string($link, (string) $unescaped_string);
    }

    function mysql_fetch_assoc($result) {
        return @mysqli_fetch_assoc($result);
    }

    function mysql_fetch_array($result, $result_type = MYSQL_BOTH) {
        $mysqliType = MYSQLI_BOTH;
        if ((int) $result_type === MYSQL_ASSOC) {
            $mysqliType = MYSQLI_ASSOC;
        } elseif ((int) $result_type === MYSQL_NUM) {
            $mysqliType = MYSQLI_NUM;
        }

        return @mysqli_fetch_array($result, $mysqliType);
    }

    function mysql_num_rows($result) {
        return (int) @mysqli_num_rows($result);
    }

    function mysql_close($link_identifier = null) {
        $link = mysql_compat_get_link($link_identifier);
        if (!$link) {
            return false;
        }

        return @mysqli_close($link);
    }
}

class db {
	
	private $Server, $DB;
	
	function __construct($host, $user, $pass, $database) {
	
		$this->Server = mysql_connect($host, $user, $pass);
		$this->DB = mysql_select_db($database);
		
		if(!$this->Server) {
			
			echo "<div style=\"font-family: tahoma; font-size: 11px; width: 400px; margin: auto; background-color:#F5A9A9; padding: 10px; border-radius: 3px; border: 1px solid #FA5858;\">";
			echo "<big><center><strong>MySQL Error:</strong></big><br />";
			echo "Cannot connect to MySQL Server @ {$host}";
			echo "</center></div>";
			
			die();
				
			
		} 
		
		if(!$this->DB) {
			
			echo "<div style=\"font-family: tahoma; font-size: 11px; width: 400px; margin: auto; background-color:#F5A9A9; padding: 10px; border-radius: 3px; border: 1px solid #FA5858;\">";
			echo "<big><center><strong>MySQL Error:</strong></big><br />";
			echo "Cannot connect to Database : {$database} @ {$host}";
			echo "</center></div>";
				
			die();
			
		}
		
	} // END __construct
	
	function Query($string) {
		
		$q = mysql_query($string);
		
		if(!$q) {
			
			echo "<div style=\"font-family: tahoma; font-size: 11px; width: 400px; margin: auto; background-color:#F5A9A9; padding: 10px; border-radius: 3px; border: 1px solid #FA5858;\">";
			echo "<big><center><strong>MySQL Error:</strong></big><br />";
			echo mysql_error();
			echo "</center></div>";
			
			die();
				
		}
		
		return $q;
		
	} // END Query
	
	function Clean($string) {
		
		return mysql_real_escape_string($string);
		
	}
	
	
}

?>
