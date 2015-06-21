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

	//-----------------------------
	//	   DEBUGGING FUNCTIONS
	//-----------------------------

	/**
     * Useful debug function that displays variables or arrays in a pretty format.
     *
     * @param STRING $name Name of the array (for pretty display purposes).
     * @param MIXED $array Array of data, but if it isn't an array we try to print it by itself.
     * @param STRING $member Calls a function on $array when outputing $array (assumes $array is an object or array of objects).
     * @return VOID
     */
	function dump($name, $array, $member=null) {
		if(is_array($array) || (is_object($array) && $array instanceof Iterator)) {
			foreach($array as $key=>$val) {
				if(is_array($val)) {
                    if($member == null)
    					dump($name."[$key]", $val, $member);
                    else
                        dump($name."[$key]", $val);
                } else {
                    if($member == null) {
    					print $name."[".$key."] = ".htmlentities($val)."<br/>\n";
                    } else {
                        print $name."[".$key."] = ".htmlentities($val->{$member}())."<br/>\n";
                    }
                }
			}
		} else {
            if($member == null) {
    			print "$name = ".htmlentities($array)."<br/>\n";
            } else {
                print "$name = ".htmlentities($array->{$member}())."<br/>\n";
            }
        }
	}

    //-----------------------------
	//			FUNCTIONS
	//-----------------------------

	function filter(SimplePie_Item $item, array $feedinfo) {
		$patterns = $feedinfo["patterns"];
		$anyOrAll = $feedinfo["anyOrAll"];
		
		$blockOrPermit = $feedinfo["blockOrPermit"];
		
		$result = true;
		if(($blockOrPermit == 'permit' && $anyOrAll == 'any') || ($blockOrPermit == 'block' && $anyOrAll == 'all')){
			$result = false;
		}
		
		foreach($patterns as $pattern) {
			switch($pattern["field"]) {
				case "title":
					if(preg_match($pattern["regex"], $item->get_title())) {
						if($anyOrAll == 'any'){
							if($blockOrPermit == 'permit'){
								return true;
							}else{//$blockOrPermit == 'block'
								return false;
							}
						}
					}else{
						if($anyOrAll == 'all'){
							if($blockOrPermit == 'permit'){
								return false;
							}else{//$blockOrPermit == 'block'
								return true;
							}
						}
					}
					break;
				case "summary":
					if(preg_match($pattern["regex"], $item->get_description())) {
						if($anyOrAll == 'any'){
							if($blockOrPermit == 'permit'){
								return true;
							}else{//$blockOrPermit == 'block'
								return false;
							}
						}
					}else{
						if($anyOrAll == 'all'){
							if($blockOrPermit == 'permit'){
								return false;
							}else{//$blockOrPermit == 'block'
								return true;
							}
						}
					}
					break;
				case "content":
					if(preg_match($pattern["regex"], $item->get_content())) {
						if($anyOrAll == 'any'){
							if($blockOrPermit == 'permit'){
								return true;
							}else{//$blockOrPermit == 'block'
								return false;
							}
						}
					}else{
						if($anyOrAll == 'all'){
							if($blockOrPermit == 'permit'){
								return false;
							}else{//$blockOrPermit == 'block'
								return true;
							}
						}
					}
					break;
				case "url":
					if(preg_match($pattern["regex"], $item->get_permalink())) {
						if($anyOrAll == 'any'){
							if($blockOrPermit == 'permit'){
								return true;
							}else{//$blockOrPermit == 'block'
								return false;
							}
						}
					}else{
						if($anyOrAll == 'all'){
							if($blockOrPermit == 'permit'){
								return false;
							}else{//$blockOrPermit == 'block'
								return true;
							}
						}
					}
					break;
				case "category":
					foreach($item->get_categories() as $category) {
						if(preg_match($pattern["regex"], $category->get_term()) || preg_match($pattern["regex"], $category->get_label())){ 
							if($anyOrAll == 'any'){
								if($blockOrPermit == 'permit'){
									return true;
								}else{//$blockOrPermit == 'block'
									return false;
								}
							}
						}else{
							if($anyOrAll == 'all'){
								if($blockOrPermit == 'permit'){
									return false;
								}else{//$blockOrPermit == 'block'
									return true;
								}
							}
						}
					}
					break;
				default:
					die("Filter not implemented for field ".$pattern["field"]);
			}
		}
		return $result;
	}
?>