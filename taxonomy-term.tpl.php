<?php

/**
 * @file
 * Default theme implementation to display a term.
 *
 * Available variables:
 * - $name: (deprecated) The unsanitized name of the term. Use $term_name
 *   instead.
 * - $content: An array of items for the content of the term (fields and
 *   description). Use render($content) to print them all, or print a subset
 *   such as render($content['field_example']). Use
 *   hide($content['field_example']) to temporarily suppress the printing of a
 *   given element.
 * - $term_url: Direct URL of the current term.
 * - $term_name: Name of the current term.
 * - $classes: String of classes that can be used to style contextually through
 *   CSS. It can be manipulated through the variable $classes_array from
 *   preprocess functions. The default values can be one or more of the following:
 *   - taxonomy-term: The current template type, i.e., "theming hook".
 *   - vocabulary-[vocabulary-name]: The vocabulary to which the term belongs to.
 *     For example, if the term is a "Tag" it would result in "vocabulary-tag".
 *
 * Other variables:
 * - $term: Full term object. Contains data that may not be safe.
 * - $view_mode: View mode, e.g. 'full', 'teaser'...
 * - $page: Flag for the full page state.
 * - $classes_array: Array of html class attribute values. It is flattened
 *   into a string within the variable $classes.
 * - $zebra: Outputs either "even" or "odd". Useful for zebra striping in
 *   teaser listings.
 * - $id: Position of the term. Increments each time it's output.
 * - $is_front: Flags true when presented in the front page.
 * - $logged_in: Flags true when the current user is a logged-in member.
 * - $is_admin: Flags true when the current user is an administrator.
 *
 * @see template_preprocess()
 * @see template_preprocess_taxonomy_term()
 * @see template_process()
 *
 * @ingroup themeable
 */
?>
<div id="taxonomy-term-<?php print $term->tid; ?>" class="<?php print $classes; ?>">

  <?php if (!$page): ?>
    <h2><a href="<?php print $term_url; ?>"><?php print $term_name; ?></a></h2>
  <?php endif; ?>

<!-- render map -->
 <link rel="stylesheet" href="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.css" />
 <script src="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.js"></script>
<?php
function vfringe_taxonomy_term_single_value($term,$field,$default=null) {
  $items = field_get_items( "taxonomy_term", $term, $field );
  $value = trim($items[0]['value']);
  if( $value ) { return $value; }
  return $default;
}
$lat_long = vfringe_taxonomy_term_single_value($term,'field_lat_long');
$icon_url = vfringe_taxonomy_term_single_value($term,'field_icon_url','http://data.southampton.ac.uk/images/numbericon.png?n=X');
$icon_size = vfringe_taxonomy_term_single_value($term,'field_icon_size','32,37');
$icon_anchor = vfringe_taxonomy_term_single_value($term,'field_icon_anchor','16,37');
$map_zoom = vfringe_taxonomy_term_single_value($term,'field_map_zoom',17);

if( $lat_long ) {
   global $mapid;
   $id = "map".(++$mapid); // make sure the js uses a unique ID in case multiple maps on a page
   print "<div id='$mapid' style='height: 300px; width: 300px;'></div>\n";
   print "<script>\n";
?>
(function(mapid,lat_long,map_zoom,icon_url,icon_size,icon_anchor){
  var map = L.map(mapid,{scrollWheelZoom: false}).setView(lat_long, map_zoom);
  L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>', maxZoom: map_zoom }).addTo(map);
  var icon = L.icon( { iconUrl: icon_url, iconSize: icon_size, iconAnchor: icon_anchor } );
  var marker = L.marker(lat_long,{ icon: icon } ).addTo(map);
<?php 
  print "}('$mapid',[$lat_long],$map_zoom,'$icon_url',[$icon_size],[$icon_anchor]));\n";
  print "</script>\n";
}
?>

<!-- end map -->

  <div class="content">
    <?php print render($content); ?>
  </div>



</div>
