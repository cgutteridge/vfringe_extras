<?php

function vfringe_extras_block_view_itinerary() {
  $itinerary = vfringe_extras_get_itinerary();
  $size = count(($itinerary["codes"]));
  $style = "";
  if( $size == 0 ) {
    $style = "display:none";
    $it_count = "";
  } elseif( $size == 1 ) {
    $it_count = "1 event in your itinerary.";
  } else {
    $it_count = "$size events in your itinerary.";
  }
  
  $block = array();    
  $block['subject'] = t('');
  $block['content'] = "<div class='vf_itinerary_display' style='$style'><div class='vf_itinerary_count'>$it_count</div><a href='/vfringe-itinerary' class='view_itinerary vf_itinerary_button'>View itinerary</a></div>";
  return $block;
}


function vfringe_extras_get_itinerary() {
  $itinerary = &drupal_static(__FUNCTION__);
  if( !isset( $itinerary ) ) {
    $itinerary = array();
    // get itinerary from cache
    if( @$_COOKIE["itinerary"] ) {
      $itinerary["codes"] = preg_split( '/,/', $_COOKIE["itinerary"] );
    } else {
      $itinerary["codes"] = array();
    }
    // load events
    // code is just Id for now, but could include start time later...
    $events = entity_load('node',$itinerary["codes"]);
    $itinerary["events"] = array();
    foreach( $events as $event ) {
      $itinerary["events"][$event->nid] = $event;
    }
  }
  return $itinerary;
}
  
function vfringe_extras_serve_itinerary() {
  $itinerary = vfringe_extras_get_itinerary();
  $venues= vfringe_extras_load_venues();

  $h = array();
  $list = array();
  $script = array();

  if( count($itinerary['codes']) ) {
    $h[]= "<p style='display:none' ";
  } else {
    $h[]= "<p ";
  }
  $h []= "class='vf_itinerary_none'>No items in your itinerary. Browse the website and add some.</p>";

  $h []="<table class='vf_itinerary_table'>";

  $h []="<tr>";
  $h []="<th>Date</th>";
  $h []="<th>Start</th>";
  $h []="<th>End</th>";
  $h []="<th>Event</th>";
  $h []="<th>Venue</th>";
  $h []="<th>Actions</th>";
  $h []="</tr>";

  foreach( $itinerary['codes'] as $code ) {
    $event = @$itinerary['events'][$code];
    if( !$event ) {
      $time_t = 0;
    } else {
      $time_t = strtotime($event->field_date['und'][0]['value']." UTC");
    }
    if( @!is_array( $list[$time_t] ) ) { $list[$time_t][]=$code; }
  }  
  ksort( $list );
  global $vf_js_id;
  foreach( $list as $start_time=>$codes ) {
    foreach( $codes as $code ) {
      ++$vf_js_id;
      $event = @$itinerary['events'][$code];
      $h []= "<tr id='${vf_js_id}_row'>";    
      if( $event ) {
        $h []= "<td>".date("l jS F",$start_time)."</td>";
        $h []= "<td>".date("H:m",$start_time)."</td>";
        if( @$event->field_date['und'][0]['value2'] ) {
          $end_t = strtotime($event->field_date['und'][0]['value2']." UTC");
          $h []= "<td>".date("H:m",$end_t)."</td>";
        } else { 
          $h []= "<td></td>";
        }

        $h []= "<td><a href='".url('node/'. $event->nid)."'>".$event->title."</a></td>";
        $venue = $venues[$event->field_venue['und'][0]['tid']];
        $h []= "<td><a href='".url('taxonomy/term/'. $venue->tid)."'>".$venue->name."</a></td>";
  
      } else {
        $h []= "<td></td>";
        $h []= "<td></td>";
        $h []= "<td></td>";
        $h []= "<td></td>";
        $h []= "<td>Error, event missing (may have been erased or altered. Sorry.)</td>";
      }
      $h []= "<td><div class='vf_itinerary_button vf_itinerary_remove_button' id='${vf_js_id}_remove'>Remove from itinerary</div>";
      $h []= "</tr>";
      $script []= "jQuery( '#${vf_js_id}_remove' ).click(function(){ jQuery( '#${vf_js_id}_row' ).hide(); vfItineraryRemove( '".$code."' ) });\n";
    }
  }
  $h []= "</table>";

  $h []= "<script>jQuery(document).ready(function(){\n".join( "", $script )."});</script>";
  return array( "#markup"=> join( "", $h) );
}

