<?php
    $path = "./";
    require_once($path."functions.php");
    require_once($path."smarty/Smarty.class.php");
    require_once($path."db/SQLiteManager.php");

    $db = SQLiteManager\SQLiteManager::getInstance();

    $mode = $_REQUEST["mode"];
    unset($_REQUEST["mode"]);
    unset($_REQUEST["submit"]);

    if($mode == "addFeed") {
        require_once($path."autoloader.php");
        $feed = new SimplePie();
        $feed->enable_cache(false);
        $feed->set_feed_url($_REQUEST["feed"]);
        $feed->init();
        if($feed->error()) {
            throw new InvalidArgumentException($feed->error());
        }
        $db->insert("feeds", ["feed"=>$_REQUEST["feed"]]);
    }

    if($mode == "addAggregateFeed") {
        $urls = explode("\n", $_REQUEST["feeds"]);

        foreach($urls as $url) {
            require_once($path."autoloader.php");
            $feed = new SimplePie();
            $feed->enable_cache(false);
            $feed->set_feed_url($url);
            $feed->init();
            if($feed->error()) {
                throw new InvalidArgumentException($feed->error());
            }
        }
        $db->insert("aggregateFeeds", ["feeds"=>$_REQUEST["feeds"]]);
    }

    if($mode == "updateAggregateFeed") {
        $urls = explode("\n", $_REQUEST["feeds"]);

        foreach($urls as $url) {
            require_once($path."autoloader.php");
            $feed = new SimplePie();
            $feed->enable_cache(false);
            $feed->set_feed_url($url);
            $feed->init();
            if($feed->error()) {
                throw new InvalidArgumentException($feed->error());
            }
        }
        $id = $_REQUEST["id"];
        $db->update("aggregateFeeds", ["feeds"=>$_REQUEST["feeds"]], ["ID"=>$id]);
    }

    if($mode == "addRegex") {
        $regex = "/".$_REQUEST["regex"]."/s";
        if($_REQUEST["caseInsensitive"]) {
            $regex .= "i";
        }
        if(preg_match($regex, "") === false) {
            throw new InvalidArgumentException($regex."<br>".preg_last_error());
        }

        $db->insert("filters", ["feedID"=>$_REQUEST["feedID"], "field"=>$_REQUEST["field"], "regex"=>$regex]);
    }

    if($mode == "deleteRegex") {
        $db->delete("filters", ["ID"=>$_REQUEST["filterID"]]);
    }

    if($mode == "setMaxItems") {
        $db->update("feeds", ["maxItems"=>$_REQUEST["maxItems"]], ["ID"=>$_REQUEST["feedID"]]);
    }

    header("location:admin.php");
?>