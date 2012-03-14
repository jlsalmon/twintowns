/**
 * File: town-chooser.js
 * Authors: Justin Lewis Salmon, Chris Smith
 * 
 * Description:
 *      Detects the onchange() event of the town/twin chooser select boxes,
 *      and sends the new values to the server via AJAX.
 */

/* Called in the onchange event of the main town select box */
function switchTown() {
    var townName, twinName;
    var selected = $("#tabs").tabs('option', 'selected');

    $.get('config.xml', function(xml) {		
        $("#twin-chooser").html('');
			
        townName = $("#town-chooser option:selected").text();
        $(xml).find("town[type='main']:has(name):contains(" + townName + ") twins twin").each(function() {
            $("#twin-chooser").append('<option value="' + $((this)).text() + '">' + $((this)).text() + '</option>');
        });
        twinName = $("#twin-chooser option:first").text();
            
            
        $("#current-town").text(townName);
        $("#current-twin").text(twinName);
			
        /* AJAX request with newly selected twin name as parameter */
        $.post('process.php', {
            town: townName, 
            twin: twinName
        }, function() {
            if (selected == 1) {
                panNewTown();
            } else {
                /* Refresh the tab */
                $("#tabs").tabs('load',selected);
            }
        });
    });
}

/* Called in the onchange event of the twin select box */
function switchTwin() {
    /* Get the current tab */
    var selected = $("#tabs").tabs('option', 'selected');
    $("#current-twin").text($("#twin-chooser option:selected").text());
        
    /* If the Maps tab is selected, do something different */
    if (selected == 1) {
        panNewTwin();
        $.post('process.php', {
            twin: $("#twin-chooser option:selected").text()
        });    
            
    } else {
        /* AJAX request with newly selected twin name as parameter */
        $.post('process.php', {
            twin: $("#twin-chooser option:selected").text()
        });
        /* Refresh the tab */
        $("#tabs").tabs('load',selected);
    }       
}