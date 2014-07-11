<?php

 	/* 
   ** Package class_DB
 	** Used to override mysqli by adding extra commands to some predefined functions
 	** Use separate dbconfig.php file to define mySQL parameters
 	** Author: Alvin James Bellero
 	*/

 	define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

	class class_DB extends mysqli {

		private $mysqli = null;

		/* Class initialization */
		public function __construct() {
			require_once('dbconfig.php');
			$this->mysqli = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

			if($this->mysqli->connect_errno) {
				echo 'Error MySQLi: ('. $this->mysqli->connect_errno. ') ' . $this->mysqli->connect_error;
				exit;
			}	$this->mysqli->set_charset('utf8');
		}

		/* Query override */
		public function query($qry, $log_execution_time=false) {
			$t      = ($log_execution_time ? microtime(1) : '');
			$qry    = preg_replace('/\s+/', ' ', $qry);
			$result = $this->mysqli->query($qry) or exit('Error on line: '.__LINE__.' - '.$this->mysqli->error);

			# Log Query execution in a text file
			if($log_execution_time) {
				$fp   = fopen(DOCROOT.'/log.txt', 'a+');
				$time = number_format(abs($t-microtime(1)), 4);
				fwrite($fp, '['.date('M-d-Y H:i:s').'] '.$time.': '.$qry."\n");
			}  return ($log_execution_time ? '<em title="'.$qry.'">Query Exec. Time: ('.$time.' sec.)</em>' : $result);
		}

		/* Escape the string to get ready to insert or update */
		public function clean($text) {
			$text = trim($text);
			return $this->mysqli->real_escape_string($text);
		}

		/* Get the last insert ID */
		public function lastInsertID() {
			return $this->mysqli->insert_id;
		}

		/* Gets the total count and returns integer */
		public function totalCount($fieldname, $tablename, $where='', $log_execution_time=false) {
			$qry    = "SELECT COUNT(".($fieldname == '' || $fieldname == null ? 1 : $fieldname).") `count` FROM {$tablename} {$where}";
			$result = $this->query($qry, $log_execution_time);

			if($result) {
				$row = $result->fetch_assoc();
				return $row['count'];
			}
		}

	}
 
?>