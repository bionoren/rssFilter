{extends file="page.tpl"}

{block name="body"}
    <form method="post" action="postback.php">
        <input type="hidden" name="mode" value="addFeed">
        <input type="text" name="feed" size="100">
        <input type="submit" name="submit" value="New Feed">
    </form>
    <br>
    {foreach $feeds as $feed}
        <hr>
        <a href="http://localhost/~bion/rss/index.php?id={$feed["ID"]}">{$feed["feed"]}</a>
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
{/block}