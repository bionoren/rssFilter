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

	$path = "../";
    require_once("SQLiteManager.php");

    $db = SQLiteManager::getInstance();

	$fields = [];
	$fields[] = new DBField("feed", DBField::STRING);
	$fields[] = new DBField("maxItems", DBField::NUM, 0);
	$db->createTable("feeds", $fields);

	$fields = [];
	$fields[] = new DBField("feedID", DBField::NUM, -1, "feeds", "ID");
	$fields[] = new DBField("field", DBField::STRING);
	$fields[] = new DBField("regex", DBField::STRING);
	$db->createTable("filters", $fields);

	//errorLog
	$fields = [];
	$fields[] = new DBField("query", DBField::STRING);
	$fields[] = new DBField("error", DBField::STRING);
	$fields[] = new DBField("date", DBField::NUM);
	$db->createTable("errorLog", $fields);
?>