
  function vfItineraryAdd(nid) {
    var list = vfGetItinerary();

    list.push( nid ); // code
    vfSetItinerary(list);
    vfUpdateItineraryCount(list.length);
  }

  function vfItineraryRemove(nid) {
    var list = vfGetItinerary();
    var newlist = [];
    for( var i=0; i<list.length; ++i ) {
      if( list[i] != nid ) { newlist.push( list[i] ); }
    }
    vfSetItinerary( newlist );
    vfUpdateItineraryCount(newlist.length);
  }

  function vfUpdateItineraryCount(n) {
    if( n==0 ) {
      jQuery('.itinerary_display').hide();
      jQuery('.itinerary_none').show();
    } else if( n==1 ) {
      jQuery('.itinerary_display').show();
      jQuery('.itinerary_count').text( "1 item in itinerary." );
      jQuery('.itinerary_none').hide();
    } else {
      jQuery('.itinerary_display').show();
      jQuery('.itinerary_count').text( n+" items in itinerary." );
      jQuery('.itinerary_none').hide();
    } 
  }

  function vfSetItinerary(list) {
    var name = 'itinerary';
    var value = list.join( "," );
    var days = 100; 
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "; expires=" + date.toGMTString();
    } else {
      expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
  }

  function vfGetItinerary() {
    var c_name = 'itinerary';
    if (document.cookie.length > 0) {
      c_start = document.cookie.indexOf(c_name + "=");
      if (c_start != -1) {
        c_start = c_start + c_name.length + 1;
        c_end = document.cookie.indexOf(";", c_start);
        if (c_end == -1) {
          c_end = document.cookie.length;
        }
        var v = unescape(document.cookie.substring(c_start, c_end));
        var list = [];
        if( v != "" ) { list = v.split( /,/ ); }
        return list;
      }
    }
    return "";
  }

