/*
 original jQuery plugin by http://www.pengoworks.com/workshop/jquery/autocomplete.htm
 just replaced $ with jQuery in order to be complaint with other JavaScript libraries.
 */

jQuery.autocomplete_gd = function(input, options) {
    // Create a link to self
    var me = this;

    // Create jQuery object for input element
    var $input = jQuery(input).attr("autocomplete", "off");

    // Apply inputClass if necessary
    if (options.inputClass) $input.addClass(options.inputClass);

    // Create results
    var results = document.createElement("div");
    // Create jQuery object for results
    var $results = jQuery(results);
    $results.show().addClass(options.resultsClass).css("position", "absolute");
    if( options.width > 0 ) $results.css("width", options.width);

    // Add to body element
    jQuery("body").append(results);

    input.autocompleter = me;

    var timeout = null;
    var prev = "";
    var active = -1;
    var cache = {};
    var keyb = false;
    var hasFocus = false;
    var lastKeyPressCode = null;

    // flush cache
    function flushCache(){
        cache = {};
        cache.data = {};
        cache.length = 0;
    }

    // flush cache
    flushCache();

    // if there is a data array supplied
    if( options.data != null ){
        var sFirstChar = "", stMatchSets = {}, row = [];

        // no url was specified, we need to adjust the cache length to make sure it fits the local data store
        if( typeof options.url != "string" ) options.cacheLength = 1;

        // loop through the array and create a lookup structure
        for( var i=0; i < options.data.length; i++ ){
            // if row is a string, make an array otherwise just reference the array
            row = ((typeof options.data[i] == "string") ? [options.data[i]] : options.data[i]);

            // if the length is zero, don't add to list
            if( row[0].length > 0 ){
                // get the first character
                sFirstChar = row[0].substring(0, 1).toLowerCase();
                // if no lookup array for this character exists, look it up now
                if( !stMatchSets[sFirstChar] ) stMatchSets[sFirstChar] = [];
                // if the match is a string
                stMatchSets[sFirstChar].push(row);
            }
        }

        // add the data items to the cache
        for( var k in stMatchSets ){
            // increase the cache size
            options.cacheLength++;
            // add to the cache
            addToCache(k, stMatchSets[k]);
        }
    }

    $input
        .keydown(function(e) {
            // track last key pressed
            lastKeyPressCode = e.keyCode;
            switch(e.keyCode) {
                case 38: // up
                    e.preventDefault();
                    moveSelect(-1);
                    break;
                case 40: // down
                    e.preventDefault();
                    moveSelect(1);
                    break;
                case 9:  // tab
                case 13: // return
                    if( selectCurrent() ){
                        // make sure to blur off the current field
                        $input.get(0).blur();
                        e.preventDefault();
                    }
                    break;
                default:
                    active = -1;
                    if (timeout) clearTimeout(timeout);
                    timeout = setTimeout(function(){onChange();}, options.delay);
                    break;
            }
        })
        .focus(function(){
            // track whether the field has focus, we shouldn't process any results if the field no longer has focus
            hasFocus = true;
        })
        .blur(function() {
            // track whether the field has focus
            hasFocus = false;
            hideResults();
        });

    hideResultsNow();

    function onChange() {
        // ignore if the following keys are pressed: [del] [shift] [capslock]
        if( lastKeyPressCode == 46 || (lastKeyPressCode > 8 && lastKeyPressCode < 32) ) return $results.show();
        var v = $input.val();
        if (v == prev) return;
        prev = v;
        if (v.length >= options.minChars) {
            $input.addClass(options.loadingClass);
            requestData(v);
        } else {
            $input.removeClass(options.loadingClass);
            $results.show();
        }
    }

    function moveSelect(step) {

        var lis = jQuery("li", results);
        if (!lis) return;

        active += step;

        if (active < 0) {
            active = 0;
        } else if (active >= lis.size()) {
            active = lis.size() - 1;
        }

        lis.removeClass("ac_over");

        jQuery(lis[active]).addClass("ac_over");

        // Weird behaviour in IE
        // if (lis[active] && lis[active].scrollIntoView) {
        // 	lis[active].scrollIntoView(false);
        // }

    }

    function selectCurrent() {
        var li = jQuery("li.ac_over", results)[0];
        if (!li) {
            var $li = jQuery("li", results);
            if (options.selectOnly) {
                if ($li.length == 1) li = $li[0];
            } else if (options.selectFirst) {
                li = $li[0];
            }
        }
        if (li) {
            selectItem(li);
            return true;
        } else {
            return false;
        }
    }

    function selectItem(li) {
        if (!li) {
            li = document.createElement("li");
            li.extra = [];
            li.selectValue = "";
        }
        var v = jQuery.trim(li.selectValue ? li.selectValue : li.innerHTML);
        input.lastSelected = v;
        prev = v;
        $results.html("");
        $input.val(v);
        hideResultsNow();

        if (options.onItemSelect) setTimeout(function() { options.onItemSelect(li,$input.parents("form")) }, 1);
    }

    // selects a portion of the input string
    function createSelection(start, end){
        // get a reference to the input element
        var field = $input.get(0);
        if( field.createTextRange ){
            var selRange = field.createTextRange();
            selRange.collapse(true);
            selRange.moveStart("character", start);
            selRange.moveEnd("character", end);
            selRange.select();
        } else if( field.setSelectionRange ){
            field.setSelectionRange(start, end);
        } else {
            if( field.selectionStart ){
                field.selectionStart = start;
                field.selectionEnd = end;
            }
        }
        field.focus();
    }

    // fills in the input box w/the first match (assumed to be the best match)
    function autoFill(sValue){
        // if the last user key pressed was backspace, don't autofill
        if( lastKeyPressCode != 8 ){
            // fill in the value (keep the case the user has typed)
            $input.val($input.val() + sValue.substring(prev.length));
            // select the portion of the value not typed by the user (so the next character will erase)
            createSelection(prev.length, sValue.length);
        }
    }

    function showResults() {
        $results.appendTo('body');

        //jQuery('.'+options.resultsClass).css({display: "block"}); /* add script on 25-04-2014 */
        $results.css({display: "block"}); /* added script on 03-08-2017 */

        // get the position of the input field right now (in case the DOM is shifted)
        var pos = findPos(input);

        // either use the specified width, or autocalculate based on form element
        var iWidth = (options.width > 0) ? options.width : $input.outerWidth();

        // reposition
        $results.css({
            width: parseInt(iWidth) + "px",
            top: $input.offset().top + $input.outerHeight(true),
            left: pos.x + "px"
        }).show();
    }

    function hideResults() {

        if (jQuery('.ac_results:hover').length != 0) {
            return setTimeout(hideResults, 100);
        }

        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(hideResultsNow, 200);
    }

    function hideResultsNow() {
        //jQuery('.'+options.resultsClass).css({display: "none"});/* add script on 25-04-2014 */
        $results.css({display: "none"}); /* added script on 03-08-2017 */

        if (timeout) clearTimeout(timeout);
        $input.removeClass(options.loadingClass);
        if ($results.is(":visible")) {
            $results.show();
        }
        if (options.mustMatch) {
            var v = $input.val();
            if (v != input.lastSelected) {
                selectItem(null);
            }
        }
    }

    function receiveData(q, data) {
        if (data) {
            $input.removeClass(options.loadingClass);
            results.innerHTML = "";

            // if the field no longer has focus or if there are no matches, do not display the drop down
            if( !hasFocus || data.length == 0 ) return hideResultsNow();

            if (jQuery.browser.msie) {
                // we put a styled iframe behind the calendar so HTML SELECT elements don't show through
                $results.append(document.createElement('iframe'));
            }
            results.appendChild(dataToDom(data));
            // autofill in the complete box w/the first match as long as the user hasn't entered in more data
            if( options.autoFill && ($input.val().toLowerCase() == q.toLowerCase()) ) autoFill(data[0][0]);
            showResults();
        } else {
            hideResultsNow();
        }
    }

    function parseData(data) {
        if (!data) return null;
        var parsed = [];
        var rows = data.split(options.lineSeparator);
        for (var i=0; i < rows.length; i++) {
            var row = jQuery.trim(rows[i]);
            if (row) {
                parsed[parsed.length] = row.split(options.cellSeparator);
            }
        }
        return parsed;
    }

    function dataToDom(data) {
        var ul = document.createElement("ul");
        var num = data.length;

        // limited results to a max number
        if( (options.maxItemsToShow > 0) && (options.maxItemsToShow < num) ) num = options.maxItemsToShow;

        for (var i=0; i < num; i++) {
            var row = data[i];
            if (!row) continue;
            var li = document.createElement("li");
            if (options.formatItem) {
                li.innerHTML = options.formatItem(row, i, num);
                li.selectValue = row[0];
            } else {
                li.innerHTML = row[0];
                li.selectValue = row[0];
            }
            var extra = null;
            if (row.length > 1) {
                extra = [];
                for (var j=1; j < row.length; j++) {
                    extra[extra.length] = row[j];
                }
            }
            li.extra = extra;
            ul.appendChild(li);
            jQuery(li).hover(
                function() { jQuery("li", ul).removeClass("ac_over"); jQuery(this).addClass("ac_over"); active = jQuery("li", ul).indexOf(jQuery(this).get(0)); },
                function() { jQuery(this).removeClass("ac_over"); }
            ).click(function(e) { e.preventDefault(); e.stopPropagation(); selectItem(this) });
        }
        return ul;
    }

    function requestData(q) {
        if (!options.matchCase) q = q.toLowerCase();
        var data = options.cacheLength ? loadFromCache(q) : null;
        // receive the cached data
        //alert(data);
        if (data && data.length) {
            receiveData(q, data);
            // if an AJAX url has been supplied, try loading the data now
        } else if( (typeof options.url == "string") && (options.url.length > 0) ){


            jQuery.ajax({
                // url: url,
                url: makeUrl(q),
                type: 'GET',
                dataType: 'html',
                beforeSend: function () {
                    geodir_search_wait(1);
                },
                success: function (data, textStatus, xhr) {
                    data = parseData(data);
                    addToCache(q, data);
                    receiveData(q, data);
                    geodir_search_wait(0);
                },
                error: function (xhr, textStatus, errorThrown) {
                    console.log(textStatus);geodir_search_wait(0);
                }
            });


            // if there's been no data found, remove the loading class
        } else {
            $input.removeClass(options.loadingClass);
        }
    }

    function makeUrl(q) {
        var sform = jQuery($input).closest('form');
        var url = options.url + "&q=" + encodeURI(q);
        for (var i in options.extraParams) {
            url += "&" + i + "=" + encodeURI(options.extraParams[i]);
        }

        if ($input.attr('name') == geodir_advanced_search_js_msg.autocomplete_field_name) {
            if (sform.find('[name="set_location_type"]').length) {
                url += "&_ltype=" + encodeURI(sform.find('[name="set_location_type"]').val());
            }
            if (sform.find('[name="set_location_val"]').length) {
                url += "&_lval=" + encodeURI(sform.find('[name="set_location_val"]').val());
            }
            if (sform.find('[name="gd_hood_s"]').length) {
                url += "&_lhood=" + encodeURI(sform.find('[name="gd_hood_s"]').val());
            }
        }
        return url;
    }

    function loadFromCache(q) {
        if (gdNearChanged) { // Remove cache on location change
            flushCache();
            gdNearChanged = false;
            return null;
        }
        if (!q) return null;
        if (cache.data[q]) return cache.data[q];
        if (options.matchSubset) {
            for (var i = q.length - 1; i >= options.minChars; i--) {
                var qs = q.substr(0, i);
                var c = cache.data[qs];
                if (c) {
                    var csub = [];
                    for (var j = 0; j < c.length; j++) {
                        var x = c[j];
                        var x0 = x[0];
                        if (matchSubset(x0, q)) {
                            csub[csub.length] = x;
                        }
                    }
                    return csub;
                }
            }
        }
        return null;
    }

    function matchSubset(s, sub) {
        if (!options.matchCase) s = s.toLowerCase();
        var i = s.indexOf(sub);
        if (i == -1) return false;
        return i == 0 || options.matchContains;
    }

    this.flushCache = function() {
        flushCache();
    };

    this.setExtraParams = function(p) {
        options.extraParams = p;
    };

    this.findValue = function(){
        var q = $input.val();

        if (!options.matchCase) q = q.toLowerCase();
        var data = options.cacheLength ? loadFromCache(q) : null;
        if (data && data.length) {
            findValueCallback(q, data);
        } else if( (typeof options.url == "string") && (options.url.length > 0) ){
            jQuery.get(makeUrl(q), function(data) {
                data = parseData(data)
                addToCache(q, data);
                findValueCallback(q, data);
            });
        } else {
            // no matches
            findValueCallback(q, null);
        }
    };

    function findValueCallback(q, data){
        if (data) $input.removeClass(options.loadingClass);

        var num = (data) ? data.length : 0;
        var li = null;

        for (var i=0; i < num; i++) {
            var row = data[i];

            if( row[0].toLowerCase() == q.toLowerCase() ){
                li = document.createElement("li");
                if (options.formatItem) {
                    li.innerHTML = options.formatItem(row, i, num);
                    li.selectValue = row[0];
                } else {
                    li.innerHTML = row[0];
                    li.selectValue = row[0];
                }
                var extra = null;
                if( row.length > 1 ){
                    extra = [];
                    for (var j=1; j < row.length; j++) {
                        extra[extra.length] = row[j];
                    }
                }
                li.extra = extra;
            }
        }

        if( options.onFindValue ) setTimeout(function() { options.onFindValue(li) }, 1);
    }

    function addToCache(q, data) {
        if (!data || !q || !options.cacheLength) return;
        if (!cache.length || cache.length > options.cacheLength) {
            flushCache();
            cache.length++;
        } else if (!cache[q]) {
            cache.length++;
        }
        cache.data[q] = data;
    }

    function findPos(obj) {
        var curleft = obj.offsetLeft || 0;
        var curtop = obj.offsetTop || 0;
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
        return {x:curleft,y:curtop};
    }
};

jQuery.fn.autocomplete_gd = function(url, options, data) {
    // Make sure options exists
    options = options || {};
    // Set url as option
    options.url = url;
    // set some bulk local data
    options.data = ((typeof data == "object") && (data.constructor == Array)) ? data : null;

    // Set default values for required options
    options.inputClass = options.inputClass || "ac_input";
    options.resultsClass = options.resultsClass || "ac_results";
    options.lineSeparator = options.lineSeparator || "\n";
    options.cellSeparator = options.cellSeparator || "|";
    options.minChars = options.minChars || 1;
    options.delay = options.delay || 400;
    options.matchCase = options.matchCase || 0;
    options.matchSubset = options.matchSubset || 1;
    options.matchContains = options.matchContains || 0;
    options.cacheLength = options.cacheLength || 1;
    options.mustMatch = options.mustMatch || 0;
    options.extraParams = options.extraParams || {};
    options.loadingClass = options.loadingClass || "ac_loading";
    options.selectFirst = options.selectFirst || false;
    options.selectOnly = options.selectOnly || false;
    options.maxItemsToShow = options.maxItemsToShow || -1;
    options.autoFill = options.autoFill || false;
    options.width = parseInt(options.width, 10) || 0;

    this.each(function() {
        var input = this;
        new jQuery.autocomplete_gd(input, options);
    });

    // Don't break the chain
    return this;
}

jQuery.fn.autocompleteArray = function(data, options) {
    return this.autocomplete_gd(null, options, data);
}

jQuery.fn.indexOf = function(e){
    for( var i=0; i<this.length; i++ ){
        if( this[i] == e ) return i;
    }
    return -1;
};

// RUN THIS ASAP
(function(){}());



function geodir_search_setup_ac(){

    if (geodir_advanced_search_js_msg.geodir_location_manager_active == 1 && geodir_advanced_search_js_msg.geodir_enable_autocompleter_near == 1) {
        jQuery(".snear").each(function() {
            jQuery(this).keyup(function() {
                jQuery(this).removeClass("near-country near-region near-city");
            });
        });

        jQuery("input[name=snear]").autocomplete_gd(
            geodir_advanced_search_js_msg.geodir_admin_ajax_url + "?action=geodir_autocompleter_near_ajax_action",
            {
                delay: 500,
                minChars: parseInt(geodir_var.as_min_char),
                matchSubset: 1,
                matchContains: 1,
                cacheLength: 1,
                formatItem: formatItemNear,
                onItemSelect: onSelectItemNear,
                autoFill: false
            }
        );
    }

    if (jQuery('.search_by_post').val()) {
        gd_s_post_type = jQuery('.search_by_post').val();
    }else if(jQuery('.geodir-search input[name="stype"]').val()){
        gd_s_post_type = jQuery('.geodir-search input[name="stype"]').val();
    }else {
        gd_s_post_type = "gd_place";
    }


    setTimeout(function(){
        jQuery('.search_by_post').change(function() {
            gd_s_post_type = jQuery(this).val();
            jQuery('.ac_results').remove();
            jQuery.each(gdsa, function(index, item) {
                if (this.autocompleter) {
                    this.autocompleter.setExtraParams({
                        post_type: gd_s_post_type
                    });
                    this.autocompleter.flushCache();
                }
            });
        });
    }, 100);

    if (geodir_advanced_search_js_msg.geodir_enable_autocompleter == 1) {
        try {
            var gdsa = jQuery('.geodir_submit_search').closest('form').find("input[name=" + geodir_advanced_search_js_msg.autocomplete_field_name + "]").autocomplete_gd(geodir_advanced_search_js_msg.geodir_admin_ajax_url + "?action=geodir_autocompleter_ajax_action", {
                delay: 500,
                minChars: parseInt(geodir_var.as_min_char),
                matchSubset: 1,
                matchContains: 1,
                cacheLength: 1,
                formatItem: formatItem,
                onItemSelect: onSelectItem,
                autoFill: false,
                extraParams: {
                    post_type: gd_s_post_type
                }
            });
        } catch (e) {}
    }

    // customize search deselect
    if (jQuery('.gd-adv-search-labels label').length) {
        jQuery('.gd-adv-search-labels label').live('click', function(event) {
            var dataName = jQuery(this).attr('data-name');
            var dataNames = jQuery(this).attr('data-names');

            if (typeof dataName != 'undefined' || jQuery(this).hasClass('gd-adv-search-near')) {
                if (jQuery(this).hasClass('gd-adv-search-near')) {
                    dataName = 'snear';
                }

                var dataEl = jQuery('form.geodir-listing-search').find('[name="' + dataName + '"]');
                gd_adv_search_deselect(dataEl);

                if (typeof dataNames != 'undefined') {
                    dataEl = jQuery('form.geodir-listing-search').find('[name="' + dataNames + '"]');
                    gd_adv_search_deselect(dataEl);
                }

                jQuery('form.geodir-listing-search').find('.geodir_submit_search').click();
            }
        });
    }

    jQuery('.geodir_advance_search_widget.geodir-advance-search-searched').each(function() {
        var $this = this;

        if (jQuery($this).attr('data-show-adv') == 'search') {
            jQuery('.showFilters', $this).trigger('click');
        }
    });

    jQuery('.customize_filter', '.geodir-filter-container').each(function() {
        var $cont = this;
        var $form = jQuery($cont).closest('form');
        var $adv_show = jQuery($form).closest('.geodir_advance_search_widget').attr('data-show-adv');

        if ($adv_show == 'always' && typeof jQuery('.showFilters', $form).html() != 'undefined') {
            // jQuery('.geodir_submit_search:first', $form).css({
            //     'visibility': 'hidden'
            // });
            jQuery('.showFilters', $form).remove();
            if (!jQuery('.customize_filter', $form).is(":visible")) {
                jQuery('.customize_filter', $form).slideToggle(500);
                //jQuery('.customize_filter', $form).hide();
            }
        }
    });

}


// RUN THIS ON LOAD
jQuery(document).ready(function($) {
    gdsText = jQuery('input[type="button"].geodir_submit_search').val();
    
    // if search form submitted without Near: Me then clear the positions
    jQuery('.geodir_submit_search').click(function() {
        var $form = jQuery(this).closest('form');
        geodir_search_wait(1);
        var is_near_me = false;
        var $snear = jQuery('.snear', $form).val();
        
        if ($snear == (geodir_advanced_search_js_msg.msg_Near + ' ' + geodir_advanced_search_js_msg.msg_Me)) {
            is_near_me = true;
        } else if ($snear == (geodir_advanced_search_js_msg.msg_Near + ' ' + geodir_advanced_search_js_msg.msg_User_defined)) {
            is_near_me = true;
        } else if ($snear.match('^' + geodir_advanced_search_js_msg.msg_In)) {
            is_near_me = true;
        }
        
        if (jQuery('.sgeo_lat').val() != '' && jQuery('.sgeo_lon').val() != '' && !is_near_me) {
            jQuery('.sgeo_lat').val('');
            jQuery('.sgeo_lon').val('');
        }
        return false;
    });

    //setup advanced search form on load
    geodir_search_setup_ac();

    //setup advanced search form on form ajax load
    jQuery("body").on("geodir_setup_search_form", function(){
        geodir_search_setup_ac();
        gdSetupUserLoc();
    });



});

function gd_adv_search_deselect(el) {
    var fType = jQuery(el).prop('type');
    switch (fType) {
        case 'checkbox':
        case 'radio':
            jQuery(el).prop('checked', false);
            break;
    }
    jQuery(el).val('');
}


// OTHER 
// STATIC FUNCTIONS
function gdGetLocation(box) {
    jQuery('.snear').removeClass("near-country near-region near-city"); // remove any location classes
    if (box && box.prop('checked') != true) {
        gdClearUserLoc();
        return;
    }
    // Try HTML5 geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            lat = position.coords.latitude;
            lon = position.coords.longitude;
            gdSetUserLocation(position.coords.latitude, position.coords.longitude, 1);
            //alert(position.coords.latitude+','+position.coords.longitude);
        }, gdLocationError, gdLocationOptions);
    } else {
        // Browser doesn't support Geolocation
        alert(geodir_advanced_search_js_msg.GEOLOCATION_NOT_SUPPORTED);
    }
}

function gdClearUserLoc() {
    lat = '';
    lon = '';
    my_location = '';
    userMarkerActive = true; /* trick script to not add marker */
    gdSetUserLocation(lat, lon, my_location);
}

function setusermarker(new_lat, new_lon, map_id) {
    if (window.gdMaps == 'osm') {
        setusermarker_osm(new_lat, new_lon, map_id);
        return;
    }
    var image = new google.maps.MarkerImage(
        geodir_advanced_search_js_msg.geodir_advanced_search_plugin_url + '/css/map_me.png',
        null, // size
        null, // origin
        new google.maps.Point(8, 8), // anchor (move to center of marker)
        new google.maps.Size(17, 17) // scaled size (required for Retina display icon)
    );
    if (map_id) {
        goMap = jQuery('#' + map_id).goMap();
    }
    if (gdUmarker['visible']) {
        return;
    } // if marker exists bail
    if (typeof goMap == 'undefined') {
        return;
    } // if no map on page bail
    var coord = new google.maps.LatLng(lat, lon);
    gdUmarker = jQuery.goMap.createMarker({
        optimized: false,
        flat: true,
        draggable: true,
        id: 'map_me',
        title: 'Set Location',
        position: coord,
        visible: true,
        clickable: true,
        icon: image
    });
    jQuery.goMap.createListener({
        type: 'marker',
        marker: 'map_me'
    }, 'dragend', function() {
        latLng = gdUmarker.getPosition();
        lat = latLng.lat();
        lon = latLng.lng();
        gdSetUserLocation(lat, lon, 0);
    });
    userMarkerActive = true;
}

function setusermarker_osm(new_lat, new_lon, map_id) {
    if (map_id) {
        goMap = jQuery('#' + map_id).goMap();
    }
    
    if (jQuery.goMap.gdUmarker) {
        return;
    } // if marker exists bail
    
    if (typeof goMap == 'undefined') {
        return;
    } // if no map on page bail
    
    gdUmarker = jQuery.goMap.createMarker({
        optimized: false,
        flat: true,
        draggable: true,
        id: 'map_me',
        title: 'Set Location',
        position: new L.latLng(lat, lon),
        visible: true,
        clickable: true,
        addToMap: true
    });
    
    gdUmarker.setIcon(L.divIcon({
        iconSize: [17, 17],
        iconAnchor: [8.5, 8.5],
        className: 'gd-user-marker',
        html: '<div class="gd-user-marker-box"><div class="gd-user-marker-animate"></div><img class="gd-user-marker-img" src="' + geodir_advanced_search_js_msg.geodir_advanced_search_plugin_url + '/css/map_me.png' + '" /></div>'
    }));
    
    gdUmarker.on('dragend', function(e) {
        gdULatLng = gdUmarker.getLatLng();
        gdSetUserLocation(gdULatLng.lat, gdULatLng.lng, 0);
    });
    
    jQuery.goMap.gdUmarker = gdUmarker;
    
    userMarkerActive = true;
}

function moveUserMarker(lat, lon) {
    if (window.gdMaps == 'google') {
        var coord = new google.maps.LatLng(lat, lon);
        gdUmarker.setPosition(coord);
    } else if (window.gdMaps == 'osm') {
        var coord = new L.latLng(lat, lon);
        gdUmarker.setLatLng(coord);
    }
}

function removeUserMarker() {
    if (typeof goMap != 'undefined') {
        jQuery.goMap.removeMarker('map_me');
    }
    userMarkerActive = false;
}

function gdLocationError(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            alert(geodir_advanced_search_js_msg.PERMISSION_DENINED);
            break;
        case error.POSITION_UNAVAILABLE:
            alert(geodir_advanced_search_js_msg.POSITION_UNAVAILABLE);
            break;
        case error.TIMEOUT:
            alert(geodir_advanced_search_js_msg.DEFAUTL_ERROR);
            break;
        case error.UNKNOWN_ERROR:
            alert(geodir_advanced_search_js_msg.UNKNOWN_ERROR);
            break;
    }
}

function gdSetupUserLoc() {
    if (my_location) {
        jQuery('.geodir-search .fa-compass').css("color", geodir_advanced_search_js_msg.compass_active_color);
        jQuery('.gt_near_me_s').prop('checked', true);
        jQuery('.snear').val(geodir_advanced_search_js_msg.msg_Near + ' ' + geodir_advanced_search_js_msg.msg_Me);
        jQuery('.sgeo_lat').val(lat);
        jQuery('.sgeo_lon').val(lon);
    } else {
        if (lat && lon) {
            jQuery('.geodir-search .fa-compass').css("color", geodir_advanced_search_js_msg.compass_active_color);
            jQuery('.gt_near_me_s').prop('checked', true);
            jQuery('.snear').val(geodir_advanced_search_js_msg.msg_Near + ' ' + geodir_advanced_search_js_msg.msg_User_defined);
            jQuery('.sgeo_lat').val(lat);
            jQuery('.sgeo_lon').val(lon);
        } else if (jQuery('.snear').length && jQuery('.snear').val().match("^" + geodir_advanced_search_js_msg.msg_Near)) {
            jQuery('.geodir-search .fa-compass').css("color", "");
            jQuery('.gt_near_me_s').prop('checked', false);
            jQuery('.snear').val('');
            jQuery('.snear').blur();
            jQuery('.sgeo_lat').val('');
            jQuery('.sgeo_lon').val('');
        }
    }
}

function gdSetUserLocation(lat, lon, my_loc) {
    if (my_loc) {
        my_location = 1;
    } else {
        my_location = 0;
    }
    gdSetupUserLoc();
    if (userMarkerActive == false) {
        jQuery.each(map_id_arr, function(key, value) {
            setusermarker(lat, lon, value); // set marker on map
        });
    } else if (lat && lon) {
        moveUserMarker(lat, lon);
    } else {
        removeUserMarker();
    }
    jQuery.ajax({
        // url: url,
        url: geodir_advanced_search_js_msg.geodir_admin_ajax_url,
        type: 'POST',
        dataType: 'html',
        data: {
            action: 'gd_set_user_location',
            lat: lat,
            lon: lon,
            myloc: my_location
        },
        success: function(data, textStatus, xhr) {
            //alert(data);
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(errorThrown);
        }
    });
}

function gdasShowRange(el){
	jQuery('.gdas-range-value-out').html(jQuery(el).val()+' '+geodir_advanced_search_js_msg.unom_dist);
}

function gdasSetRange(el){
    range = jQuery(el).val();
	var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url;
	jQuery.post(ajax_url,
	{	action: 'geodir_set_near_me_range', 
		range:range
	},
	function(data){
		//alert(data);
	});

}

// SHARE LOCATION SCRIPT
function geodir_do_geolocation_on_load() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(geodir_position_success_on_load, geodir_position_error, {
            timeout: 10000
        });
    } else {
        var error = {
            code: '-1'
        };
        geodir_position_error(error);
    }
}

function geodir_position_error(err) {
    var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url;
    var msg;
    var default_err = false;
    
    switch (err.code) {
        case err.UNKNOWN_ERROR:
            msg = geodir_advanced_search_js_msg.UNKNOWN_ERROR;
            break;
        case err.PERMISSION_DENINED:
            msg = geodir_advanced_search_js_msg.PERMISSION_DENINED;
            break;
        case err.POSITION_UNAVAILABLE:
            msg = geodir_advanced_search_js_msg.POSITION_UNAVAILABLE;
            break;
        case err.BREAK:
            msg = geodir_advanced_search_js_msg.BREAK;
            break;
        case 3:
            geodir_position_success_on_load(null);
            break;
        default:
            msg = geodir_advanced_search_js_msg.DEFAUTL_ERROR;
            default_err = true;
            break;
    }

    jQuery.post(ajax_url, {
            action: 'geodir_share_location',
            geodir_ajax: 'share_location',
            error: true
        },
        function(data) {
            //window.location = data;
        });
    
    if (!default_err) {
        alert(msg);
    }
}

function geodir_position_success_on_load(position) {
    var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url;
    var request_param = geodir_advanced_search_js_msg.request_param;
    var redirect = typeof redirect !== 'undefined' ? redirect : '';
    var lat;
    var long;
    
    if (position && position !== null) {
        var coords = position.coords || position.coordinate || position;
        lat = coords.latitude;
        long = coords.longitude;
    }
    
    jQuery.post(ajax_url, {
        action: 'geodir_share_location',
        geodir_ajax: 'share_location',
        lat: lat,
        long: long,
        request_param: request_param
    }, function(data) {
        if (data && data !== 'OK') {
            window.location = data;
        }
    });
}
jQuery(document).ready(function() {
    geodir_search_onload_redirect();
});

function geodir_search_onload_redirect() {
    var onloadRedirect = geodir_advanced_search_js_msg.onload_redirect;
    var onloadAskRedirect = geodir_advanced_search_js_msg.onload_askRedirect;
    
    if (!onloadAskRedirect) {
        return;
    }


    // Cache busting local storage if page is cached we don't keep showing redirect popup.
    if (typeof(Storage) !== "undefined" && sessionStorage.getItem('gd_onload_redirect_done')!='1') {

        sessionStorage.setItem('gd_onload_redirect_done', '1');

        switch(onloadRedirect) {
            case 'nearest':
                if (geodir_advanced_search_js_msg.geodir_autolocate_ask) {
                    if (confirm(geodir_advanced_search_js_msg.geodir_autolocate_ask_msg)) {
                        geodir_do_geolocation_on_load();
                    } else {
                        geodir_position_do_not_share();
                    }
                } else {
                    geodir_do_geolocation_on_load();
                }
            break;
            case 'location':
                var redirectLocation = geodir_advanced_search_js_msg.onload_redirectLocation;
                if (redirectLocation && redirectLocation !== '') {
                    window.location = redirectLocation;
                }
            break;
            case 'no':
            default:
                geodir_position_do_not_share();
            break;
        }



    }
}

function geodir_position_do_not_share() {
    var ajax_url = geodir_advanced_search_js_msg.geodir_admin_ajax_url;
    
    jQuery.post(ajax_url, {
        action: 'geodir_do_not_share_location',
        geodir_ajax: 'share_location'
    }, function(data){
    });
}

// AUTOCOMPLETER FUNCTIONS
function formatItemNear(row) {
	var attr;
	
	if (row.length == 3) {
		attr = "attr=\"" + row[2] + "\"";
	} else {
		attr = "";
	}
	return row[0] + "<span " + attr + "></span>";
}

gdReplaceASC_load = true;
// @depreciated
function gdReplaceASC(stype, el) {


	if (!stype) {
		return;
	}
	var $form = jQuery(el).closest('form');
	
	var $adv_show = jQuery($form).closest('.geodir_advance_search_widget').attr('data-show-adv');

	jQuery($form).addClass('geodir-adv-form-wait');
	if ($adv_show == 'always' || $adv_show == 'search') {
		jQuery('.geodir-loc-bar-in', $form).css({'opacity' : '.66'});
		jQuery('.customize_filter', $form).css({'opacity' : '.66'});
		if (jQuery('.customize_filter', $form).is(":visible")) {
			jQuery('.customize_filter', $form).slideToggle(500);
		}
		
		//jQuery('.geodir_submit_search', $form).css('visibility', 'hidden');
	} else {
		jQuery('.customize_filter', $form).hide('slow');
		jQuery('.geodir_submit_search', $form).css('visibility', 'visible');
	}

	
	var $div = jQuery(el).closest('.geodir-search');

	var button = '';
	var html = '';
	jQuery.ajax({
		url: geodir_advanced_search_js_msg.geodir_admin_ajax_url,
		type: 'POST',
		dataType: 'html',
		data: {
			action: 'geodir_advance_search_button_ajax',
			stype: stype,
			adv: $adv_show
		},
		beforeSend: function() {
           if(!gdReplaceASC_load ) {geodir_search_wait(1);}//only show the animation if not the first load of page
            gdReplaceASC_load = false;
        },
		success: function(data, textStatus, xhr) {			
			jQuery($form).removeClass('geodir-adv-form-wait');
			jQuery('body').removeClass('gd-multi-datepicker');
			if (data) {
				gdGetCustomiseHtml(stype, data, el);
			} else {
				if ($adv_show == 'always' || $adv_show == 'search') {
					jQuery('.geodir_submit_search', $form).css('visibility', 'visible');
					jQuery('.geodir-loc-bar-in', $form).css({'opacity' : '1'});
				}
				gdSetupAjaxAdvancedSearch('', '', el);
                geodir_search_wait(0);
			}
			
			geodir_reposition_compass();
			//jQuery('.geodir-filter-container').html(data);
		},
		error: function(xhr, textStatus, errorThrown) {
			console.log(textStatus);geodir_search_wait(0);
		},
		complete: function(xhr, textStatus) {

		}
	});
}
		
function gdGetCustomiseHtml(stype, button, el) {
	var $adv_show = jQuery(el).closest('.geodir_advance_search_widget').attr('data-show-adv');
	
	jQuery.ajax({
		url: geodir_advanced_search_js_msg.geodir_admin_ajax_url,
		type: 'POST',
		dataType: 'html',
		data: {
			action: 'gd_advancedsearch_customise',
			stype: stype,
			adv: $adv_show
		},
		beforeSend: function() {},
		success: function(data, textStatus, xhr) {
			gdSetupAjaxAdvancedSearch(button, data, el);
			geodir_reposition_compass();
			//jQuery('.geodir-filter-container').html(data);
            geodir_search_wait(0);
		},
		error: function(xhr, textStatus, errorThrown) {
			console.log(textStatus);geodir_search_wait(0);
		}
	});
}
		
function gdSetupAjaxAdvancedSearch(button, html, el) {
	var $form = jQuery(el).closest('form');
	var $adv_show = jQuery($form).closest('.geodir_advance_search_widget').attr('data-show-adv');

	if (button) {
		if (jQuery('.showFilters', $form).length) {
		} else {
			jQuery('.geodir_submit_search:visible', $form).after(button);
		}
		jQuery($form).addClass('geodir-adv-form');
		
		jQuery('.geodir-filter-container', $form).html(html);
		
		if ($adv_show == 'always' || $adv_show == 'search') {
			jQuery('.geodir-loc-bar-in', $form).css({'opacity' : '1'});
			if (!jQuery('.customize_filter', $form).is(":visible")) {
				jQuery('.customize_filter', $form).slideToggle(500);
			}
			
			if ($adv_show == 'always') {
				jQuery('.showFilters', $form).remove();
			} else {
				jQuery('body').addClass('geodir_advance_search');
			}
			//jQuery('.geodir_submit_search:first', $form).css({'visibility': 'hidden'});
		} else {
			jQuery('body').addClass('geodir_advance_search');
		}
	} else {
		jQuery('.showFilters', $form).remove();
		jQuery('.geodir-filter-container', $form).html('');
		jQuery('body').removeClass('geodir_advance_search');
		
		jQuery($form).removeClass('geodir-adv-form');
	}
	
	geodir_setup_submit_search();
}
		
function onSelectItem(row, $form) {
	if (geodir_advanced_search_js_msg.geodir_autocompleter_autosubmit == 1) {
		var link = jQuery(row).find('span').attr('link');
		if (typeof link != 'undefined' && link != '') { // If listing link set then redirect to listing page.
			window.location.href = link;
			return true;
		}
		
		if ($form.find(' input[name="snear"]').val() == geodir_advanced_search_js_msg.default_Near) {
			jQuery('input[name="snear"]').val('');
		}
		
		// Disable location based search for disabled location post type.
		if (jQuery('.search_by_post', $form).val() != '' && typeof gd_cpt_no_location == 'function') {
			if (gd_cpt_no_location(jQuery('.search_by_post', $form).val())) {
				jQuery('.snear', $form).remove();
				jQuery('.sgeo_lat', $form).remove();
				jQuery('.sgeo_lon', $form).remove();
				jQuery('select[name="sort_by"]', $form).remove();
			}
		}
		
		if (typeof(jQuery(row).find('span').attr('attr')) != 'undefined') {
			jQuery('input.geodir_submit_search', $form).trigger('click');
			//$form.submit();
		} else {
			jQuery('input.geodir_submit_search', $form).trigger('click');
			//$form.submit();
		}
	} else {
		jQuery(row).parents("form").find('input[name="' + geodir_advanced_search_js_msg.autocomplete_field_name + '"]').focus();
	}
}
		
function onSelectItemNear(row, $form) {
	//gdClearUserLoc(); // we now set this with sessions
	nClass = "";
	lType = "";
    
    gdNearChanged = true;
	
	if (row.extra[2] == 1) {
		nClass = "near-country";
		lType = geodir_advanced_search_js_msg.txt_in_country;
	} else if (row.extra[2] == 2) {
		nClass = "near-region";
		lType = geodir_advanced_search_js_msg.txt_in_region;
	} else if (row.extra[2] == 3) {
		nClass = "near-city";
		lType = geodir_advanced_search_js_msg.txt_in_city;
	} else if (row.extra[2] == 4) {
		nClass = "near-neighbourhood";
		lType = geodir_advanced_search_js_msg.txt_in_hood;
		
		if ($form.find(' input[name="gd_hood_s"]').length) {
			$form.find(' input[name="gd_hood_s"]').val(row.extra[3]);
		} else {
			$form.find(' input[name="snear"]').after('<input name="gd_hood_s" type="hidden" value="' + row.extra[3] + '" />');
		}
	}
	
	if (row.extra[2] != 4) {
		$form.find('input[name="gd_hood_s"]').remove();
	}

	fillVal = geodir_advanced_search_js_msg.msg_In + " " + row.extra[0] + " " + lType;
	$form.find(' input[name="snear"]').val(fillVal);
	
	if ($form.find(' input[name="set_location_val"]').length) {
		$form.find(' input[name="set_location_val"]').val(row.extra[1]);
	} else {
		$form.find(' input[name="snear"]').after('<input name="set_location_val" type="hidden" value="' + row.extra[1] + '" />');
	}
	
	if ($form.find(' input[name="set_location_type"]').length) {
		$form.find(' input[name="set_location_type"]').val(row.extra[2]);
	} else {
		$form.find(' input[name="snear"]').after('<input name="set_location_type" type="hidden" value="' + row.extra[2] + '" />');
	}
	
	$form.find(' input[name="snear"]').removeClass("near-country near-region near-city");
	
	if (nClass) {
		$form.find(' input[name="snear"]').addClass(nClass);
	}
	
	if (geodir_advanced_search_js_msg.geodir_autocompleter_autosubmit_near == 1) {
		setTimeout(function() {
			$form.find('.geodir_submit_search').click();
		}, 100);
	}
}
		
function formatItem(row) {
	var attr;
	if (row.length == 3 && row[2] != '') {
		attr = "attr=\"" + row[2] + "\"";
	} else {
		attr = "";
	}
	var link = '';
	var icon = '';
	var exp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
	if (typeof row[1] != 'undefined' && row[1] != '' && exp.test(row[1])) {
        link = ' link="' + row[1] + '" class="gd-search-has-link"';
		icon = '<i class="fa fa-angle-double-right"></i>';
	}
	return row[0] + "<span " + attr + " " + link + ">" + icon + "</span>";
}		
		
function geodir_insert_compass() {

    if(geodir_advanced_search_js_msg.search_new_style=='1'){return;}

	jQuery('.snear').each(function() {
		var $this = jQuery(this);
		jQuery('<span class="near-compass" data-dropdown=".gd-near-me-dropdown"></span>').css({
			position: 'absolute',
			left: $this.position().left + $this.outerWidth() + parseInt($this.css('margin-left')) - ($this.innerHeight()),
			top: $this.position().top + parseInt($this.css('margin-top')) + ($this.outerHeight() - $this.innerHeight()) / 2,
			fontSize: ($this.innerHeight() + parseInt($this.css('margin-top'))) * 0.95,
			'line-height': '0px'
		}).html('<i class="fa fa-compass"></i>').data('inputEQ', $this.index()).insertAfter($this);
	});
	
	jQuery(window).resize(function() {
		geodir_reposition_compass();
	});
}

function geodir_reposition_compass() {

    if(geodir_advanced_search_js_msg.search_new_style=='1'){return;}
    
	jQuery('.snear').each(function() {
		var $this = jQuery(this);
		jQuery($this).next('.near-compass').css({
			position: 'absolute',
			left: $this.position().left + $this.outerWidth() + parseInt($this.css('margin-left')) - ($this.innerHeight()),
			top: $this.position().top + parseInt($this.css('margin-top')) + ($this.outerHeight() - $this.innerHeight()) / 2,
			fontSize: ($this.innerHeight() + parseInt($this.css('margin-top'))) * 0.95,
			'line-height': '0px'
		});
	});
}

function geodir_search_expandmore(el) {
	var moretext = jQuery.trim(jQuery(el).text());
	jQuery(el).closest('ul').find('.more').toggle('slow');
	if (moretext == geodir_advanced_search_js_msg.text_more) {
		jQuery(el).text(geodir_advanced_search_js_msg.text_less);
	} else {
		jQuery(el).text(geodir_advanced_search_js_msg.text_more);
	}
}



if (typeof window.gdShowFilters === 'undefined') {
    window.gdShowFilters = function (fbutton) {
        var $form = jQuery(fbutton).closest('form');
        var $adv_show = jQuery($form).closest('.geodir_advance_search_widget').attr('data-show-adv');
        if ($adv_show == 'always') {
        } else {

            jQuery('button.showFilters i').addClass('fa-spin');


            jQuery(".customize_filter", $form).slideToggle("slow", function () {

                //alert(1);

                // if (jQuery(this).is(":visible")) {
                //     jQuery('.geodir_submit_search:first', $form).css({'visibility': 'hidden'});
                // } else {
                //     jQuery('.geodir_submit_search:first', $form).css({'visibility': 'visible'});
                // }

                jQuery('button.showFilters i').removeClass('fa-spin');
            });


        }
    }
}

/*
 * jQuery dropdown: A simple dropdown plugin
 *
 * MODIFIED FOR GEODIRECTORY
 *
 * Copyright A Beautiful Site, LLC. (http://www.abeautifulsite.net/)
 *
 * Licensed under the MIT license: http://opensource.org/licenses/MIT
 *
 */
jQuery && function(t) {
    function o(o, d) {
        var n = o ? t(this) : d,
            a = t(n.attr("data-dropdown")),
            s = n.hasClass("dropdown-open");
        if (o) {
            if (t(o.target).hasClass("dropdown-ignore")) return;
            o.preventDefault(), o.stopPropagation()
        } else if (n !== d.target && t(d.target).hasClass("dropdown-ignore")) return;
        r(), s || n.hasClass("dropdown-disabled") || (n.addClass("dropdown-open"), a.data("dropdown-trigger", n).show(), e(), a.trigger("show", {
            dropdown: a,
            trigger: n
        }))
    }

    function r(o) {
        var r = o ? t(o.target).parents().addBack() : null;
        if (r && r.is("div.gd-dropdown")) {
            if (!r.is(".dropdown-menu")) return;
            if (!r.is("A")) return
        }
        t(document).find("div.gd-dropdown:visible").each(function() {
            var o = t(this);
            o.hide().removeData("dropdown-trigger").trigger("hide", {
                dropdown: o
            })
        }), t(document).find(".dropdown-open").removeClass("dropdown-open")
    }

    function e() {
        var o = t(".gd-dropdown:visible").eq(0),
            r = o.data("dropdown-trigger"),
            e = r ? parseInt(r.attr("data-horizontal-offset") || 0, 10) : null,
            d = r ? parseInt(r.attr("data-vertical-offset") || 0, 10) : null;

        jQuery(o).appendTo('body');
        0 !== o.length && r && o.css(o.hasClass("dropdown-relative") ? {
            left: o.hasClass("dropdown-anchor-right") ? r.position().left - (o.outerWidth(!0) - r.outerWidth(!0)) - parseInt(r.css("margin-right"), 10) + e : r.position().left + parseInt(r.css("margin-left"), 10) + e,
            top: r.position().top + r.outerHeight(!0) - parseInt(r.css("margin-top"), 10) + d
        } : {
            left: o.hasClass("dropdown-anchor-right") ? r.offset().left - (o.outerWidth() - r.outerWidth()) + e : r.offset().left + e,
            top: r.offset().top + r.outerHeight(true) + d
        })
    }
    t.extend(t.fn, {
        dropdown: function(e, d) {
            switch (e) {
                case "show":
                    return o(null, t(this)), t(this);
                case "hide":
                    return r(), t(this);
                case "attach":
                    return t(this).attr("data-dropdown", d);
                case "detach":
                    return r(), t(this).removeAttr("data-dropdown");
                case "disable":
                    return t(this).addClass("dropdown-disabled");
                case "enable":
                    return r(), t(this).removeClass("dropdown-disabled")
            }
        }
    }), t(document).on("click.dropdown", "[data-dropdown]", o), t(document).on("click.dropdown", r), t(window).on("resize", e)
}(jQuery);
