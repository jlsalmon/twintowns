<?php
/**
 * File: time.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      This file simply outputs the current time and timezone for
 *      the currently selected town/twin. It will be periodically
 *      updated via Javascript.
 * 
 *      Uses the Earthtools API to get the current time for a town,
 *      based on latitude/longitude.
 */
$town = $config->xpath('//town[name="' . $_SESSION['town'] . '"]');
$twin = $config->xpath('//town[name="' . $_SESSION['twin'] . '"]');

printTime(getLocalTime($town), $town[0]->timezone, $position = "left");
printTime(getLocalTime($twin), $twin[0]->timezone, $position = "right");

/**
 *
 * @param type $town
 * @return type 
 */
function getLocalTime($town) {
    $tz_info = simplexml_load_string(proxy_retrieve(
            "http://www.earthtools.org/timezone-1.1/"
            . $town[0]->location->lat
            . "/" . $town[0]->location->long
            ));
    
    $time = $tz_info->isotime;
    $time = explode(" ", $time);
    return explode(":", $time[1]);
}

/**
 * Outputs the current time for the given timezone.
 * 
 * @param array the current time in the specified timezone.
 * @param string $tz the timezone to be displayed.
 * @param type $position the position on the screen to display 
 *        the time.
 */
function printTime($time, $tz, $position) {

    ?>

    <div class="<? echo $position; ?>">    
        <div class="clock-<? echo $position; ?>">
            <strong>
                <span class="hours"><?= $time[0]; ?></span>:<span class="minutes"><?= $time[1]; ?></span>:<span class="seconds"><?= $time[2]; ?></span>
            </strong>
            <? echo $tz; ?></div>
        <?
        if ($position == 'left') {
            echo "<span class='town-name-$position'>"
            . $_SESSION['town']
            . " Local Time</span>";
        } else {
            echo "<span class='town-name-$position'>"
            . $_SESSION['twin']
            . " Local Time</span>";
        }
        ?>
    </div>
    <?
}
?>