<?php
	// This class is used for logging user actions to the database in the logs table
	class Log {
		
		private $db = null; // Used to store an instance of the database
		
		// Constructor
		public function __construct($action = null) {
			// Obtain an instance of the database
			$this->db = DB::get_instance();
			
			// If action was sent then process a new action to be added to the database
			if($action) {
				// $action has been sent, add to the database
				$this->action($action);
			}
		}
		
		// Find all logs from the database
		public function find_all() {
			// Return all 
			return $this->all = $this->db->query('SELECT * FROM logs', PDO::FETCH_ASSOC);
		}
		
		// Method to add a new entry to the logs table in the database
		public function action($action = null) {
			global $user;
			
			// Define the SQL to be used to make changes to the database
			$sql = '
				INSERT INTO logs ( 
					datetime, 
					action, 
					url, 
					user, 
					ip, 
					user_agent 
				) VALUES ( 
					:datetime, 
					:action, 
					:url, 
					:user, 
					:ip, 
					:user_agent 
				)
			';
			
			// Begin a prepared statement using the previous $sql
			$stmt = $this->db->prepare($sql);
			
			// Bind values to the prepared statement
			$datetime = $this->current_mysql_datetime();
			$stmt->bindParam(':datetime', $datetime);
			$action = $this->get_action($action);
			$stmt->bindParam(':action', $action);
			$stmt->bindParam(':url', $_SERVER['REQUEST_URI']);
			$name = $user->username ? $user->name . ' [' . $user->username . ']' : 'Unknown';
			$stmt->bindParam(':user', $name);
			$stmt->bindParam(':ip', $_SERVER['REMOTE_ADDR']);
			$stmt->bindParam(':user_agent', $_SERVER['HTTP_USER_AGENT']);
			
			// Execute the prepared statement
			$result = $stmt->execute();
			
			// Check if successful
			if($result) {
				// Insert successful
				return true;
			} else {
				// Insert failed
				return false;
			}
		}
		
		// Returns a string of an action, based on an input
		private function get_action($action = null) {
			// Run through a switch statement to specify an action and return
			switch($action) {
				case 'view' : // For page views
					$action = 'Page Viewed: (' . page_name() . ')'; // Use the page_name function to specify which page a user has visited
					break;
				default :
					$action = 'Action Unspecified!';
					break;
			}
			
			// Return the $action
			return $action;
		}
		
		// Method to obtain the current datetime in MySQL format
		private function current_mysql_datetime() {
			// Return the current time in MySQL datetime formate 
			return date('Y-m-d H:i:s', time());
		}
		
	}; // Close class Log
// EOF