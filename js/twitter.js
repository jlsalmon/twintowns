/**
 * File: twitter.js
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      Causes the twitter feed to be refreshed at regular time intervals.
 *      Also Takes the timestamp of each tweet on the page, and replaces it
 *      with a string representing how long ago the tweet was posted.
 */


/* Refresh the twitter feeds every 10 seconds */
var i = setInterval(refreshTweets, 10000); 

function refreshTweets() {
    $("#twitter-container").fadeOut().load("twitter.php", function() {
        $('small.time').each(function() {
            $((this)).text(timeAgo($((this)).text()));        
        });
        $(this).fadeIn();
    });
}
            
function timeAgo(dateString) {
                
    var diff = new Date() - new Date(dateString);
    var second = 1000, minute = second * 60,
    hour = minute * 60, day = hour * 24;

    if (isNaN(diff) || diff < 0) {
        return ""; 
    } else if (diff < second * 2) {
        return "right now";
    } else if (diff < minute) {
        return Math.floor(diff / second) + " seconds ago";
    } else if (diff < minute * 2) {
        return "about 1 minute ago";
    } else if (diff < hour) {
        return Math.floor(diff / minute) + " minutes ago";
    } else if (diff < hour * 2) {
        return "about 1 hour ago";
    } else if (diff < day) {
        return  Math.floor(diff / hour) + " hours ago";
    } else {
        return "more than " 
            + Math.floor(diff / hour) + " hours ago";
    }
}