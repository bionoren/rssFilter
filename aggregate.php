<?php
    $path = "./";
    require_once($path."autoloader.php");
    require_once($path."functions.php");
    require_once($path."db/SQLiteManager.php");

    $id = $_REQUEST["id"];
    if(!$id) {
        die("no id");
    }
    $db = SQLiteManager\SQLiteManager::getInstance();

    $result = $db->select("aggregateFeeds", null, ["ID"=>$id]);
    $feedInfo = $db->fetchArray($result)[0];
    if(!$feedInfo) {
        die("bad id ".$id);
    }
    $threshold = $_REQUEST["threshold"];
    if(!$threshold) {
        die("no threshold (0 - 100)");
    }
    $minThreshold = $_REQUEST["minThreshold"];
    if(!isset($_REQUEST["minThreshold"])) {
        $minThreshold = true;
    }

    $urls = explode("\n", $feedInfo["feeds"]);
    $articles = [];
    $feeds = [];
    $i = 0;
    foreach($urls as $url) {
        $feed = new SimplePie();
        $feed->enable_cache(false);
        $feed->set_feed_url($url);
        $feed->init();

        foreach($feed->get_items() as $item) {
            $title = $item->get_title();
            $summary = $item->get_description();

            $articles[$i][$title] = $item;
        }

        $i++;
        $feeds[] = $feed;
    }

    $output = [];
    foreach($articles as $src1=>$feedArticles) {
        foreach($feedArticles as $title=>$article) {
            foreach($articles as $src2=>$feedArticles2) {
                if($src1 == $src2) {
                    continue;
                }
                foreach($feedArticles2 as $title2=>$article2) {
                    $similarity;
                    similar_text($title, $title2, $similarity);
                    if(($minThreshold && $similarity >= $threshold) || (!$minThreshold && $similarity < $threshold)) {
                        $output[] = [$similarity, $article];
                    }
                }
            }
        }
    }
?>

<?xml version="1.0" encoding="utf-8"?>

<feed xmlns="http://www.w3.org/2005/Atom">
    <title><?= $feeds ?></title>
    <link href="http://localhost/~bion/rss/aggregate.php" rel="self"/>
    <updated><?= date("c"); ?></updated> <?php /* WARNING: simplepie doesn't provide ANY feed-level date */ ?>
    <?php $i = 0; ?>
    <?php foreach($output as $temp) {
        $item = $temp[1];
        /*if(!filter($item, $feedInfo["patterns"])) {
            continue;
        }*/
        ?>
        <entry>
            <title><?= $item->get_title()." - ".round($temp[0], 1)."%"; ?></title>
            <link href="<?= $item->get_permalink(); ?>"/>
            <id><?= $item->get_id(); ?></id>
            <?php if($item->get_date()) { ?>
                <published><?= $item->get_date("c"); ?></published>
            <?php } ?>
            <?php if($item->get_updated_date()) { ?>
                <updated><?= $item->get_updated_date("c"); ?></updated>
            <?php } ?>
            <summary type="html"><![CDATA[<?= $item->get_description(); ?>]]></summary>
            <?php if($item->get_content(true)) { ?>
                <content type="html"><![CDATA[<?= $item->get_content(true); ?>]]></content>
            <?php } ?>
            <?php if($item->get_authors()) { ?>
                <?php foreach($item->get_authors() as $author) { ?>
                    <author>
                        <?php if($author->get_name()) { ?>
                            <name><?= $author->get_name(); ?></name>
                        <?php } ?>
                        <?php if($author->get_email()) { ?>
                            <email><?= $author->get_email(); ?></email>
                        <?php } ?>
                        <?php if($author->get_link()) { ?>
                            <uri><?= $author->get_link(); ?></uri>
                        <?php } ?>
                    </author>
                <?php } ?>
            <?php } ?>
            <?php if($item->get_contributors()) { ?>
                <?php foreach($item->get_contributors() as $acontributor) { ?>
                    <contributor>
                        <?php if($contributor->get_name()) { ?>
                            <name><?= $contributor->get_name(); ?></name>
                        <?php } ?>
                        <?php if($contributor->get_email()) { ?>
                            <email><?= $contributor->get_email(); ?></email>
                        <?php } ?>
                        <?php if($contributor->get_link()) { ?>
                            <uri><?= $contributor->get_link(); ?></uri>
                        <?php } ?>
                    </contributor>
                <?php } ?>
            <?php } ?>
            <?php if($item->get_categories()) { ?>
                <?php foreach($item->get_categories() as $category) { ?>
                    <category<?php if($category->get_scheme()) { ?> scheme="<?= $category->get_scheme(); ?>"<?php } ?><?php if($category->get_term()) { ?> term="<?= $category->get_term(); ?>"<?php } ?><?php if($category->get_label()) { ?> label="<?= $category->get_label(); ?>"<?php } ?>/>
                <?php } ?>
            <?php } ?>
            <?php if($item->get_copyright()) { ?>
                <rights><?= $item->get_copyright(); ?></rights>
            <?php } ?>
        </entry>
    <?php } ?>
</feed>