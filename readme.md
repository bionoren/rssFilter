About
-----
regex filters for various components of rss feeds. Useful for filtering:
* Types of content (videos, audio)
* Ads
* Types of articles (summaries, links to other feeds, categories, etc)
* Anything else you can find in the feed with a regex

Feed Aggregation (BETA)
* Only show articles if their titles are sufficiently similar or dissimilar (see http://us1.php.net/manual/en/function.similar-text.php)
* Article similarity rating shown after article title
* Useful for only getting major news events or filtering out duplicates across feeds
* URL customizable filter level - easily tweak filter threshold

Setup
-----
Put all files in your web server's document directory.
Navigate to admin.php. Here you can add new rss/atom feeds and set up regex filters for them.
Once you've added a feed, you will be provided a URL for your filtered feed. Put this into your RSS reader of choice, and get on with your life.

Hacking
-------
Don't see the field you want to filter by? Need something more complex? Check out the filter function in functions.php!

*****************
See MIT.txt for licensing info
