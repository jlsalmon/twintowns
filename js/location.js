/**
 * File: location.js
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      If the user's browser supports geolocation, this file gets the 
 *      user's latitude and longitude and passes it to geolookup.php,
 *      which will perform a reverse geolookup and return the name of
 *      the user's current location.
 */

function getLocation() {  

    /* Check for geoLocation support */
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(location) {
            url = 'geolookup.php?type=reverse&'
            + 'lat=' + location.coords.latitude 
            + '&lng=' + location.coords.longitude;

            /* Get reverse geo data and print location info */
            $.get(url, function(data) {
                $(document).find('#geo')
                .html(data);
            });
        }, error);

    } else {
        $(document).find('#geo')
        .html('<p>Your browser does not support geolocation.</p>');
    }
}

/**
 * Error callback for getCurrentPosition() failure.
 */
function error() {
    $(document).find('#geo').html('<p>The page could not get your location.</p>');
}
