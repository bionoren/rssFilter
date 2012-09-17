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

    /**
     * Manages creating and modifying a SQLite table field.
     *
     * @author Bion Oren
     *
     * @property-read STRING $name The name of this field.
     * @property-read BOOLEAN $indexed True if this field should be indexed.
     * @property-read BOOLEAN $unique True if this field is unique.
     */
    class DBField {
        /** STRING Constant for a text data type. */
        const STRING = "TEXT";
        /** STRING Constant for an integer data type. */
        const NUM = "INTEGER";

        /** STRING The name of this field. */
        protected $name;
        /** STRING One of the field type constants in this class. */
        protected $type;
        /** MIXED The default value for this field. */
        protected $default;
        /** STRING If this field is a foreign key, the name of the table it is linked back to. */
        protected $keyTable;
        /** STRING If this field is a foreign key, the name of the field in $keyTable it is linked to. */
        protected $keyField;
        /** BOOLEAN True if this field is unique. */
        protected $unique;
        /** BOOLEAN True if this field should be indexed. */
        protected $indexed;
        /** BOOLEAN True if this is the primary key. */
        public $primary;

        /**
         * Creates a new field manager.
         *
         * @param STRING $name The name of this database field.
         * @param INTEGER $type One of the class' type constants.
         * @param MIXED $default Default value for the field.
         * @param STRING $keyTable Optional name of the table this field references as a foreign key.
         * @param STRING $keyField Optional name of the field this field references as a foreign key.
         */
        public function __construct($name, $type, $default=null, $keyTable=null, $keyField=null) {
            $this->name = $name;
            $this->type = $type;
            $this->default = $default;
            $this->keyTable = $keyTable;
            $this->keyField = $keyField;
            $this->unique = false;
            $this->indexed = false;
            if($keyTable != null) {
                $this->setIndexed();
            }
        }

        /**
         * Creates the SQL necessary to create this field.
         *
         * @return STRING SQL statement.
         */
        public function createInTable() {
            $ret = $this->name." ".$this->type;
            if($this->default != null) {
                $ret .= " DEFAULT '".$this->default."'";
            }
            if($this->keyTable != null) {
                $ret .= " REFERENCES ".$this->keyTable;
                if($this->keyField != null) {
                    $ret .= "(".$this->keyField.")";
                }
                $ret .= " ON UPDATE CASCADE ON DELETE CASCADE";
            }
            if($this->primary) {
                $ret .= " PRIMARY KEY";
            }
            return $ret;
        }

        /**
         * Marks this field to be indexed if it is not already unique.
         *
         * @return VOID
         * @see setUnique
         */
        public function setIndexed() {
            if(!$this->unique) {
                $this->indexed = true;
            }
        }

        /**
         * Marks this field to be unique and not indexed (since unique is an index).
         *
         * @return VOID
         */
        public function setUnique() {
            $this->unique = true;
            $this->indexed = false;
        }

		/**
         * Fake read only properties
         *
         * @param STRING $name Name of the property to get.
         * @return MIXED Value of the property $name (if it existed).
         */
        public function __get($name) {
            if(property_exists($this, $name)) {
                return $this->$name;
            } elseif(property_exists($this, $name."_")) {
				return $this->{$name."_"};
			} else {
				$trace = debug_backtrace();
				trigger_error('Undefined property via __get(): '.$name.' on '.get_class($this).' in '.$trace[0]['file'].' on line '.$trace[0]['line'], E_USER_NOTICE);
				debug_print_backtrace();
				return null;
			}
        }

        /**
         * Fake write only properties
         *
         * @param STRING $name Name of the property to get.
         * @param MIXED $value Value of the property $name (if it existed).
         */
        public function __set($name, $value) {
            $name .= "_";
            if(property_exists($this, $name)) {
                $this->$name = $value;
            } else {
				$trace = debug_backtrace();
				trigger_error('Undefined property via __set(): '.substr($name, 0, -1).' on '.get_class($this).' in '.$trace[0]['file'].' on line '.$trace[0]['line'], E_USER_NOTICE);
				debug_print_backtrace();
			}
        }

		/**
         * Returns a string that's somewhat useful for debugging purposes.
         *
         * @return STRING Debug string.
         */
        public function __toString() {
            return "Instance of ".get_class($this);
        }
    }
?>