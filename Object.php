<?php
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
     * Base class for all objects.
     *
     * Implements hacked properties. By default, all protected variables are read only properties.
     * Any variable name postfixed with '_' will be a write only property.
     * Remember to document these properties with @property-read or @property-write for code completion!
     *
     * @author Bion Oren
     */
    abstract class Object {
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