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
    $grouping = (int)$_REQUEST["grouping"];
    if(!isset($_REQUEST["grouping"])) {
        $grouping = 1;
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
    $feedArticles = array_shift($articles);
    foreach($feedArticles as $title=>$article) {
        $title = strtolower($title); //similar_text takes case into account
        $avg = 0;
        $hits = 0;
        $maxes = [];
        foreach($articles as $feedArticles2) {
            $max = 0;
            foreach($feedArticles2 as $title2=>$article2) {
                $title2 = strtolower($title2);
                $similarity1;
                $similarity2;
                similar_text($title, $title2, $similarity1);
                similar_text($title2, $title, $similarity2);
                $max = max($max, $similarity1, $similarity2);
            }
            if(($minThreshold && $max >= $threshold) || (!$minThreshold && $max <= $threshold)) {
                $maxes[] = round($max, 1)."%";
                $hits++;
            }
            if($grouping > 0 && $grouping === $hits) {
                $output[] = [implode(", ", $maxes), $article];
                break;
            }
            $avg += $max;
        }
        if($grouping == 0) {
            $avg /= count($articles) - 1;
            if(($minThreshold && $avg >= $threshold) || (!$minThreshold && $avg <= $threshold)) {
                $output[] = [round($avg, 1), $article];
            }
        }
    }
?>

<?xml version="1.0" encoding="utf-8"?>

<feed xmlns="http://www.w3.org/2005/Atom">
    <?php $feed = $feeds[0]; ?>
    <title><?= $feed->get_title(); ?></title>
    <link href="http://localhost/~bion/rss/aggregate.php" rel="self"/>
    <link href="<?= $feed->get_base(); ?>" />
    <id><?= $feed->get_permalink(); ?></id>
    <?php if($feed->get_authors()) { ?>
        <?php foreach($feed->get_authors() as $author) { ?>
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
    <?php if($feed->get_contributors()) { ?>
        <?php foreach($feed->get_contributors() as $acontributor) { ?>
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
    <?php if($feed->get_categories()) { ?>
        <?php foreach($feed->get_categories() as $category) { ?>
            <category term="<?= $category; ?>"/>
        <?php } ?>
    <?php } ?>
    <?php if($feed->get_copyright()) { ?>
        <rights><?= $feed->get_copyright(); ?></rights>
    <?php } ?>
    <?php if($feed->get_image_url()) { ?>
        <logo><?= $feed->get_image_url(); ?></logo>
    <?php } ?>
    <updated><?= date("c"); ?></updated> <?php /* WARNING: simplepie doesn't provide ANY feed-level date */ ?>
    <?php $i = 0; ?>
    <?php foreach($output as $temp) {
        $item = $temp[1];
        /*if(!filter($item, $feedInfo["patterns"])) {
            continue;
        }*/
        ?>
        <entry>
            <title><?= $item->get_title()." - ".$temp[0]."%"; ?></title>
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