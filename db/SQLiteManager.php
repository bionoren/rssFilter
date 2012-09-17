<?php
	namespace SQLiteManager;

    /*
	 *	Copyright 2010 Bion Oren
	 *
	 *	Licensed under the Apache License, Version 2.0 (the "License");
	 *	you may not use this file except in compliance with the License.
	 *	You may obtain a copy of the License at
	 *		http://www.apache.org/licenses/LICENSE-2.0
	 *	Unless required by applicable law or agreed to in writing, software
	 *	distributed under the License is distributed on an "AS IS" BASIS,
	 *	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	 *	See the License for the specific language governing permissions and
	 *	limitations under the License.
	 */

	require_once("SQLiteManagerConfig.inc");
    require_once("DBField.php");

    /**
     * Manages interaction with a SQLite database.
     *
     * @author Bion Oren
     */
    class SQLiteManager {
        /** SQLiteManager Conntainer for the singleton instance. */
        protected static $instance = null;

        /** STRING Database journaling mode. */
        private $journal = "MEMORY";
        /** STRING Database SYNC mode. */
        private $sync = "OFF";

        /** RESOURCE Link to the database. */
        protected $db;

        /**
         * Constructs the manger.
         *
         * @param STRING $db Database name.
         */
        protected function __construct($db) {
            try {
                $this->db = new \SQLite3($db, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            } catch(Exception $e) {
                print "FATAL ERROR opening $db - is it writable? (or if it doesn't exist, is the directory writable?)<br>";
                die($e);
            }
            $this->query("PRAGMA synchronous = ".$this->sync);
            $this->query("PRAGMA journal_mode = ".$this->journal);
            $version = explode(".", phpversion());
            if($version[0] < 5) {
                die("Your current PHP version is: ".phpversion()." - GO UPGRADE PHP RIGHT NOW!!!");
            } elseif($version[1] >= 3 && $version[2] >= 1) {
                $this->query("PRAGMA foreign_keys = ON");
            }
        }

        /**
         * Returns true if the database changed in the last query.
         *
         * @return BOOLEAN True if the db changed.
         */
        public function changed() {
            return $this->db->changes() != 0;
        }

        /**
         * Closes the database connection.
         *
         * @return VOID
         */
        public function close() {
            $this->db->close();
			$this::$instance = null;
        }

        /**
         * Creates a new database table.
         *
         * @param STRING $name Table name.
         * @param ARRAY $fields Array of DBField objects.
         * @return BOOLEAN True if table creation succeeded.
         * @see DBField
         */
        public function createTable($name, array $fields) {
            if(empty($fields)) {
                print "Error: Cannot create an empty table<br>";
                return false;
            }
            if(empty($name)) {
                print "Error: Cannot create an unamed table<br>";
                return false;
            }
            //every table gets a primary key alias to keep foreign key constraints happy
            $keyField = new DBField("ID", DBField::NUM);
            $keyField->primary = true;
            array_unshift($fields, $keyField);
            //we're smacking all the tables, so force them to go away
            $this->query("PRAGMA foreign_keys = OFF");
            $this->query("DROP TABLE IF EXISTS ".$name);
            $this->query("PRAGMA foreign_keys = ON");
            $sql = "CREATE TABLE IF NOT EXISTS ".$name." (";
            foreach($fields as $field) {
                $sql .= $field->createInTable().",";
            }
            $sql = substr($sql, 0, -1).")";
            $this->query($sql);
            foreach($fields as $field) {
                if($field->unique) {
                    $sql = "CREATE UNIQUE INDEX IF NOT EXISTS ".$field->name." ON ".$name." (".$field->name.");";
                    $this->query($sql);
                }
                if($field->indexed) {
                    $sql = "CREATE INDEX IF NOT EXISTS ".$field->name." ON ".$name." (".$field->name.");";
                    $this->query($sql);
                }
            }
            return true;
        }

        /**
         * Creates a unique constraint in the given table on the given set of fields.
         *
         * @param STRING $name Table name.
         * @param ARRAY $fields Array of DBField objects.
         * @return BOOLEAN True if the query succeeded.
         */
        public function createUniqueConstraint($name, array $fields) {
            if(empty($name) || empty($fields)) {
                return;
            }

            $sql = "CREATE UNIQUE INDEX ".current($fields)->name.rand(1, 100)." ON ".$name." (";
            $tmp = "";
            foreach($fields as $field) {
                $tmp .= $field->name.",";
            }
            $sql .= substr($tmp, 0, -1).")";
            return $this->query($sql);
        }

		/**
		 * Fetches all the data from a result set.
		 *
		 * @param OBJECT $result SQLITE3 query result.
		 * @param INTEGER $mode SQLITE3 Data mode to use (ASSOC or NUM).
		 * @return ARRAY Array of data row arrays.
		 */
		public static function fetchArray($result, $mode=SQLITE3_ASSOC) {
			$ret = [];
			while($row = $result->fetchArray($mode)) {
				$ret[] = $row;
			}
			return $ret;
		}

        /**
         * Returns the singleton instance, creating it if necessary.
         *
         * @return SQLiteManager Singleton instance.
         */
        public static function getInstance() {
            if(SQLiteManager::$instance == null) {
                global $path;
                SQLiteManager::$instance = new SQLiteManager($path.DB_PATH.DB_NAME);
            }
            return SQLiteManager::$instance;
        }

        /**
         * Returns the last row insert ID.
         *
         * @return INTEGER Last row insert ID.
         */
        public function getLastInsertID() {
            return $this->db->lastInsertRowID();
        }

        /**
         * Constructs a where clause from a given set of fields ANDed together.
         *
         * @param ARRAY $whereFields Associative array mapping field names to their values.
         * @return STRING Where clause.
         */
        public static function getWhereClause(array $whereFields) {
            $sql = "";
            if(!empty($whereFields)) {
                $sql = " WHERE ";
                foreach($whereFields as $key=>$val) {
                    $sql .= $key."='".\SQLite3::escapeString($val)."' AND ";
                }
                $sql = substr($sql, 0, -5);
            }
            return $sql;
        }

        /**
         * Inserts a new table row.
         *
         * @param STRING $table Table name.
         * @param ARRAY $fields Associative array mapping field names to their values.
         * @param BOOLEAN $ignore If true, insert failure will be ignored.
         * @return BOOLEAN True if the query succeeded.
         */
        public function insert($table, array $fields, $ignore=false) {
            if(empty($fields) || empty($table)) {
                return;
            }

            $sql = "INSERT ";
            if($ignore) {
                $sql .= "OR IGNORE ";
            }
            $sql .= "INTO ".$table." ";
            $colStr = "";
            $valStr = "";
            foreach($fields as $key=>$val) {
                $colStr .= "'".$key."',";
                if((string)intval($val) == $val) {
                    $valStr .= $val.",";
                } elseif(is_null($val)) {
					$valStr .= "NULL,";
				} else {
                    $valStr .= "'".\SQLite3::escapeString($val)."',";
                }
            }
            $sql .= "(".substr($colStr, 0, -1).") VALUES (".substr($valStr, 0, -1).")";
            return $this->query($sql);
        }

        /**
         * Performs an arbitrary query on the database.
         *
         * @param STRING $sql SQL Statement.
         * @return MIXED SQLite3Result or true if the query was simply successful.
         * @throws InvalidArgumentException if an error occurs.
         */
        public function query($sql) {
            if(empty($sql)) {
                return false;
            }

            $ret = $this->db->query($sql);
            if($ret === false) {
                $fields["query"] = $sql;
                $fields["error"] = $this->db->lastErrorMsg();
                $fields["date"] = time();
                $this->insert("errorLog", $fields);
                $msg = "sql = $sql<br><span style='color:red'>".$this->db->lastErrorMsg()."</span><br>";
                throw new \InvalidArgumentException($msg);
            }
            return $ret;
        }

        /**
         * Runs a simple select statement.
         *
         * @param STRING $table Table name.
         * @param ARRAY $fields Array of field names to select.
         * @param ARRAY $whereFields Associative array mapping field names to their values for building the where clause.
         * @return SQLite3Result Result of the select statement.
         */
        public function select($table, array $fields=null, array $whereFields=[]) {
            if(empty($table)) {
                return;
            }

            $sql = "SELECT ";
            if(!empty($fields)) {
                foreach($fields as $val) {
                    $sql .= $val.",";
                }
                $sql = substr($sql, 0, -1);
            } else {
                $sql .= "*";
            }
            $sql .= " FROM ".$table.$this::getWhereClause($whereFields);
            return $this->query($sql);
        }

        /**
         * Runs a simple update statement.
         *
         * @param STRING $table Table name.
         * @param ARRAY $fields Array of field names to select.
         * @param ARRAY $whereFields Associative array mapping field names to their values for building the where clause.
         * @return BOOLEAN True if the update succeeded.
         */
        public function update($table, array $fields, array $whereFields=[]) {
            if(empty($table) || empty($fields)) {
                return;
            }

            $sql = "UPDATE ".$table." SET ";
            foreach($fields as $key=>$val) {
				if(is_null($val)) {
					$sql .= $key."=NULL,";
				} else {
	                $sql .= $key."='".$val."',";
				}
            }
            $sql = substr($sql, 0, -1).$this::getWhereClause($whereFields);
            return $this->query($sql);
        }

        /**
         * Releases database resources when the object is dealloced.
         *
         * @return VOID
         */
        function __destruct() {
            $this->close();
        }
    }
?>