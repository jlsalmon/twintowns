<!--
	Document:     maps.php
	Created on:   16/01/2012
	Author:      Christopher Smith 10010381
	Descritption:
		modulular php file to output Google maps containing tourist 
		information supplied by Lonely Planet for the twin towns
	
-->
<div id="map_controls" class="box-rounded">
	<h4 class="ui-widget-header">Map controls</h4>
	<div class="box-inside">
		<fieldset>
			<label for="map_controls_lp_pois">Lonely Planet</label><input type="checkbox" id="map_controls_lp_pois" onchange="toggleLPMarkers()" checked="checked" />
		</fieldset>
		<fieldset>
			<label for="map_controls_lp_labels">Place names on hover</label><input type="checkbox" id="map_controls_lp_labels" onchange="toggleLPLabels()" />
			<p class="small">This feature might slow down your browser on large towns and cities!</p>
		</fieldset>
		<div class="clear"></div>
	</div>
</div>
<div id="map_container">
	<div id="town_map_container"></div>
	<div id="twin_map_container"></div>
	<div class="clear"></div>
</div>
<div id="place_info" class="box-rounded">
	<h4 id="place_info_title" class="ui-widget-header">Place info</h4>
	<div id="place_info_content" class="box-inside">
		<p>Click a Map marker to see more information here!</p>
	</div>
</div>
<div id="maps-spinner" class="spinner">
	<img id="img-spinner" src="images/loading.gif" alt="Loading"/>
	<p>This can take a while for bigger towns and cities</p>
</div>
<script type="text/javascript">

	$(document).ready(function() {
	
		var spinnerImage = $("#maps-spinner img");
		var	spinnerP = $("#maps-spinner p");
		var	outerDiv = $("#ui-tabs-2");
			
		spinnerImage.css('left', (outerDiv.innerWidth() / 2) - (spinnerImage.innerWidth() / 2) + 'px');
			
		spinnerP.css('left', (outerDiv.innerWidth() / 2) - (spinnerP.innerWidth() / 2) + 'px');
		
		$.get('config.xml', function(data) {
			setConfig(data);
			
			// GETS CONFIG XML AND ASYNCRONOUSLY LOADS 
			// THE GOOGLE MAPS SCRIPT - CREATES SCRIPT 
			// TAG WHICH GETS PUT INTO THE MAIN PAGE
			
			apikey = $(data).find("module:has('name'):contains('Google Maps') apikey").text()
			
			// GOOGLE MAPS SCRIPT CALL INCLUDES CALL BACK TO INITIALIZE 
			// FUNCITON IN maps.js
			
			script = document.createElement("script");
			script.type = "text/javascript";
			script.src = "http://maps.googleapis.com/maps/api/js?key=" + apikey + "&sensor=false&callback=initialize";
			document.body.appendChild(script);
		});
		
	});
	
</script>
