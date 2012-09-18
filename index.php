<?xml version="1.0" encoding="utf-8"?>

<feed xmlns="http://www.w3.org/2005/Atom">
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

    $result = $db->select("feeds", null, ["ID"=>$id]);
    $feedInfo = $db->fetchArray($result)[0];
    if(!$feedInfo) {
        die("bad id ".$id);
    }
    $result = $db->select("filters", null, ["feedID"=>$feedInfo["ID"]]);
    $feedInfo["patterns"] = $db->fetchArray($result);

    $feed = new SimplePie();
    $feed->enable_cache(false);
    $feed->set_feed_url($feedInfo["feed"]);
    $feed->init();
    // This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
    $feed->handle_content_type();
?>
    <title><?= $feed->get_title(); ?></title>
    <link href="http://localhost/~bion/rss/index.php" rel="self"/>
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
    <?php foreach($feed->get_items(0, $feedInfo["maxItems"]) as $item) { ?>
        <?php
            if(!filter($item, $feedInfo["patterns"])) {
                continue;
            }
        ?>
        <entry>
            <title><?= $item->get_title(); ?></title>
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