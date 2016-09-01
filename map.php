<?php

function vfringe_extras_serve_map() {
  $venues= vfringe_extras_load_venues();
  $pois= vfringe_extras_load_pois();
  $places = array_merge( $venues, $pois);
?>
<html>
<style>
html, body {
  margin: 0;
}
</style>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.0-rc.2/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.0.0-rc.2/dist/leaflet.js"></script>
<?php
  global $mapid;
  $id = "map".(++$mapid); // make sure the js uses a unique ID in case multiple maps on a page
  print "<div id='$id' style='height: 100%; width: 100%;'></div>\n";
  print "<script>\n";
?>
var map;
var bounds = L.latLngBounds([]);
(function(mapid){
  map = L.map(mapid,{scrollWheelZoom: false});
  var icon;
  var marker;
  L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>', maxZoom: 20 }).addTo(map);
<?
  print "}('$id'));\n";


  foreach( $places as $place ) {
    $lat_long = vfringe_extras_taxonomy_term_single_value($place,'field_lat_long');
    $icon_url = vfringe_extras_taxonomy_term_single_value($place,'field_icon_url','http://data.southampton.ac.uk/images/numbericon.png?n=X');
    $icon_size = vfringe_extras_taxonomy_term_single_value($place,'field_icon_size','32,37');
    $icon_anchor = vfringe_extras_taxonomy_term_single_value($place,'field_icon_anchor','16,37');

    if( !$lat_long ) { continue; }
?>
  (function(lat_long,icon_url,icon_size,icon_anchor){
    icon = L.icon( { iconUrl: icon_url, iconSize: icon_size, iconAnchor: icon_anchor } );
    marker = L.marker(lat_long,{ icon: icon } ).addTo(map);
    bounds.extend( lat_long );
<?php 
    print "}([$lat_long],'$icon_url',[$icon_size],[$icon_anchor]));\n";
  }

  print "map.fitBounds( bounds );\n";
  print "</script>\n";
  print "</html>\n";
}

// eat a sea horse
