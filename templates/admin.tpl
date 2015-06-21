{extends file="page.tpl"}

{block name="body"}
    <form method="post" action="postback.php">
        <input type="hidden" name="mode" value="addFeed">
        <input type="text" name="feed" size="100">
        <input type="submit" name="submit" value="New Feed">
    </form>
    <form method="post" action="postback.php" id="aggregateForm">
        <input type="hidden" name="mode" value="addAggregateFeed">
        <textarea rows="4" cols="100" name="feeds" form="aggregateForm"></textarea>
        <input type="submit" name="submit" value="Create Aggregate Feed">
    </form>
    <br>
    {foreach $feeds as $feed}
        <hr>
        <a href="{$base_url}/index.php?id={$feed["ID"]}">{$feed["feed"]}</a>
        <form method="post" action="postback.php">
            <input type="hidden" name="mode" value="setFeedProperties">
            <input type="hidden" name="feedID" value="{$feed["ID"]}">
            Max Items: <input type="text" name="maxItems" value="{$feed["maxItems"]}">
            <br />
            <select name="blockOrPermit">
            <option value="block"{if $feed["blockOrPermit"] ne 'permit'} selected="selected"{/if}>Block</option>
            <option value="permit"{if $feed["blockOrPermit"] eq 'permit'} selected="selected"{/if}>Permit</option>
            </select>
            items that match
            <select name="anyOrAll">
            <option value="any"{if $feed["anyOrAll"] ne 'all'} selected="selected"{/if}>any</option>
            <option value="all"{if $feed["anyOrAll"] eq 'all'} selected="selected"{/if}>all</option>
            </select>
            of the following rules:
            <input type="submit" name="submit" value="Save">
        </form>
        <table>
            <tr>
                <td>
                    Regex
                </td>
                <td>
                    Field
                </td>
                <td>
                    Case Insensitive
                </td>
                <td></td>
            </tr>
            <tr>
                <form method="post" action="postback.php">
                    <input type="hidden" name="mode" value="addRegex">
                    <input type="hidden" name="feedID" value="{$feed["ID"]}">
                    <td>
                        /<input type="text" name="regex" size="50">/s
                    </td>
                    <td>
                        <select name="field">;
                        {foreach $fields as $field}
                            <option value="{$field}">{$field}</option>
                        {/foreach}
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" name="caseInsensitive">
                    </td>
                    <td>
                        <input type="submit" name="submit" value="Add">
                    </td>
                </form>
            </tr>
            {foreach $feed["patterns"] as $pattern}
                <form method="post" action="postback.php">
                    <input type="hidden" name="mode" value="deleteRegex">
                    <input type="hidden" name="filterID" value="{$pattern["ID"]}">
                    <tr>
                        <td>
                            {$pattern["regex"]}
                        </td>
                        <td>
                            {$pattern["field"]}
                        </td>
                        <td></td>
                        <td><input type="submit" name="submit" value="Delete"></td>
                    </tr>
                </form>
            {/foreach}
        </table>
    {/foreach}
    Aggregate Feeds
    <br>
    {foreach $aggregates as $feed}
        <hr>
        <a href="{$base_url}/aggregate.php?id={$feed["ID"]}&threshold=75&minThreshold=true&grouping=1">{$feed["feeds"]|nl2br}</a>
        <br>
        Note: Articles will be sourced from the first feed in the list, all others will simply be used as filters
        <form method="post" action="postback.php" id="aggregateForm{$feed["ID"]}">
            <input type="hidden" name="mode" value="updateAggregateFeed">
            <input type="hidden" name="id" value="{$feed["ID"]}">
            <textarea rows="4" cols="100" name="feeds" form="aggregateForm{$feed["ID"]}">{$feed["feeds"]}</textarea>
            <input type="submit" name="submit" value="Update">
        </form>
    {/foreach}
{/block}