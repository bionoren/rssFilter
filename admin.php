<?php
    $path = "./";
    require_once($path."functions.php");
    require_once($path."smarty/Smarty.class.php");
    require_once($path."db/SQLiteManager.php");

    $fields = ["title", "summary", "category", "contributor", "author", "content", "url"];

    $db = SQLiteManager\SQLiteManager::getInstance();

    $result = $db->select("feeds");
    $feeds = $db->fetchArray($result);

    foreach($feeds as &$feed) {
        $result = $db->select("filters", null, ["feedID"=>$feed["ID"]]);
        $feed["patterns"] = $db->fetchArray($result);
    }

    //DISPLAY
    $smarty = new Smarty();
    $smarty->auto_literal = true;
    $data = new Smarty_Data();
    $data->assign("fields", $fields);
    $data->assign("feeds", $feeds);
    $smarty->display("admin.tpl", $data);
?>