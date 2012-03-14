<?php
/**
 * File: weather_box.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      Prints a small weather box containing current conditions and
 *      a 4-day forecast for the current town and twin.
 */
$town = $config->xpath('//town[name="' . $_SESSION['town'] . '"]');
$twin = $config->xpath('//town[name="' . $_SESSION['twin'] . '"]');

printWeather($town, $position = "left");
printWeather($twin, $position = "right");
printAttrib();

/**
 * Gets live and forecast feeds for the supplied town, and prints it
 * in a pretty little box.
 * 
 * @param SimpleXMLElement $town the XML tree for the current town.
 * @param string $position the position on the screen to print.
 */
function printWeather($town, $position) {
    $weather_params = $town[0]->xpath('weather');

    $live_feed = getLiveWeather($weather_params);
    $forecast_feed = getForecastWeather($weather_params);

    printWeatherBox($live_feed, $forecast_feed, $position);
}

/**
 * Builds a URL for a live weather feed based upon the configuration
 * parameters for the supplied town and returns the retrieved feed.
 * 
 * @global SimpleXMLElement $config the global config XML tree.
 * @param SimpleXMLElement $weather_params the XML tree containing
 *        weather feed parameters for the current town.
 * @return SimpleXMLElement the XML tree of the retrieved feed.
 */
function getLiveWeather($weather_params) {
    global $config;

    $module = $config->xpath('//module[name="Weather"]');
    $feed_params = $weather_params[0]->xpath('source[@type="live"]');
    $base = $module[0]->xpath('url[@source="Weather Underground"]');

    $url = (string) ($base[0]->base
            . $module[0]->apikey
            . $base[0]->params['live_ext']
            . $feed_params[0]['wunderground_station_id']);

    return loadFeed($url);
}

/**
 * Builds a URL for a forecast weather feed based upon the configuration
 * parameters for the supplied town and returns the retrieved feed.
 * 
 * @global SimpleXMLElement $config the global config XML tree.
 * @param SimpleXMLElement $weather_params the XML tree containing
 *        weather feed parameters for the current town.
 * @return SimpleXMLElement the XML tree of the retrieved feed.
 */
function getForecastWeather($weather_params) {
    global $config;

    $module = $config->xpath('//module[name="Weather"]');
    $feed_params = $weather_params[0]->xpath('source[@type="forecast"]');
    $base = $module[0]->xpath('url[@source="Google Weather"]');

    $url = (string) ($base[0]->base
            . $feed_params[0]['google_search_q']);
    return loadFeed($url);
}

/**
 * Retrieves a UTF-8 encoded XML tree from the given URL.
 * 
 * @param string $url the URL of the feed to retrieve.
 * @return SimpleXMLElement the XML tree of the retrieved feed. 
 */
function loadFeed($url) {
    return simplexml_load_string(utf8_encode(proxy_retrieve($url)));
}

/**
 * Outputs the actual weather box HTML, with pretty formatting.
 * 
 * @param SimpleXMLElement $live_feed the live XML tree.
 * @param SimpleXMLElement $forecast_feed the forecast XML tree.
 * @param string $position the position on the screen to print.
 */
function printWeatherBox($live_feed, $forecast_feed, $position) {
    ?>
    <div id="weather-box">
        <table class="<? echo $position; ?>">
            <tbody>
                <tr>
                    <?
                    if ($position == "right") {
                        printCurrentConditions($forecast_feed, $position);
                    }

                    foreach ($forecast_feed->weather->forecast_conditions as $day) {
                        ?>
                        <td>
                            <em><? echo $day->day_of_week['data']; ?></em>
                            <br />
                            <img class="small-img" 
                                 src="images/<? echo image($day->icon['data']); ?>" />
                            <small class="tiny">
                                <?
                                echo FtoC($day->low['data'])
                                . "&deg; | "
                                . FtoC($day->high['data']);
                                ?>&deg;
                            </small>
                            <br />
                            <small>
                                <?
                                echo FtoC(($day->high['data'] + $day->low['data']) / 2);
                                ?>&deg;
                            </small>
                        </td>

                        <?
                    }

                    if ($position == "left") {
                        printCurrentConditions($forecast_feed, $position);
                    }
                    ?> 

                </tr>
                <tr>
                    <td colspan="6" class="last-upd">
                        <? echo $live_feed->current_observation->observation_time; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?
}

/**
 * Prints the portion of the weather box that contains the current
 * conditions (town name, condition, humidity, wind).
 * 
 * @param SimpleXMLElement $feed the feed XML tree.
 * @param string $position the position on the screen to print.
 */
function printCurrentConditions($feed, $position) {

    if ($position == "right") {
        printCurrentDay($feed);
    }
    ?>
    <td>
        <em class="town-name">
            <?
            if ($position == "left")
                echo $_SESSION['town'];
            else
                echo $_SESSION['twin'];
            ?>
        </em>
        <br />
        <small>
            <?
            echo $feed->weather->current_conditions->condition['data'];
            ?>
        </small>
        <br />
        <small class="small">
            <?
            echo $feed->weather->current_conditions->wind_condition['data'];
            ?>
        </small>
        <br />
        <small class="small">
            <? echo $feed->weather->current_conditions->humidity['data'];
            ?>
        </small>
    </td>

    <?
    if ($position == "left") {
        printCurrentDay($feed);
    }
}

/**
 * Prints the portion of the weather box containing the current day's
 * icon and temperature.
 * 
 * @param SimpleXMLElement $feed the feed XML tree.
 */
function printCurrentDay($feed) {
    ?>
    <td>
        <em class="temp">
            <?
            echo $feed->weather->current_conditions->temp_c['data'];
            ?><sup>&deg;C</sup>
        </em>
        <img class="big-img" src="images/<?
        echo image($feed->weather->current_conditions->icon['data']);
            ?>" />                  
    </td>
    <?
}

/**
 * Converts fahrenheit to celcius.
 * 
 * @param double $f the temperature in fahrenheit.
 * @return integer the rounded temperature in celsius. 
 */
function FtoC($f) {
    return (integer) (($f - 32) / 1.8);
}

/**
 * Strips the Google Weather icon url and returns only the filename,
 * so that local images can be used instead.
 * 
 * @param string $url the full URL to be stripped.
 */
function image($url) {
    $url = explode("/", $url);
    echo $url[4];
}

/**
 * Displays an attribution link for Google Weather and Weather
 * Underground.
 * 
 * @global SimpleXMLElement $config the global config XML tree.
 */
function printAttrib() {
    global $config;
    $sources = $config->xpath('//module[name="Weather"]/url');
    ?>
    <div class="right">
        <span class="attribution">
            Sources: 
            <?
            foreach ($sources as $s) {
                echo "<a href='" . $s[0]->attrib
                . "'>" . $s[0]['source'] . "</a> ";
            }
            ?>
        </span>
    </div>
    <?
}
?>
