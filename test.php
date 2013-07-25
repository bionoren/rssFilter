<?php
    $path = "./";
    require_once($path."autoloader.php");
    require_once($path."functions.php");
    require_once($path."db/SQLiteManager.php");

    $urls = ["http://www.nytimes.com/services/xml/rss/nyt/GlobalHome.xml", "http://newsrss.bbc.co.uk/rss/newsonline_uk_edition/world/rss.xml", "http://feeds.reuters.com/reuters/topNews"];

    $articles = [];
    $i = 0;
    foreach($urls as $url) {
        $feed = new SimplePie();
        $feed->enable_cache(false);
        $feed->set_feed_url($url);
        $feed->init();
        // This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
        $feed->handle_content_type();

        foreach($feed->get_items() as $item) {
            $title = $item->get_title();
            $summary = $item->get_description();

            $articles[$i][$title] = $summary;
        }

        $i++;
    }

    $articles2 = $articles;

    foreach($articles as $src1=>$feedArticles) {
        foreach($feedArticles as $title=>$article) {
            foreach($articles2 as $src2=>$feedArticles2) {
                if($src1 == $src2) {
                    continue;
                }
                foreach($feedArticles2 as $title2=>$article2) {
                    $similarity;
                    similar_text($title, $title2, $similarity);
                    if($similarity > 60) {
                        print $similarity." - ".$title." - ".$title2." <br>";
                    }
                }
            }
        }
    }
?>