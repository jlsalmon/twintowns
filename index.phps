<?php
/**
 * File: index.php
 * Authors: Justin Lewis Salmon, Chris Smith
 * 
 * Description:
 *      The main index file for the DSA Twin Towns application.
 *      This file instantiates the AJAX tabs interface, which in
 *      turn loads most of the site content.
 * 
 *      The site makes use of PHP sessions to persist the names
 *      of the currently selected town and it's twin (which can 
 *      be changed using the dropdown boxes at the top of this
 *      page).
 */
include 'lib.php';

/* So we can use $_SESSION vars */
session_start();
/* Grab a copy of config.xml */
$config = loadConfig();

/* Set default towns if necessary */
if (!isset($_SESSION['town'])) {
    $town = $config->xpath("//town[name='Exeter']");
    $_SESSION['town'] = (string) $town[0]->name;
}
if (!isset($_SESSION['twin'])) {
    $twin = $config->xpath("//town[name='Rennes']");
    $_SESSION['twin'] = (string) $twin[0]->name;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Twin Towns Mashup</title>

        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/style.css" type="text/css" />
        <link rel="stylesheet" type="text/css" href="css/maps.css" />
        <link rel="stylesheet" type="text/css" href="css/weather.css" />
        <link rel="stylesheet" type="text/css" href="css/twitter.css" />
        <link rel="stylesheet" type="text/css" href="css/gallery.css" />
        <link href="css/ticker.css" rel="stylesheet" type="text/css" />
        <link type="text/css" href="css/smoothness/jquery-ui-1.8.16.custom.css"
              rel="stylesheet" />

    </head>
    <body>
        <div class="main-container">
            <div class="container">
                <? include 'town-chooser.php'; ?>
            </div>
        </div>

        <div class="main-container">
            <div class="container">
                <div id="tabs">
                    <ul>
                        <li><a href="home.php"><span>Home&nbsp;</span></a></li>
                        <li><a id="map_tab" href="maps.php"><span>Maps&nbsp;</span></a></li>
                        <li><a href="weather.php"><span>Weather&nbsp;</span></a></li>
                        <li><a href="news.php"><span>News&nbsp;</span></a></li>
                    </ul>
                </div>
            </div>
            <br /> <br />
        </div>

        <footer>
            <p class="tag-left">
                Tabs courtesy of <a href="http://jqueryui.com">JQuery UI</a>
            </p>
            <p class="tag-right">
                Twin Towns Mashup built by 
                <a href="http://justinsalmon.co.uk">Justin Lewis Salmon</a>, 
                <a href="http://funkyrobot.net">Chris Smith</a>, 
                <a href="#">Nathan Luke Steers</a>
            </p>
            <br class="clear" />
        </footer>

        <br />
        <br />

        <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="js/town-chooser.js"></script>
        <script type="text/javascript" src="js/twitter.js"></script>
        <script type="text/javascript" src="js/time.js"></script>
        <script type="text/javascript" src="js/location.js"></script>
        <script type="text/javascript" src="js/gallery.js"></script>
        <script type="text/javascript">
            /* Instantiate tabs */
            $(function() {
                $("#tabs").tabs({
                    spinner: '<img id="spinner" src="images/loading.gif" />',
                    cache: true,
                    ajaxOptions: {
                        async: true,
                        error: function( xhr, status, index, anchor ) {
                            $( anchor.hash ).html(
                            "Loading..." );
                        }
                    }
                });
            });   
            
            /* Get user's location and start the image slideshow 
             * when the 'tabsload' event fires.  */
            $( "#tabs" ).bind( "tabsload", function(event, ui) {
                getLocation();
                refreshTweets();
            });
        </script>
        <script type="text/javascript" src="js/maps.js"></script>
        <script src="js/jquery.zrssfeed.min.js" type="text/javascript"></script>
        <script src="js/jquery.vticker.js" type="text/javascript"></script>

    </body>
</html>
