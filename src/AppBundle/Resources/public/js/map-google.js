var map;
var marker;
var pinColor = "FE7569";
var latitude = parseFloat(document.getElementById('mapGoogle').getAttribute('data-latitude'));
var longitude = parseFloat(document.getElementById('mapGoogle').getAttribute('data-longitude'));
function initMap() {
    map = new google.maps.Map(document.getElementById('mapGoogle'), {
        center: {lat: latitude, lng: longitude},
        zoom: 12
    });
    marker = new google.maps.Marker({
        position: {lat: latitude, lng: longitude},
        map: map,
        animation: google.maps.Animation.DROP, // drops marker in from top
        icon: {
            url: 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|' + pinColor,
            size: new google.maps.Size(21, 34)
        }
    });
}