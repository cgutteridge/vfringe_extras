<?php

function vfringe_extras_serve_grid() {

  // load venues
  $venues = vfringe_extras_load_venues();

  // load events
  $query = new EntityFieldQuery();
  $entities = $query->entityCondition('entity_type', 'node')
                 ->addTag('efq_debug')
                 ->entityCondition('bundle','event' )
                 ->execute();
  $events = entity_load('node',array_keys($entities['node']));

  // work out timeslots
  $times = array();
  foreach( $events as $event ) {
    $start = $event->field_date["und"][0]["value"]." ".$event->field_date["und"][0]["timezone_db"]; 
    $dates = array( strtotime( $start ));

    # if value2 not set, add an hour to the start date I guess? TODO
    $end = $event->field_date["und"][0]["value2"]." ".$event->field_date["und"][0]["timezone_db"]; 
    $dates[]= strtotime($end);
    # trim to start/end time of window TODO
    $times[$dates[0]] = true;
    $times[$dates[1]] = true;
  }
  ksort($times);
  $times = array_keys( $times );

  $timeslots = array();
  $timemap = array();
  for($i=0;$i<sizeof($times);++$i) {
    if( $i<sizeof($times)-1 ) {
      # the last time isn't a timeslot but it still has an index
      $timeslots []= array( "start"=>$times[$i], "end"=>$times[$i+1] );
    }
    $timemap[ $times[$i] ] = $i;
  }


  // build up grid  
  $grid = array(); # venue=>list of columns for venu
  foreach( $events as $event ) {
    $start = strtotime($event->field_date["und"][0]["value"]." ".$event->field_date["und"][0]["timezone_db"]);
    $end = strtotime($event->field_date["und"][0]["value2"]." ".$event->field_date["und"][0]["timezone_db"]); 
    $start_i = $timemap[$start];
    $end_i = $timemap[$end];
    $venue_id = $event->field_venue['und'][0]['tid'];

    $column_id = null;
    if( !@$grid[$venue_id] ) {
      # no columns. Leave column_id null and init a place to put columns
      $grid[$venue_id] = array();
    } else {
      # find a column with space, if any
      for( $c=0;$c<sizeof($grid[$venue_id]);++$c ) {
        // check all the slots this event needs
        for($p=$start_i;$p<$end_i;++$p ) {
          if( $grid[$venue_id][$c][$p]['used'] ) {
            continue(2); // skip to next column
          }
        }
        // ok looks like this column is clear!
        $column_id = $c;
        break;
      }
    }
    if( $column_id === null ) {
      $col = array();
      for($p=0;$p<sizeof($timeslots);++$p) {
        $col[$p] = array( "used"=>false );
      }
      $grid[$venue_id][] = $col;
      $column_id = sizeof($grid[$venue_id])-1;
    }

    // ok. column_id is now a real column and has space
    // fill out the things as used
    for( $p=$start_i; $p<$end_i; ++$p ) {
      $grid[$venue_id][$column_id][$p]["used"] = true;
    }
    // then put this event in the top one.
    $grid[$venue_id][$column_id][$start_i]["event"] = $event;
    $grid[$venue_id][$column_id][$start_i]["start_i"] = $start_i;
    $grid[$venue_id][$column_id][$start_i]["end_i"] = $end_i;
   
  } // end of events loop

  $path = drupal_get_path('module', 'vfringe_extras');
  print '<link rel="stylesheet" href="'.$path.'/grid.css" />';
  print "<table class='vf_grid'>";

  // venue ids. Could/should sort this later
  $venue_ids = array_keys( $grid );

  // Venue headings
  print "<tr><th></th>";
  foreach( $venue_ids as $venue_id ) {
    $cols = $grid[$venue_id];
    print "<th class='vf_grid_timeslot' colspan='".sizeof( $cols )."'>";
    print $venues[$venue_id]->name;
    print "</th>\n";
  }
  print "</tr>";
  // Venue headings
  
  foreach( $timeslots as $p=>$slot ) { 
    print "<tr>";
    print "<th class='vf_grid_venue'>".date("H:i",$slot["start"])."</th>";
    foreach( $venue_ids as $venue_id ) {
      foreach( $grid[$venue_id] as $col ) {
        $cell = $col[$p];
        if( @$cell['event'] ) {
          $height = $cell['end_i'] - $cell['start_i'];
          print "<td class='vf_grid_event' rowspan='$height'>";
          print $cell['event']->title;
          print "</td>";
        } else if( $cell["used"] ) {
          print "";
        } else {
          print "<td class='vf_grid_freecell'></td>";
        }
      }
    }
    print "</tr>";
  }

  print "</table>";
}

