<?php
/**
 * File: twitter.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      Pulls a JSON string of tweets based upon the latitude/longitude
 *      of the currently selected town and twin. Some fancy methods then
 *      format and output the tweets with avatars, links and hashtags 
 *      as actual links to the relevant page on Twitter.
 * 
 *      Javascript is used in a separate file to calculate how long it 
 *      has been since the tweet was posted, and to refresh the feed at
 *      regular intervals.
 */
require_once 'lib.php';

session_start();
$config = loadConfig();

$town = $config->xpath('//town[name="' . $_SESSION['town'] . '"]');
$twin = $config->xpath('//town[name="' . $_SESSION['twin'] . '"]');

getTweets($town, $position = "left");
getTweets($twin, $position = "right");

/**
 * Builds a request URL based on the config file parameters, gets
 * the returned JSON string and decodes it into an associative
 * array, then passes it on to be printed.
 * 
 * @global SimpleXMLElement $config the global config XML tree.
 * @param SimpleXMLElement $town the current town XML tree.
 * @param string $position the position on the screen to output
 *        the tweets.
 */
function getTweets($town, $position) {
    global $config;

    $module = $config->xpath('//module[name="Twitter"]');
    $base = $module[0]->xpath('url[@source="Twitter"]');
    $feed_params = $base[0]->params;

    $url = (string) ($base[0]->base
            . "geocode="
            . $town[0]->location->lat . ","
            . $town[0]->location->long . ","
            . $feed_params[0]['search_radius']
            . "&include_entities="
            . $feed_params[0]['include_entities']
            . "&rpp="
            . $feed_params[0]['rpp']);
    $tweets = json_decode(proxy_retrieve($url));

    printTweets($tweets, $town, $position);
}

/**
 * Outputs the tweets in a fancy format, with proper URLs, avatars, 
 * usernames and hashtags.
 * 
 * @param array $tweets the decoded JSON array containing all the 
 *        twitter data.
 * @param SimpleXMLElement $town the current town XML tree.
 * @param string $position the position on the screen to print the
 *        tweets.
 */
function printTweets($tweets, $town, $position) {
    ?>  
    <div id="twitter" style="float: <? echo $position ?>">
        <div class="box-rounded">
            <h5 class="ui-widget-header">
                <span class="<? echo $position; ?>">
                    Latest Tweets from <? echo $town[0]->name; ?>
                </span>
                <div class="clear"></div>
            </h5>
            <div class="box-inside">
                <table class="<? echo $position; ?>">
                    <?
                    /* Print a table row for each tweet */
                    foreach ($tweets->results as $tweet) {
                        ?>
                        <tr class="tweet">
                            <?
                            if ($position == "left") {
                                imageify($tweet);
                            }
                            ?>
                            <td>
                                <span class="user">
                                    <? echo userify($tweet->from_user); ?>
                                </span><br />
                                <span class="text">
                                    <? echo linkify($tweet->text); ?>
                                    <br />
                                    <small class="time">
                                        <? echo $tweet->created_at; ?>
                                    </small>
                                </span>
                            </td>
                            <?
                            if ($position == "right") {
                                imageify($tweet);
                            }
                            ?>
                        </tr>
                        <?
                    }
                    ?>
                </table>
                <div class="clear"></div>
            </div>           
        </div>
    </div>
    <?
}

/**
 * Creates a hyperlinked avatar for the user who posted this 
 * particular tweet.
 * 
 * @param array $tweet the array of data for a single tweet.
 */
function imageify($tweet) {
    ?>
    <td>
        <a href="http://www.twitter.com/<? echo $tweet->from_user; ?>">
            <img src="<? echo $tweet->profile_image_url; ?>"
                 width="48px" height="48px"/>
        </a>
    </td>
    <?
}

/**
 * Takes the entire tweet text and processes it, turning usernames, link
 * text and hash tags into hyperlinks to the appropriate page on Twitter.
 * 
 * @param string $text the tweet text.
 * @return string the linkified tweet.
 */
function linkify($text) {
    /* linkify URLs */
    $text = preg_replace(
            '/(https?:\/\/\S+)/', '<a href="\1">\1</a>', $text
    );

    /* linkify twitter users */
    $text = preg_replace(
            '/(^|\s)@(\w+)/', '\1<a href="http://twitter.com/\2">@\2</a>', $text
    );

    /* linkify tags */
    $text = preg_replace(
            '/(^|\s)#(\w+)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $text
    );

    return $text;
}

/**
 * Turns the supplied username into a link to their profile on Twitter.
 * 
 * @param string $user the twit's username.
 * @return string the URL to the twit's profile.
 */
function userify($user) {
    $user = '<a href="http://www.twitter.com/' . $user . '" title="' . $user . '">' . $user . '</a> ';
    return $user;
}

/**
 * Displays a hyperlinked attribution to Twitter.
 * 
 * @global SimpleXMLElement $config the global config XML tree. 
 */
function printTwitterAttrib() {
    global $config;

    $attrib = $config->xpath("//module[name='Twitter']/url");
    ?>
    <div class="clear"></div>
    <div class="attribution">
        Data courtesy of 
        <a href="<? echo $attrib[0]->attrib; ?>">
            twitter.com
        </a>
    </div>
    <?
}
?>
