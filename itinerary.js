  function vfItineraryAdd(nid) {
    var list = vfGetCookie( 'itinerary' ).split( /,/ ); 
    list.push( nid ); // code
    vfSetCookie('itinerary',list.join( ',' ));
    vfUpdateItineraryCount(list.length);
  }

  function vfItineraryRemove(nid) {
    var list = vfGetCookie( 'itinerary' ).split( /,/ ); 
    var newlist = [];
    for( var i=0; i<list.length; ++i ) {
      if( list[i] != nid ) { newlist.push( list[i] ); }
    }
    vfSetCookie('itinerary',newlist.join( ',' ));
    vfUpdateItineraryCount(newlist.length);
  }

  function vfUpdateItineraryCount(n) {
    if( n==0 ) {
      jQuery('.itinerary_display').hide();
    } else if( n==1 ) {
      jQuery('.itinerary_display').show();
      jQuery('.itinerary_count').text( "1 item in itinerary." );
    } else {
      jQuery('.itinerary_display').show();
      jQuery('.itinerary_count').text( n+" items in itinerary." );
    } 
  }

  function vfSetCookie(name, value, days=100) {
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "; expires=" + date.toGMTString();
    } else {
      expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
  }

  function vfGetCookie(c_name) {
    if (document.cookie.length > 0) {
      c_start = document.cookie.indexOf(c_name + "=");
      if (c_start != -1) {
        c_start = c_start + c_name.length + 1;
        c_end = document.cookie.indexOf(";", c_start);
        if (c_end == -1) {
          c_end = document.cookie.length;
        }
        return unescape(document.cookie.substring(c_start, c_end));
      }
    }
    return "";
  }

