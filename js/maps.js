/* maps.js ------------------------------------------------------------------
	author: Christopher Smith 
	website: www.funkyrobot.net
	email: chris@funkyrobot.net 
	UWE id: 10010381
	date: 17.02.2012
----------------------------------------------------------------------------- */

/* GLOBALS ------------------------------------------------------------------ */
var configXML; // Globally store the whole config tree
var currentTown, currentTwin; // Current town and twin (string)
var townMap, twinMap; // Town and twin google map objects

// Google map boundary objects for the current town and twin
var currentTownBounds, currentTwinBounds; 

// Google maps LatLng objects
var currentTownCenter, currentTwinCenter; 

// XML tree selections for the current town and twin
var townXML, twinXML;

// These listeners are made global so that they can be changed when 
// the twin or town is swtiched
var townOutOfBoundsListener, twinOutOfBoundsListener;

// Lonely Planet place ID's for the currently displayed towns
var LPTownId, LPTwinId;

// Hold LonelyPlanet Points Of Interest (pois)
var LPTownPois = new Array(), LPTwinPois = new Array(); 

// Holds the LP API base url for getting POIS for a placeID
var LPServiceUrl;

// LonelyPlanet Google maps marker and shadow images and a 
// different image when a marker is selected - google.maps.MarkerImage
var LPMarkerImage, LPMarkerShadow, LPMarkerSelected;

// index of the current selected LP maps marker and the map the marker 
// is on town|twin map
var currentSelectedPoi = 0, currentSelectedPoiMap;

// Boolean if the enable labels control is checked - false by default
// If true (see toggleLPLabels()) markers are loaded with hover labels
var enableLabels = false;

// This count checks how many maps have finished loading markers so that the loading spinner can be hidden
var loadCount = 0;

// Thumb-sized relational map (top right corner) Google static map (image)
// root divs for the controls (added to the map control layer) 
// (townRelationMapControl) and control prototypes which hold the state
// and behaviour of the controls (townRMC) 
var townRelationMapControl, twinRelationMapControl, townRMC, twinRMC;

/* Relational map control prototype
   controlDiv - root div
   staticMapImage - relative file path to map image (.png) */
function relationMapControl(controlDiv, staticMapImage) {
	this.div_ = controlDiv;
	this.div_.style.padding = '5px';
	
	this.ui_ = document.createElement('div');
	this.ui_.style.backgroundColor = 'white';
	this.ui_.style.cursor = 'pointer';
	
	this.img_ = document.createElement('img');
	this.img_.src = staticMapImage;
	
	this.ui_.appendChild(this.img_);
	
	this.div_.appendChild(this.ui_);
}

/* Additional prototype control behaviour 
   change map image when the twin or town is changed */
relationMapControl.prototype.setImage = function(staticMapImage) {
	this.img_.src = staticMapImage;
}

/* Set Config 
   called before initialize() from the maps.php page */
function setConfig(xml) {
	configXML = xml;
}

/* hideSpinner
		when loadCount is 2 (both maps have loaded) it hides the spinner overlay
*/
function hideSpinner() {
	if (loadCount > 1) {
		loadCount = 0;
		$("#maps-spinner").hide();
	}
}

/* toggleLPLabels
		will toggle enableLabels and reload markers
*/
function toggleLPLabels() {
	spinnerImage = $("#maps-spinner img");
	spinnerP = $("#maps-spinner p");
	outerDiv = $("#ui-tabs-2");
	
	$("#maps-spinner").show();
	
	if (document.getElementById('map_controls_lp_labels').checked) {
		for (marker in LPTownPois) {
			LPTownPois[marker].setMap(null);
		}
		for (marker in LPTwinPois) {
			LPTwinPois[marker].setMap(null);
		}
		LPTownPois = [];
		LPTwinPois = [];
		enableLabels = true;
		initializeLPMarkers();
	} else {
		for (marker in LPTownPois) {
			LPTownPois[marker].setMap(null);
		}
		for (marker in LPTwinPois) {
			LPTwinPois[marker].setMap(null);
		}
		LPTownPois = [];
		LPTwinPois = [];
		enableLabels = false;
		initializeLPMarkers();
	}
}

/* Marker display controls
   toggle markers used for the textbox control */
function toggleLPMarkers() {
	if (document.getElementById('map_controls_lp_pois').checked) {
		for (marker in LPTownPois) {
			LPTownPois[marker].setMap(townMap);
		}	
		for (marker in LPTwinPois) {
			LPTwinPois[marker].setMap(twinMap);
		}
	} else {
		for (marker in LPTownPois) {
			LPTownPois[marker].setMap(null);
		}
		for (marker in LPTwinPois) {
			LPTwinPois[marker].setMap(null);
		}
	}
}

/* getLPPlaceInfo (Get Lonely Planet Point of Interest Info)
		poiId - Lonely Planet point of interest ID 
		map   - the map the marker is on 
		(to help in indicating the current selected marker) 
*/ 
function getLPPlaceInfo(index, poiId, map) {
	// DESELECT ANY PREVIOUSLY SELECTED MAP MARKERS (CHANGE MARKER IMAGE)
	if (currentSelectedPoi != 0) {
		if (currentSelectedPoiMap == 'twin') {
			LPTwinPois[currentSelectedPoi].setIcon('images/lp_map-marker_image.png');
		} else if (currentSelectedPoiMap == 'town') {
			LPTownPois[currentSelectedPoi].setIcon('images/lp_map-marker_image.png');
		}
	}

	// SELECT THE NOW SELECTED MARKER (CHANGE MARKER IMAGE)
	if (map == 'twin') {
		LPTwinPois[index].setIcon('images/lp_map-marker_selected.png');
		currentSelectedPoi = index;
		currentSelectedPoiMap = map;
	} else if (map == 'town') {
		LPTownPois[index].setIcon('images/lp_map-marker_selected.png');
		currentSelectedPoi = index;
		currentSelectedPoiMap = map;
	}
	
	// GET API KEY FOR LONELY PLANET
	userpw = $(configXML).find("module:has(name):contains('Lonely Planet') apikey").text();
	
	// GET THE BASE URL FOR THE SERVICE
	LPServiceUrl = $(configXML).find("module:has(name):contains('Lonely Planet') baseurl[service = 'poibyid']").text();
	
	// ADD THE POI ID TO THE SERVICE URL
	poiUrl = LPServiceUrl.replace('{poi-id}', poiId);
	
	// JQUERY AJAX CALLS queryLP.php WHICH RETURNS THE LONELY PLANET XML 
	$.get('queryLP.php?serviceurl=' + poiUrl + '&userpw=' + userpw, function(poiXML) {
		poiName = $(poiXML).find('name').text();
		poiType = $(poiXML).find('poi-type').text();
		poiOpenTimes = $(poiXML).find('hours').text();
		poiPrice = $(poiXML).find('price-string').text();
		poiReview = $(poiXML).find('review').text();
		poiLPPage = $(poiXML).find("representation[type='lp.com']").attr('href');
		
		// CREATE AND SHOW THE INFO PANE USING XML DATA
	
		$('#place_info_title').text(poiName);
		$('#place_info_content').html('<p>Type: ' + poiType + '</p>');
		if (poiOpenTimes != '') {
			$('#place_info_content').append('<p class="small">Opening times: ' + poiOpenTimes + '</p>');
		}
		if (poiPrice != '') {
			$('#place_info_content').append('<p>' + poiPrice + '</p>');
		}
		if (poiReview != '') {
			$('#place_info_content').append('<div class="wrap-content"><h5 class="ui-widget-header">Review</h5><div class="outer">' + poiReview + '</div></div>');
		}
		$('#place_info_content').append('<ul class="place-info-urls"><li><a href="' + poiLPPage +'">' + poiLPPage + '</a></li></ul>');
	});
	
}

/* markerLabel (hover labels on markers that display the POI title)
		This is a prototype of the google maps overlay class (map marker)
		it holds the title (name_), position to be converted to a screen 
		location (latlng_) and the marker DOM (div_)
		the functionality of this prototype is encoded in 
		initializeLPMarkers() below
*/
function markerLabel(name, pos, map) {
	this.name_ = name;
	this.latlng_ = pos;
	this.div_ = document.createElement('div');
	this.div_.innerHTML = "<span>" + name + "</span>";
	this.setMap(map);
	this.hide();
}

/*  initializeLPMarkers() (Lonely Planet google maps markers on the map)
		Gets Lonely Planet places of interest and places them as markers 
		on the map.
*/
function initializeLPMarkers() {
	if (enableLabels) {
		// SUBCLASS OVERLAY VIEW
		markerLabel.prototype = new google.maps.OverlayView();
		
		// IMPLEMENT DRAW METHOD
		markerLabel.prototype.draw = function() {
			
			// RENAME DIV FOR FRIENDLYNESS
			var div = this.div_;
				
			// ADD CLASSNAME FOR CSS
			div.className = 'marker-label';
			
			// ADD THE LABEL TO THE MAP
			var panes = this.getPanes();
			panes.overlayImage.appendChild(div);
			
			// POSITION THE LABEL USING LATLONG TO PIXEL FUNCTION IN MAPS API
			var point = this.getProjection().fromLatLngToDivPixel(this.latlng_);
			if (point) {
				
				// LEFT = POINT OF MAP MARKER - (LENGTH OF LABEL / 2) TO CENTER THE LABEL 
				div.style.left = (point.x - (div.offsetWidth / 2)) + 'px';
				div.style.top = (point.y - 45) + 'px';
			}
		};
	
		// SHOW LABEL (FOR MARKER MOUSEOVER - SEE BELOW)
		markerLabel.prototype.show = function() {
			if (this.div_) {
				this.div_.style.visibility = "visible";
			}
		}
		
		// HIDE LABEL (FOR MARKER MOUSEOUT - SEE BELOW)
		markerLabel.prototype.hide = function() {
			if (this.div_) {
				this.div_.style.visibility = "hidden";
			}
		}
	}
			
	// GET LONELYPLANET API KEY
	userpw = $(configXML).find("module:has(name):contains('Lonely Planet') apikey").text();
	
	// GET THE BASE URL FOR THE SERVICE (GET POIS FOR PLACE ID)
	LPServiceUrl = $(configXML).find("module:has(name):contains('Lonely Planet') baseurl[service = 'poibypid']").text();
	
	if (LPTownPois.length < 1) {		
		// GET LP TOWNID FOR THE CURRENT TOWN
		LPTownId = $(townXML).find('lonelyplanet').find('placeid').text();
		
		// SOME TOWNS DON'T HAVE ANY LONELY PLANET PLACES OF INTEREST
		// THESE HAVE A LP TOWN ID OF 0
		if (LPTownId != 0) {
			// FILL OUT BASE URL WITH PLACE ID
			townUrl = LPServiceUrl.replace('{placeID}', LPTownId);
			townUrl = townUrl.replace('[?poi_type={type-name}]', '');
			count = 0;
			
			// GET THE LP POIS FOR THE TOWN AND LOOP ON EACH, CREATING A MARKER
			// JQUERY AJAX CALLS queryLP.php
			$.get('queryLP.php?serviceurl='+ townUrl + '&userpw=' + userpw, function(data) {
				$(data).find('poi').each(function(count) {
					latitude = $((this)).find('latitude').text();
					longitude = $((this)).find('longitude').text();
					point = new google.maps.LatLng(latitude, longitude);
					name = $((this)).find('name').text();
					id = $((this)).find('id').text();
					type = $((this)).find('poi-type').text();
					
					// ADDS MARKER TO POI COLLECTION
					// ADDITIONAL OPTIONS POI, POITYPE ADD STATE TO MARKER FOR EVENTS BELOW
					LPTownPois[count] = new google.maps.Marker({
						position: point,
						map: townMap,
						shadow: LPMarkerShadow,
						icon: LPMarkerImage,
						poi: id,
						index: count,
						poitype: type
						//animation: google.maps.Animation.DROP
					});
					
					if (enableLabels) {
						LPTownPois[count].markerlabel = new markerLabel(name, point, townMap);
					}
					
					// GET PLACE INFO ON CLICK
					google.maps.event.addListener(LPTownPois[count], 'click', function() {
						getLPPlaceInfo(((this)).index, ((this)).poi, 'town');
					});
					
					if (enableLabels) {
						// SHOW LABEL WITH TITLE ON MOUSEOVER
						google.maps.event.addListener(LPTownPois[count], 'mouseover', function() {
							((this)).markerlabel.show();
						});
						
						// HIDE LABEL ON MOUSEOUT
						google.maps.event.addListener(LPTownPois[count], 'mouseout', 	function() {
							((this)).markerlabel.hide();
						});
					}
					
					count++;
				});		
				
				loadCount++;
				hideSpinner();	
			});
		} else {
			loadCount++;
			hideSpinner();	
		}
	}

	if (LPTwinPois.length < 1) {
		LPTwinId = $((twinXML)).find('lonelyplanet').find('placeid').text();
		if (LPTwinId != 0) {
			twinUrl = LPServiceUrl.replace('{placeID}', LPTwinId);
			twinUrl = twinUrl.replace('[?poi_type={type-name}]', '');
			count = 0;
			
			// FOR EACH PLACE OF INTEREST CREATE MARKER (SAME AS TOWN ABOVE)
			$.get('queryLP.php?serviceurl='+ twinUrl + '&userpw=' + userpw, function(data2) {
				$(data2).find('poi').each(function() {
					
					latitude = $((this)).find('latitude').text();
					longitude = $((this)).find('longitude').text();
					point = new google.maps.LatLng(latitude, longitude);
					name = $((this)).find('name').text();
					id = $((this)).find('id').text();
					type = $((this)).find('poi-type').text();
					
					LPTwinPois[count] = new google.maps.Marker({
						//markerlabel: new markerLabel(name, point, twinMap),
						position: point,
						map: twinMap,
						shadow: LPMarkerShadow,
						icon: LPMarkerImage,
						poi: id,
						index: count,
						poitype: type
						//animation: google.maps.Animation.DROP
					});
					
					if (enableLabels) {
						LPTwinPois[count].markerlabel = new markerLabel(name, point, twinMap);
					}
					
					google.maps.event.addListener(LPTwinPois[count], 'click', function() {
						getLPPlaceInfo(((this)).index, ((this)).poi, 'twin');
					});
					
					if (enableLabels) {
						google.maps.event.addListener(LPTwinPois[count], 'mouseover', function() {
							((this)).markerlabel.show();
						});
						
						google.maps.event.addListener(LPTwinPois[count], 'mouseout', function() {
							((this)).markerlabel.hide();
						});
					}
					
					count++;
				});
				
				loadCount++;
				hideSpinner();
			});
		} else {
			loadCount++;
			hideSpinner();	
		}
	}
}

/* initialize() (initializaiton script that loads the map add adds markers to map)
		called from maps.php after configXML is set
		town/twin chooser select boxes are disabled while the 
		initialization script is running
*/
function initialize() {	
	// TOWN/TWIN SELECT BOXES DISABLED
	$("#town-chooser").attr('disabled', true);
	$("#twin-chooser").attr('disabled', true);
	
	$("#maps-spinner").show();
	
	// CREATE MARKER IMAGE OBJECTS 
	LPMarkerImage = new google.maps.MarkerImage('images/lp_map-marker_image.png',
		new google.maps.Size(12, 23),  // Image size
		new google.maps.Point(0, 0),   // Origin
		new google.maps.Point(6, 23)); // Anchor (base of the marker)
		
	LPMarkerShadow = new google.maps.MarkerImage('images/lp_map-marker_shadow.png',
		new google.maps.Size(24, 23), // Shadow size
		new google.maps.Point(0, 0), // Origin
		new google.maps.Point(0, 23)); // Anchor
		
	LPMarkerSelected = new google.maps.MarkerImage('images/lp_map-marker_selected.png',
		new google.maps.Size(12, 23), // Shadow size
		new google.maps.Point(0, 0), // Origin
		new google.maps.Point(6, 23)); // Anchor
		
	
	// INITIALIZE TOWNNAME (STRING) AND TOWNXML GLOBALS
	townName = $('#town-chooser option:selected').val();
	townXML = $(configXML).find("town[type='main']:has(name):contains('" + townName + "')");
	
	// DO THE SAME FOR THE CURRENT TWIN
	twinName = $('#twin-chooser option:selected').val();
	twinXML = $(configXML).find("town[type='twin']:has(name):contains('" + twinName +"')");
	
	// GET TOWN LATITUDE AND LONGITUDE FROM CONFIG
	townLat = $(townXML).find('location').find('lat').text();
	townLong = $(townXML).find('location').find('long').text();
	
	// CREATE GOOGLE MAPS LATLNG OBJECT TO CENTER MAP
	currentTownCenter = new google.maps.LatLng(townLat, townLong);
	
	// MAP OPTIONS FOR THE TOWN MAP
	townOptions = {
		zoom: 14, //  INITIAL ZOOM
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		panControl: false, // DISABLE CONTROLS THAT ARE NOT USED
		mapTypeControl: false, // LIMIT MAP TO A ROADMAP
		streetViewControl: false,
		minZoom: 13, // LIMIT ZOOM
		maxZoom: 18,
		center: currentTownCenter // INITIAL CENTER
	}
	
	// CREATE GOOGLE MAPS BOUNDS OBJECT FROM BOUNDS LATITUDE AND LONGITUDE IN CONFIG
	currentTownBounds = new google.maps.LatLngBounds(
		new google.maps.LatLng(
			$(townXML).find('bounds').find('lat:first').text(),
			$(townXML).find('bounds').find('long:first').text()
		),
		new google.maps.LatLng(
			$(townXML).find('bounds').find('lat:last').text(),
			$(townXML).find('bounds').find('long:last').text()
		)
	);
	
	// REPEAT FOR TWIN
	twinLat = $(twinXML).find('location').find('lat').text();
	twinLong = $(twinXML).find('location').find('long').text();
	
	currentTwinCenter = new google.maps.LatLng(twinLat, twinLong);
	
	twinOptions = {
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		panControl: false,
		mapTypeControl: false,
		streetViewControl: false,
		minZoom: 13,
		maxZoom: 18,
		center: currentTwinCenter
	}
	
	currentTwinBounds = new google.maps.LatLngBounds(
		new google.maps.LatLng(
			$(twinXML).find('bounds').find('lat:first').text(),
			$(twinXML).find('bounds').find('long:first').text()
		),
		new google.maps.LatLng(
			$(twinXML).find('bounds').find('lat:last').text(),
			$(twinXML).find('bounds').find('long:last').text()
		)
	);

	// INSTANTIATE GOOGLE MAP OBJECTS
	townMap = new google.maps.Map(document.getElementById("town_map_container"), townOptions);
		
	twinMap = new google.maps.Map(document.getElementById("twin_map_container"), twinOptions);
	
	// CREATE LISTENERS FOR OUT OF BOUNDS DETECTION FOR CURRENT TOWN/TWIN
	townOutOfBoundsListener = google.maps.event.addListener(townMap, 'dragend', function() {
		if (currentTownBounds.contains(townMap.getCenter())) return;
	
		townMap.panTo(currentTownCenter);	
	});
	
	twinOutOfBoundsListener = google.maps.event.addListener(twinMap, 'dragend', function() {
		if (currentTwinBounds.contains(twinMap.getCenter())) return;
	
		twinMap.panTo(currentTwinCenter);	
	});
	
	// GET PATH TO RELATIONAL MAP IMAGES FROM CONFIG
	townImg = $(townXML).find('maps mapimg').text();
	twinImg = $(twinXML).find('maps mapimg').text();
	
	// CREATE CONTROLS AND PUSH THEM ON THE MAP
	townRelationMapControl = document.createElement('div');
	townRMC = new relationMapControl(townRelationMapControl, townImg);
	
	twinRelationMapControl = document.createElement('div');
	twinRMC = new relationMapControl(twinRelationMapControl, twinImg);
	
	townMap.controls[google.maps.ControlPosition.TOP_RIGHT].push(townRelationMapControl);
	twinMap.controls[google.maps.ControlPosition.TOP_RIGHT].push(twinRelationMapControl);
	
	LPTownPois = [];
	LPTwinPois = [];
	
	// INITIALIZE MARKERS FOR CURRENT TOWN AND ALL OF ITS TWINS
	initializeLPMarkers();
		
	// ENABLE TOWN/TWIN SELECT BOXES
	$("#town-chooser").attr('disabled', false);
	$("#twin-chooser").attr('disabled', false);
	
}

/*	panNewTwin (change twin map to show another town - selected from the 
		twin select box)
		This also disables the town/twin select boxes to prevent corruption
*/
function panNewTwin() {
	// DISABLE TOWN/TWIN SELECT BOXES
	$("#town-chooser").attr('disabled', true);
	$("#twin-chooser").attr('disabled', true);
	
	spinnerImage = $("#maps-spinner img");
	spinnerP = $("#maps-spinner p");
	outerDiv = $("#ui-tabs-2");
	
	$("#maps-spinner").show();

	// CHANGE GLOBALS
	newTwin = $('#twin-chooser option:selected').val();
	twinXML = $(configXML).find("town[type='twin']:has(name):contains('" + newTwin +"')");
	
	twinLat = $(twinXML).find('location').find('lat').text();
	twinLong = $(twinXML).find('location').find('long').text();
	
	// UPDATE CENTER FOR NEW TWIN
	currentTwinCenter = new google.maps.LatLng(twinLat, twinLong);
	
	// UPDATE BOUNDS FOR NEW TWIN
	currentTwinBounds = new google.maps.LatLngBounds(
		new google.maps.LatLng(
			$(twinXML).find('bounds').find('lat:first').text(),
			$(twinXML).find('bounds').find('long:first').text()
		),
		new google.maps.LatLng(
			$(twinXML).find('bounds').find('lat:last').text(),
			$(twinXML).find('bounds').find('long:last').text()
		)
	);
	
	// REMOVE CURRENT BOUNDS LISTENER 
	google.maps.event.removeListener(twinOutOfBoundsListener);
	
	for (marker in LPTwinPois) {
		LPTwinPois[marker].setMap(null);
	}
	
	LPTwinPois = [];
	
	// MOVE THE MAP CENTER TO NEW TWIN
	twinMap.panTo(currentTwinCenter);
	// CHANGE THE RELATIONAL MAP IMAGE
	newTwinMapImage = $(twinXML).find('maps mapimg').text();
	twinRMC.setImage(newTwinMapImage);
	
	loadCount = loadCount + 2;
	initializeLPMarkers();
	
	// ADD NEW LISTENER FOR NEW TWIN BOUNDS
	twinOutOfBoundsListener = google.maps.event.addListener(twinMap, 'dragend', function() {
		if (currentTwinBounds.contains(twinMap.getCenter())) return;
	
		twinMap.panTo(currentTwinCenter);	
	});
	
	// DISABLE TOWN/TWIN SELECT BOXES
	$("#town-chooser").attr('disabled', false);
	$("#twin-chooser").attr('disabled', false);
	
}

/* panNewTown
		changes the townMap to the newly selected town and changed the twinMap to show
		the towns first twin
*/
function panNewTown() {

	// DISABLES THE TOWN/TWIN SELECT BOXES
	$("#town-chooser").attr('disabled', true);
	$("#twin-chooser").attr('disabled', true);
	
	// SHOWS THE LOADING IMAGE
	spinnerImage = $("#maps-spinner img");
	spinnerP = $("#maps-spinner p");
	outerDiv = $("#ui-tabs-2");
	
	$("#maps-spinner").show();
	
	// GETS THE NEW TOWN FROM THE TOWN SELECT BOX
	newTown = $('#town-chooser option:selected').val();
	
	// GETS THE NEW TOWN XML SELECTION
	townXML = $(configXML).find("town[type='main']:has(name):contains('" + newTown +"')");
	
	// GETS THE NEW TOWN LATITIUDE AND LONGITUDE
	townLat = $(townXML).find("location lat").text();
	townLong = $(townXML).find("location long").text();
	
	// CREATES A NEW GOOGLE LATLNG OBJECT FOR THE NEW TOWN
	currentTownCenter = new google.maps.LatLng(townLat, townLong);
	
	// CREATES BOUNDS FOR THE NEW TOWN
	currentTownBounds = new google.maps.LatLngBounds(
		new google.maps.LatLng(
			$(townXML).find('bounds').find('lat:first').text(),
			$(townXML).find('bounds').find('long:first').text()
		),
		new google.maps.LatLng(
			$(townXML).find('bounds').find('lat:last').text(),
			$(townXML).find('bounds').find('long:last').text()
		)
	);
	
	// FINDS THE FIRST TWIN NAME FROM THE NEW TOWNS LIST OF TWINS
	newTwin = $(configXML).find("town[type='main']:has(name):contains('" + newTown + "') twins twin:first").text();
	
	// GETS THE TWIN XML SELECTION
	twinXML = $(configXML).find("town[type='twin']:has(name):contains('"  + newTwin +"')");
	
	// GETS THE TWIN LATITUDE AND LONGITUDE
	twinLat = $(twinXML).find("location lat").text();
	twinLong = $(twinXML).find("location long").text();
	
	// CREATES A NEW GOOGLE LATLNG OBJECT FOR THE NEW TWIN
	currentTwinCenter = new google.maps.LatLng(twinLat, twinLong);
	
	// CREATES BOUNDS FOR THE NEW TWIN
	currentTwinBounds = new google.maps.LatLngBounds(
		new google.maps.LatLng(
			$(twinXML).find('bounds').find('lat:first').text(),
			$(twinXML).find('bounds').find('long:first').text()
		),
		new google.maps.LatLng(
			$(twinXML).find('bounds').find('lat:last').text(),
			$(twinXML).find('bounds').find('long:last').text()
		)
	);
	
	// REMOVES THE OUT OF BOUNDS LISTENERS FOR THE OLD TOWNS
	google.maps.event.removeListener(townOutOfBoundsListener);
	google.maps.event.removeListener(twinOutOfBoundsListener);
	
	// TAKES ALL THE OLD MARKERS OFF THE MAP
	for (marker in LPTownPois) {
		LPTownPois[marker].setMap(null);
	}
	for (marker in LPTwinPois) {
		LPTwinPois[marker].setMap(null);
	}
	
	// CLEARS THE MARKER COLLECTIONS
	LPTownPois = [];
	LPTwinPois = [];
	
	// MOVES THE MAPS TO THE NEW TOWNS
	townMap.panTo(currentTownCenter);
	twinMap.panTo(currentTwinCenter);
	
	// CHANGES THE RELATIONAL MAP IMAGES FOR BOTH TOWNS
	newTownMapImage = $(townXML).find('maps mapimg').text();
	townRMC.setImage(newTownMapImage);
	
	newTwinMapImage = $(twinXML).find('maps mapimg').text();
	twinRMC.setImage(newTwinMapImage);
	
	// CALLS initializeLPMarkers TO LOADS THE NEW MARKERS
	initializeLPMarkers();
	
	// CREATES NEW OUT OF BOUNDS LISTENERS
	townOutOfBoundsListener = google.maps.event.addListener(townMap, 'dragend', function() {
		if (currentTownBounds.contains(townMap.getCenter())) return;
	
		townMap.panTo(currentTownCenter);	
	});
	
	twinOutOfBoundsListener = google.maps.event.addListener(twinMap, 'dragend', function() {
		if (currentTwinBounds.contains(twinMap.getCenter())) return;
	
		twinMap.panTo(currentTwinCenter);	
	});
	
	// ENABLES THE TOWN/TWIN SELECT BOXES
	$("#town-chooser").attr('disabled', false);
	$("#twin-chooser").attr('disabled', false);
	
}