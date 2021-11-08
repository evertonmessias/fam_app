function initMap () {
	var $ = jQuery;

	var coords = function (str) {
		var coord = str.trim().split(',');
		var lat = parseFloat(coord[0].trim());
		var lng = parseFloat(coord[1].trim());

		return { lat: lat, lon: lng };
	}

	$('.google-map').each(function () {
		var el = $(this);
		var coord = coords(el.data('location'));
		var zoom = el.data('zoom') || 17;
		var pos = { lat: coord.lat, lng: coord.lon };
	    var map = new google.maps.Map(this, {
	      center: pos,
	      scrollwheel: false,
	      zoom: zoom
	    });

	    var marker = el.data('marker');
	    var markerPos = false;
	    if (marker == true) {
	    	// Mesma da posição
	    	markerPos = pos;
	    } else if (marker.length > 0) {
	    	// Coords
	    	markerPos = coords(marker);
	    	markerPos = { lat: markerPos.lat, lng: markerPos.lng };
	    }

	    if (markerPos) {
		    var title = el.data('title') || '';

			var marker = new google.maps.Marker({
				position: markerPos,
				map: map,
				title: title
			});
		}
	});
}