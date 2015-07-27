var initial_params = null;

var map=null;
var infowindow=null;

var old_temp_unavail_value=null;
var old_arch_value=null;
var refresh_rand="r0";

// Draw circle with radius 150 m to show contain existing geocaches 
function okrag(srodek,promien)
{
	if(!srodek || !promien)
		return;

	// default
	var wyp_kolor = '#0000ff';
	var wyp_alfa = 0.25;
	var obr_kolor = '#0000ff';
	var obr_grubosc = 1;
	var obr_alfa = 0.65;
	var dokladnosc = 24;
	
	switch(arguments.length)
	{
		case 8: dokladnosc = arguments[7];
		case 7: wyp_alfa = arguments[6];
		case 6: wyp_kolor = arguments[5];
		case 5: obr_alfa = arguments[4];
		case 4: obr_grubosc = arguments[3];
		case 3: obr_kolor = arguments[2];
	}

	return new google.maps.Circle({
		center: srodek, radius: promien,
		strokeColor: obr_kolor, strokeWeight: obr_grubosc, strokeOpacity: obr_alfa,
		fillColor: wyp_kolor, fillOpacity: wyp_alfa,
		clickable: false
	});
}

function ShowCoordsControl(map) {
	var container = document.createElement("div");
	var showCoords = document.createElement("div");

	var icon = document.createElement("img");
	icon.src = "tpl/stdstyle/images/blue/compas20.png";
	icon.alt = "";
	icon.style.marginTop = "-2px";

	this.type = 1;

	this.showCoords = showCoords;

	this.setStyle_(showCoords);
	container.appendChild(showCoords);
	showCoords.appendChild(icon);
	var textNode = document.createTextNode("");
	showCoords.appendChild(textNode);
	showCoords.owner = this;

	map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(container);
	
	google.maps.event.addDomListener(showCoords, "click", function() {
		this.owner.type = ((this.owner.type + 1) % 3);
		this.owner.setCoords(this.owner.lastLatLng);
	});

	this.setCoords(map.getCenter());

	return this;
}

function toWGS84(type, latlng)
{
	var lat = latlng.lat(), lng = latlng.lng();
	var latD = 'N', lngD = 'E';

	if(lat < 0) {
		lat = -lat;
		latD = 'S';
	}
	if(lng < 0) {
		lng = -lng;
		lngD = 'W';
	}

	var latstr, lngstr;

	if(type == 0) {
		latstr = lat.toFixed(5) + "°";
		lngstr = lng.toFixed(5) + "°";
	}
	else if(type == 1) {
		var degs1 = lat | 0;
		var degs2 = lng | 0;
		var minutes1 = ((lat - degs1)*60);
		var minutes2 = ((lng - degs2)*60);
		latstr = degs1 + "° " + minutes1.toFixed(3) + "'";
		lngstr = degs2 + "° " + minutes2.toFixed(3) + "'";
	}
	else if(type == 2) {
		var degs1 = lat | 0;
		var degs2 = lng | 0;
		var minutes1 = ((lat - degs1)*60);
		var minutes2 = ((lng - degs2)*60);
		var seconds1 = (minutes1 - (minutes1 | 0))*60;
		var seconds2 = (minutes2 - (minutes2 | 0))*60;
		latstr = degs1 + "° " + (minutes1 | 0) + "' " + (seconds1.toFixed(2)) + "\"";
		lngstr = degs2 + "° " + (minutes2 | 0) + "' " + (seconds2.toFixed(2)) + "\"";;
	}
	return latD + " " + latstr + " " + lngD + " " + lngstr;
}

ShowCoordsControl.prototype.setCoords = function(latlng) {
	this.lastLatLng = latlng;
	this.showCoords.childNodes[1].data = toWGS84(this.type, latlng);
};

ShowCoordsControl.prototype.setStyle_ = function(elem) {
	elem.style.textDecoration = "none";
	elem.style.color = "#000000";
	elem.style.backgroundColor = "white";
	elem.style.font = "small Arial";
	elem.style.border = "1px solid #717B87";
	elem.style.fontWeight = "bold";
	elem.style.paddingTop = "2px";
	elem.style.width = "225px";
	elem.style.textAlign = "center";
	elem.style.cursor = "pointer";
};

function statusToImageName(status)
{
	switch( status )
	{
		case "2":
			return "-n";
		case "3":
			return "-a";
		case "6":
			return "-d";
		default:
			return "-s";
	}
}

function typeToImageName(type, status)
{
	switch( type )
	{
		case "1":
			return "unknown"+statusToImageName(status)+".png";
		case "2":
		default:
			return "traditional"+statusToImageName(status)+".png";
		case "3":
			return "multi"+statusToImageName(status)+".png";
		case "4":
			return "virtual"+statusToImageName(status)+".png";
		case "5":
			return "webcam"+statusToImageName(status)+".png";
		case "6":
			return "event"+statusToImageName(status)+".png";
		case "7":
			return "quiz"+statusToImageName(status)+".png";
		case "8":
			return "moving"+statusToImageName(status)+".png";
		case "10":
			return "owncache"+statusToImageName(status)+".png";
	}
}

function stripslashes(str) 
{
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\\\/g,'\\');
	str=str.replace(/\\0/g,'\0');
	return str;
}

function check_field()
{
	if( document.getElementById('be_ftf').checked )
	{
		// store previews values of temp_unavail and arch checkboxes
		old_temp_unavail_value = document.getElementById('h_temp_unavail').checked;
		old_arch_value = document.getElementById('h_arch').checked;

		document.getElementById('h_temp_unavail').checked = true;
		document.getElementById('h_arch').checked = true;
		
		document.getElementById('h_temp_unavail').disabled = true;
		document.getElementById('h_arch').disabled = true;
	}
	else
	{
		// restore previews values of temp_unavail and arch checkboxes
		document.getElementById('h_temp_unavail').checked = old_temp_unavail_value;
		document.getElementById('h_arch').checked = old_arch_value;
		
		document.getElementById('h_temp_unavail').disabled = false;
		document.getElementById('h_arch').disabled = false;
	}
}

function getCurrentOCMapId()
{
	switch (map.getMapTypeId()) {
		case google.maps.MapTypeId.ROADMAP:
			return 0;
		case google.maps.MapTypeId.SATELLITE:
			return 1;
		case google.maps.MapTypeId.HYBRID:
			return 2;
		case google.maps.MapTypeId.TERRAIN:
			return 3;
		default:
			return 0;
	}
}

function getMapTypeFromOCMapId(value)
{
	switch (value) {
		case 0:
			return google.maps.MapTypeId.ROADMAP;
		case 1:
			return google.maps.MapTypeId.SATELLITE;
		case 2:
			return google.maps.MapTypeId.HYBRID;
		case 3:
			return google.maps.MapTypeId.TERRAIN;
		default:
			return google.maps.MapTypeId.ROADMAP;
	}
}

function prepareCommonFilterParams()
{
	return ""+
		"&h_u="+document.getElementById('h_u').checked+
		"&h_t="+document.getElementById('h_t').checked+
		"&h_m="+document.getElementById('h_m').checked+
		"&h_v="+document.getElementById('h_v').checked+
		"&h_w="+document.getElementById('h_w').checked+
		"&h_e="+document.getElementById('h_e').checked+
		"&h_q="+document.getElementById('h_q').checked+
		"&h_o="+document.getElementById('h_o').checked+
		"&h_owncache="+document.getElementById('h_owncache').checked+
		"&h_ignored="+document.getElementById('h_ignored').checked+
		"&h_own="+document.getElementById('h_own').checked+
		"&h_found="+document.getElementById('h_found').checked+
		"&h_noattempt="+document.getElementById('h_noattempt').checked+
		"&h_nogeokret="+document.getElementById('h_nogeokret').checked+
		"&h_avail="+document.getElementById('h_avail').checked+
		"&h_temp_unavail="+document.getElementById('h_temp_unavail').checked+
		"&h_arch="+document.getElementById('h_arch').checked+
		"&be_ftf="+document.getElementById('be_ftf').checked+
		"&min_score="+document.getElementById('min_score').value+
		"&max_score="+document.getElementById('max_score').value+
		"&h_noscore="+document.getElementById('h_noscore').checked;
}

function saveMapSettings()
{
	if (initial_params.start.savesettings === false) return;

	var queryString = "?maptype="+getCurrentOCMapId()+
		prepareCommonFilterParams();
		// These settings are currently ignored
		//"&signes="+document.getElementById('signes').checked+
		//"&waypoints="+document.getElementById('waypoints').checked+
		//"&h_pl="+document.getElementById('h_pl').checked+
		//"&h_de="+document.getElementById('h_de').checked+
		//"&h_no="+document.getElementById('h_no').checked+
		//"&h_se="+document.getElementById('h_se').checked;

	jQuery.get("cachemapsettings.php" + queryString);
}		 

function get_current_rand() { return refresh_rand; }

function generate_new_rand() {
	t = new Date();
	refresh_rand = "r" + t.getHours() + ":" + t.getMinutes() + ":" + Math.floor(t.getSeconds() / 10) + "0";
}

function addOCOverlay()
{
	var tilelayer = new google.maps.ImageMapType({
		opacity: 1.0,
		tileSize: new google.maps.Size(256, 256),
		getTileUrl: function(tile, zoom) {
			return initial_params.start.cachemap_mapper+"?userid="+initial_params.start.userid+
				"&z="+zoom+"&x="+tile.x+"&y="+tile.y+
				prepareCommonFilterParams()+
				"&rand="+get_current_rand()+
				"&"+initial_params.start.searchdata;
		}
	});
	map.overlayMapTypes.insertAt(0, tilelayer); 
}

function reload()
{
	map.overlayMapTypes.removeAt(0);
	addOCOverlay();
	saveMapSettings();
}

function prepareLibXmlMapUrl(clickBounds)
{
	var p1 = clickBounds.getSouthWest();
	var p2 = clickBounds.getNorthEast();
	return "lib/xmlmap.php"+
		"?latmin="+p1.lat()+"&lonmin="+p1.lng()+"&latmax="+p2.lat()+"&lonmax="+p2.lng()+
		"&userid="+initial_params.start.userid+
		prepareCommonFilterParams()+
		"&"+initial_params.start.searchdata;
}

function WMSImageMapTypeOptions(wmsName, wmsURL, wmsLayers, wmsStyles, wmsFormat, wmsVersion, wmsBgColor)
{
	var myBaseURL = wmsURL;
	var myLayers = wmsLayers;
	var myStyles = (wmsStyles ? wmsStyles : "");
	var myFormat = (wmsFormat ? wmsFormat : "image/gif");
	var myVersion = (wmsVersion ? wmsVersion : "1.1.1");
	var myBgColor = (wmsBgColor ? wmsBgColor : "0xFFFFFF");

	this.tileSize = new google.maps.Size(256, 256);
	this.name = wmsName;
	this.maxZoom = 18;

	this.getTileUrl = function(point, zoom) {
		var proj = map.getProjection();
	    var zfactor = Math.pow(2, zoom);
		var lULP = new google.maps.Point(point.x * 256 / zfactor, (point.y + 1) * 256 / zfactor);
		var lLRP = new google.maps.Point((point.x + 1) * 256 / zfactor, point.y * 256 / zfactor);
		var lUL = proj.fromPointToLatLng(lULP);
		var lLR = proj.fromPointToLatLng(lLRP);
	    var lBbox = lUL.lng() + "," + lUL.lat() + "," + lLR.lng() + "," + lLR.lat();
	    var lSRS = "EPSG:4326";
		var lURL = myBaseURL;
		lURL += "?REQUEST=GetMap";
		lURL += "&SERVICE=WMS";
		lURL += "&VERSION=" + myVersion;
		lURL += "&LAYERS=" + myLayers;
		lURL += "&STYLES=" + myStyles;
		lURL += "&FORMAT=" + myFormat;
		lURL += "&BGCOLOR=" + myBgColor;
		lURL += "&TRANSPARENT=TRUE";
		lURL += "&SRS=" + lSRS;
		lURL += "&BBOX=" + lBbox;
		lURL += "&WIDTH=256";
		lURL += "&HEIGHT=256";
		lURL += "&reaspect=false";
		return lURL;
	};
}

function load(additionalControls, searchElement)
{
	var ocMapTypeIds = [];
	for (var type in google.maps.MapTypeId) {
		ocMapTypeIds.push(google.maps.MapTypeId[type]);
	}
	ocMapTypeIds.push("OSMapa");
	if (initial_params.start.moremaptypes) {
		ocMapTypeIds.push("OSM");
	}
	ocMapTypeIds.push("UMP");
	if (initial_params.start.moremaptypes) {
		ocMapTypeIds.push("Topo");
		ocMapTypeIds.push("Orto");
	}

	map = new google.maps.Map(
		document.getElementById("map_canvas"),
		{
			center: new google.maps.LatLng(initial_params.start.coords[0], initial_params.start.coords[1]),
			zoom: initial_params.start.zoom,
			mapTypeId: getMapTypeFromOCMapId(initial_params.start.map_type),
			mapTypeControlOptions: {
				mapTypeIds: ocMapTypeIds
			},
			scaleControl: true,
			draggableCursor: 'crosshair',
			draggingCursor: 'pointer',
			overviewMapControl: initial_params.start.largemap
		}
	);

	if (initial_params.start.largemap === false) {
		// Disable some controls on a small map to save screen space
		map.setOptions({panControl: false, mapTypeControlOptions: { mapTypeIds: ocMapTypeIds, style: google.maps.MapTypeControlStyle.DROPDOWN_MENU } });
	}

	addOCOverlay();

    var osmapaMapType = new google.maps.ImageMapType({
		getTileUrl: function (point, zoom) {
			return "http://tile.openstreetmap.pl/"  + zoom + "/" + point.x + "/" + point.y + ".png";
		},
		tileSize: new google.maps.Size(256, 256),
		name: "OSMapa",
		maxZoom: 18
	});
    map.mapTypes.set("OSMapa", osmapaMapType);

	if (initial_params.start.moremaptypes) {
	    var osmMapType = new google.maps.ImageMapType({
			getTileUrl: function (point, zoom) {
				return "http://tile.openstreetmap.org/" + zoom + "/" + point.x + "/" + point.y + ".png";
			},
			tileSize: new google.maps.Size(256, 256),
			name: "OSM",
			maxZoom: 18
		});
	    map.mapTypes.set("OSM", osmMapType);
	}

    var umpMapType = new google.maps.ImageMapType({
		getTileUrl: function (point, zoom) {
			return "http://tiles.ump.waw.pl/ump_tiles/" + zoom + "/" + point.x + "/" + point.y + ".png";
		},
		tileSize: new google.maps.Size(256, 256),
		name: "UMP",
		maxZoom: 18
	});
	map.mapTypes.set("UMP", umpMapType);

	if (initial_params.start.moremaptypes) {
		var topoMapType = new google.maps.ImageMapType(new WMSImageMapTypeOptions(
			"Topo",
			"http://sdi.geoportal.gov.pl/wms_topo/wmservice.aspx",
			"TOPO_50_65,TOPO_50_42,TOPO_25_65,TOPO_100_80,TOPO_10_92,TOPO_10_65,TOPO_10_42",
			"",
			"image/jpeg"));
		map.mapTypes.set("Topo", topoMapType);

		var ortoMapType = new google.maps.ImageMapType(new WMSImageMapTypeOptions(
			"Orto",
			"http://sdi.geoportal.gov.pl/wms_orto/wmservice.aspx",
			"ORTOFOTO",
			"",
			"image/jpeg"));
		map.mapTypes.set("Orto", ortoMapType);
	}

	var attributionDiv = document.createElement('div');
	attributionDiv.id = "map-copyright";
	attributionDiv.style.fontSize = "10px";
	attributionDiv.style.fontFamily = "Arial, sans-serif";
	attributionDiv.style.padding = "3px 6px";
	attributionDiv.style.whiteSpace = "nowrap";
	attributionDiv.style.opacity = "0.7";
	attributionDiv.style.background = "#fff";
	map.controls[google.maps.ControlPosition.BOTTOM_RIGHT].push(attributionDiv);

	google.maps.event.addListener(map, "maptypeid_changed", function() {
		var newMapTypeId = map.getMapTypeId();
		if (newMapTypeId === "OSMapa") {
			attributionDiv.innerHTML = '&copy; <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC BY-SA</a> | Hosting:<a href="http://trail.pl/" target="_blank">trail.pl</a> i <a href="http://centuria.pl/" target="_blank">centuria.pl</a>';
		}
		else if (newMapTypeId === "OSM") {
			attributionDiv.innerHTML = '&copy; <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC BY-SA</a>';
		}
		else if (newMapTypeId === "UMP") {
			attributionDiv.innerHTML = '&copy; Mapa z <a href="http://ump.waw.pl/" target="_blank">UMP-pcPL</a>';
		}  
		else if ((newMapTypeId === "Topo") || (newMapTypeId === "Orto")) {
			attributionDiv.innerHTML = '&copy; <a href="http://geoportal.gov.pl/" target="_blank">geoportal.gov.pl</a>';
		}  
		else {
			attributionDiv.innerHTML = '';
		}
		saveMapSettings();
	});

	document.getElementById("zoom").value = map.getZoom();

	google.maps.event.addListener(map, "zoom_changed", function() {
		document.getElementById("zoom").value = map.getZoom();
	});

	if (initial_params.start.largemap) {
		var showCoords = new ShowCoordsControl(map);
		google.maps.event.addListener(map, "mousemove", function(event) {
			showCoords.setCoords(event.latLng);
		});
	}

	if (searchElement) {
		// Create a search control
		var searchControl = new google.search.SearchControl();

		// Add in local search
		var localSearch = new google.search.LocalSearch();
		var options = new google.search.SearcherOptions();
		options.setExpandMode(GSearchControl.EXPAND_MODE_OPEN);
		searchControl.addSearcher(localSearch, options);

		localSearch.setCenterPoint(map.getCenter());

		// Tell the searcher to draw itself and tell it where to attach
		searchControl.draw(searchElement);

		searchControl.setSearchCompleteCallback(this, function(sc, searcher) {
			if(searcher.results.length < 1)
				return;
			var result = searcher.results[0];
			var p = new google.maps.LatLng(parseFloat(result.lat), parseFloat(result.lng));
			localSearch.setCenterPoint(p);
			map.setCenter(p);
			map.setZoom(13);
			searchElement.getElementsByTagName("input")[0].value = "";
		});

		google.maps.event.addListener(map, "idle", function() {
			localSearch.setCenterPoint(map.getCenter());
		});
	}

	if (initial_params.start.circle == 1)  
	{
		// draw circle with radius 150 m to check existing geocaches 
		var punktCentralny = new google.maps.LatLng(initial_params.start.coords[0], initial_params.start.coords[1]);
		var poli = okrag(punktCentralny,150,'#0000FF',2,0.5,'#9999CC',0.2,55);
		poli.setMap(map);
		var new_cache = new google.maps.Marker({position: punktCentralny, map: map});
	}

	// This is only necessary to do pixel <-> lat/lng calculations
	var overlay = new google.maps.OverlayView();
	overlay.draw = function() {};
	overlay.setMap(map);

	var calcClickBounds = function(ll) {
		var proj = overlay.getProjection();
		if (typeof proj === "undefined") {
			// Projection is not yet available when map is not fully positioned
			// This may happen when showing the info window on initial page load (doopen is true)
			// Send same bounds as min and max - will be handled by xmlmap.php 
			return new google.maps.LatLngBounds(ll, ll);
		} else {
			var xyCenter = proj.fromLatLngToContainerPixel(ll);
			var xy1 = new google.maps.Point(xyCenter.x - 16, xyCenter.y + 16);
			var xy2 = new google.maps.Point(xyCenter.x + 16, xyCenter.y - 16);
			var ll1 = proj.fromContainerPixelToLatLng(xy1);
			var ll2 = proj.fromContainerPixelToLatLng(xy2);
			return new google.maps.LatLngBounds(ll1, ll2);
		}
	};

	var onClickFunc = function(event) {

		var clickBounds = calcClickBounds(event.latLng);
		var clickRect = new google.maps.Rectangle({bounds: clickBounds, strokeColor: '#080', fillColor: '#9c9', map: map});

		jQuery.get(prepareLibXmlMapUrl(clickBounds), function(data, status, jqxhr) {

			clickRect.setMap(null);

			var xml = jqxhr.responseXML;

			var caches = xml.documentElement.getElementsByTagName("cache");
			var cache_id = caches[0].getAttribute("cache_id");
			var name = stripslashes(caches[0].getAttribute("name"));
			var username = stripslashes(caches[0].getAttribute("username"));
			var wp = caches[0].getAttribute("wp");
			var votes = caches[0].getAttribute("votes");
			var score = caches[0].getAttribute("score");
			var topratings = caches[0].getAttribute("topratings");
			var lat = caches[0].getAttribute("lat");
			var lon = caches[0].getAttribute("lon");
			var type = caches[0].getAttribute("type");
			var size = caches[0].getAttribute("size");
			var status = caches[0].getAttribute("status");
			var founds = caches[0].getAttribute("founds");
			var notfounds = caches[0].getAttribute("notfounds");
			var node = caches[0].getAttribute("node");

			if( cache_id != "" )
			{
				var show_score;
				var show_size;
				var print_topratings;
				if( score != "" && votes > 2)
				{
					show_score = "<br><b>" + initial_params.translation.score_label + ":<\/b> " + score;
				}
				else show_score = "";
				
				if( topratings == 0 )
					print_topratings = "";
				else 
				{
					print_topratings = "<br><b>"+initial_params.translation.recommendations+": <\/b>";
					var gwiazdka = "<img width=\"10\" height=\"10\" src=\"images/rating-star.png\" alt=\""+initial_params.translation.recommendation+"\" />";
					var ii;
					for( ii=0;ii<topratings;ii++)
						print_topratings += gwiazdka;
				}

				var infoWindowContent = "";
				var domain="";
				switch( node )
				{
					case "1":
						domain = "http://www.opencaching.de/";
						break;
					case "2":
						domain = "";
						break;
					case "3":
						domain = "http://www.opencaching.cz/";
						break;
					case "7":
						domain = "http://www.opencaching.se/";
						break;
					case "8":
						domain = "http://www.opencaching.no/";
						break;
					default:
						domain = "";
				}
				
				if( type == 6 ) // event
				{
					found_attended = initial_params.translation.attendends;
					notfound_will_attend = initial_params.translation.will_attend;
					show_size = "";
				}
				else
				{
					found_attended = initial_params.translation.found;
					notfound_will_attend = initial_params.translation.not_found;
					show_size = "<br><b>"+initial_params.translation.size+":<\/b> " + size;
				}

				infoWindowContent += "<table border=\"0\" width=\"350\" height=\"120\" class=\"table\">";
				infoWindowContent += "<tr><td colspan=\"2\" width=\"100%\"><table cellspacing=\"0\" width=\"100%\"><tr><td width=\"90%\">";
				infoWindowContent += "<center><img align=\"left\" width=\"20\" height=\"20\" src=\"tpl/stdstyle/images/cache/"+typeToImageName(type, status)+"\" /><\/center>";
				infoWindowContent += "&nbsp;<a href=\""+domain+"viewcache.php?cacheid=" + cache_id + "\" target=\"_blank\">" + name + "<\/a>";
				infoWindowContent += "<\/td><td width=\"10%\">";
				infoWindowContent += "<b>"+wp+"<\/b><\/td><\/tr><\/table>";
				infoWindowContent += "<\/td><\/tr>";
				infoWindowContent += "<tr><td width=\"70%\" valign=\"top\">";
				infoWindowContent += "<b>"+initial_params.translation.created_by+"<\/b> " + username + show_size + show_score + print_topratings;
				
				infoWindowContent += "<\/td>";
				infoWindowContent += "<td valign=\"top\" width=\"30%\"><table cellspacing=\"0\" cellpadding=\"0\" class=\"table\"><tr><td width=\"100%\">";
				infoWindowContent += "<nobr><img src=\"tpl/stdstyle/images/log/16x16-found.png\" border=\"0\" width=\"10\" height=\"10\" /> "+founds+" x "+found_attended+"<\/nobr><\/td><\/tr>";
				infoWindowContent += "<tr><td width=\"100%\"><nobr><img src=\"tpl/stdstyle/images/log/16x16-dnf.png\" border=\"0\" width=\"10\" height=\"10\" /> "+notfounds+" x "+notfound_will_attend+"<\/nobr><\/td><\/tr>";
				if( node == 2 )
					infoWindowContent += "<tr><td width=\"100%\"><nobr><img src=\"tpl/stdstyle/images/action/16x16-adddesc.png\" border=\"0\" width=\"10\" height=\"10\" /> "+votes+" x "+initial_params.translation.scored+"<\/nobr>";

				infoWindowContent += "<\/td><\/tr><\/table><\/td><\/tr>";
				infoWindowContent += "<tr><td align=\"left\" width=\"100%\" colspan=\"2\">";
				/*if( node == 2 )
					infoWindowContent += "<font size=\"0\"><a href=\"cachemap3.php?lat="+"\"><?php echo ($yn=='y'?tr('add_to'):tr('remove_from'));?> {{to_print_list}}<\/a><\/font>";*/
				infoWindowContent += "<\/td><\/tr><\/table><\/td><\/tr>";
				infoWindowContent += "<\/table>";

				if (infowindow === null) {
					infowindow = new google.maps.InfoWindow();
				}

				infowindow.setContent(infoWindowContent);
				infowindow.setPosition(new google.maps.LatLng(lat,lon));

				infowindow.open(map);
			}
			else
			{
				if (infowindow) { infowindow.close(); }
			}
		});
	};

	google.maps.event.addListener(map, 'click', onClickFunc);

	var onRightClickFunc = function(event) 
	{
		var clickBounds = calcClickBounds(event.latLng);
		var clickRect = new google.maps.Rectangle({bounds: clickBounds, strokeColor: '#008', fillColor: '#99c', map: map});

		jQuery.get(prepareLibXmlMapUrl(clickBounds), function(data, status, jqxhr) {
			clickRect.setMap(null);
			var caches = jqxhr.responseXML.documentElement.getElementsByTagName("cache");
			var cache_id = caches[0].getAttribute("cache_id");
			if(cache_id != "")
				window.open("viewcache.php?cacheid="+cache_id, "_blank");
		});
	};

	google.maps.event.addListener(map, 'rightclick', onRightClickFunc);

	if(initial_params.start.doopen)
		onClickFunc({ latLng: new google.maps.LatLng(initial_params.start.coords[0], initial_params.start.coords[1]) });

	if(initial_params.start.fromlat != initial_params.start.tolat) {
		var area = new google.maps.LatLngBounds();
		area.extend(new google.maps.LatLng(initial_params.start.fromlat, initial_params.start.fromlon));
		area.extend(new google.maps.LatLng(initial_params.start.tolat,   initial_params.start.tolon));
		map.fitBounds(area);
	}

	for (var i = 0; i < additionalControls.length; i++) {
		var ac = additionalControls[i];
		map.controls[ac.position].push(ac.control);
	}

	if (initial_params.start.fullscreen && $.browser.msie && (parseInt($.browser.version, 10) < 8)) {
		// Dirty hack for IE7 in full-screen mode only. For unknown reason map does not initially fill entire window.
		// Triggering the 'resize' event fixes this problem.
		setTimeout(function() {
			google.maps.event.trigger(map, 'resize');
			map.setCenter(new google.maps.LatLng(initial_params.start.coords[0], initial_params.start.coords[1]));
		}, 1000);
	}
}

function fullscreen() {
	window.location = "cachemap-full.php"+
		"?lat="+map.getCenter().lat()+
		"&lon="+map.getCenter().lng()+
		"&inputZoom="+map.getZoom()+
		"&"+initial_params.start.searchdata+initial_params.start.boundsurl+initial_params.start.extrauserid;
}
