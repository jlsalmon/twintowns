/**
 * File: time.js
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      This function acts as a clock, updating the current time on
 *      the page every second.
 */

$(window).load(function() {
    var time = '00:00:00';
    var arr_time = time.split(':');
    var ss = arr_time[2];
    var mm = arr_time[1];
    var hh = arr_time[0];
    var update_ss = setInterval(updatetime, 1000);
    
    function updatetime() {
        ss++;
        if (ss < 10) {
            ss = '0' + ss;
        }
        if (ss == 60) {
            ss = '00';
            mm++;
            if (mm < 10) {
                mm = '0' + mm;
            }
            if (mm == 60) {
                mm = '00';
                hh++;
                if (hh < 10) {
                    hh = '0' + hh;
                }
                if (hh == 24) {
                    hh = '00';
                }
                $(".hours").html(hh);
            }
            $(".minutes").html(mm);
        }
        $(".seconds").html(ss);
    }
});