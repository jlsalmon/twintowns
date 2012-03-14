<?php
/**
 * File: geolookup.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      Performs a reverse geolookup based on the user's latitude and 
 *      longitude (passed in via JavaScript) to retrieve the name of
 *      the town/city the user is currently in. Uses the Geonames API
 *      for the lookup.
 * 
 *      Also performs distance and travel time calculations.
 */
require_once 'lib.php';

session_start();
$config = loadConfig();

$town = $config->xpath('//town[name="' . $_SESSION['town'] . '"]');
$twin = $config->xpath('//town[name="' . $_SESSION['twin'] . '"]');

if ($_GET['type'] == 'reverse') {
    $loc = json_decode(proxy_retrieve('http://ws.geonames.org/findNearbyPlaceNameJSON?lat='
                    . $_GET['lat'] . '&lng=' . $_GET['lng']));

    $town_dist = calcDistance($town, $_GET['lat'], $_GET['lng'], "mi");
    $twin_dist = calcDistance($twin, $_GET['lat'], $_GET['lng'], "mi");
    ?>
    <div class="location">
        You are in <strong><? echo $loc->geonames[0]->name; ?></strong>.
    </div>
    <br />
    <?
    printDistanceInfo($town, $position = "left");
    printDistanceInfo($twin, $position = "right");
}

/**
 * Outputs a HTML block with info about how far away from the current town
 * the user is, and how long it would take to get there.
 * 
 * @param SimpleXMLElement $town the XML tree for the current town.
 * @param string $position the position on the page to print to.
 */
function printDistanceInfo($town, $position) {
    ?>   
    <div class="<? echo $position; ?>">
        <span class="distance">
            You are about
            <strong>
                <?
                echo $dist = calcDistance(
                $town, $_GET['lat'], $_GET['lng'], "mi"
                );
                ?>
            </strong>
            miles from
            <? echo $town[0]->name; ?>.
        </span>
        <br />
        <span class="distance-small">
            That's about 
            <strong>
                <?
                echo calcDistance(
                        $town, $_GET['lat'], $_GET['lng'], "km"
                );
                ?>
            </strong>
            kilometres.
        </span>
        <br />
        <span class="flight-time">
            It would take you roughly
            <strong>
                <? echo calcFlightTime($dist); ?>
            </strong>
            to fly there by plane.
        </span>
        <br />
        <span class="car-time">
            Or roughly
            <strong>
                <? echo calcCarTime($dist); ?>
            </strong>
            to drive there by car.
        </span>
    </div>
    <?
}

/**
 * Uses the ‘haversine’ formula to calculate the great-circle 
 * distance between two points – that is, the shortest distance over 
 * the earth’s surface – giving an ‘as-the-crow-flies’ distance 
 * between the points. It is probably wildly inaccurate.
 * 
 * @param SimpleXMLElement $town the xml tree of the current 
 *        town being processed.
 * @param float $lat2 user's current latitude.
 * @param float $lng2 user's current longitude.
 * @param string $unit the distance unit to return.
 */
function calcDistance($town, $lat2, $lng2, $unit) {
    $lat1 = $town[0]->location->lat;
    $lng1 = $town[0]->location->long;

    $theta = $lng1 - $lng2;
    $dist = sin(deg2rad($lat1))
            * sin(deg2rad($lat2)) + cos(deg2rad($lat1))
            * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;

    if ($unit == "km") {
        return round($miles * 1.609344);
    } else if ($unit == "nm") {
        return round($miles * 0.8684);
    } else {
        return round($miles);
    }
}

function calcFlightTime($dist) {
    $decTime = $dist / 400;
    $hour = floor($decTime);
    return $hour . ($hour == 1 ? " hour " : " hours " )
            . ($mins = round(60 * ($decTime - $hour)))
            . ($mins == 1 ? " minute " : " minutes ");
}

function calcCarTime($dist) {
    $decTime = $dist / 50;
    $hour = floor($decTime);
    return $hour . ($hour == 1 ? " hour " : " hours " )
            . ($mins = round(60 * ($decTime - $hour)))
            . ($mins == 1 ? " minute " : " minutes ");
}
?>