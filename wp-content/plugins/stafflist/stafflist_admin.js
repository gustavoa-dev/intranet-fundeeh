/* stafflist handlers */
jQuery(document).ready(function() {
	/**************************************************************************************************
	*	Instructions Tab
	**************************************************************************************************/
	var instr = jQuery('#stafflist_instructions');
	var instrh = instr.height();
	instr.css({ 'top': (-1* (instrh+22)) +"px", 'display':'block' });
	jQuery("#stafflist_instructions div.sltab").on("click", function(){
		if(instr.data("expanded")<1){
			instr.css({ top: "-1px" }).data('expanded',1); jQuery(this).addClass("expanded");
		} else {
			instr.css({ top: (-1* (instrh+22)) +"px" }).data('expanded',0); jQuery(this).removeClass("expanded");
		}
	});
	/**************************************************************************************************
	*	On the Fly Updates
	**************************************************************************************************/
    jQuery('#stafflists input[type="text"]').addClass("idleField"); 
    jQuery('#newstaff   input[type="text"]').addClass("idleField");
    jQuery('#stafflists input[type="text"]').focus(function() {  
        jQuery(this).removeClass("idleField").addClass("focusField");  
        if (this.value == this.defaultValue){  
            this.select(); 
        }
    });  
    jQuery('#stafflists input[type="text"]').blur(function() {  
        jQuery(this).removeClass("focusField").addClass("idleField");
        var fd = jQuery(this);
        if (fd.val() == unescape(this.defaultValue)){  
            this.value = (this.defaultValue ? unescape(this.defaultValue) : '');  
        } else {	
        	var fname = fd.attr('id').split(":");
        	var fval = fd.val();
        	updateField(fname,fval);
        }
    });
    
    /**************************************************************************************************
    *	Set some custom titles for Standard Columns
    **************************************************************************************************/
    jQuery('#stafflistRenameColumns').click(function(){
    	var titles = jQuery("#stafflistRename").serialize();
    	stafflistRename(titles);
    });
    
    /**************************************************************************************************
    *	Adding Another Staff Record
    **************************************************************************************************/
    jQuery("#stafflist_new").focus(function() {
    	//fetch next id
    	var newid = false;
    	jQuery.post(ajaxurl, {
    		action: 'ajax_nextrow',
    		async: false
    	}, function(data) {
    		newid = data;
    		
    		if(newid>0 && jQuery('#staff_'+newid).length == 0){
    			
    			//clone row
    	    	var tr = jQuery("#stafflists tr:last");
    	    	jQuery(tr).clone().insertAfter(tr).find( 'input:text' ).val('');
    	    	
	        	//prepare fields with new id
	        	jQuery("#stafflists tr:last").find('input:text').each(function(e){
	        		var oldid = jQuery(this).attr("id").split(":");
	        		jQuery(this).attr("id",oldid[0]+":"+newid);
	        	});
	        	
	        	jQuery("#stafflists tr:last").attr("id","staff_"+newid);
	        	jQuery("#stafflists tr:last a.remove").remove();
	        	
	        	//bind handler to new row
	            jQuery('#stafflists tr:last input[type="text"]').focus(function() {  
	                jQuery(this).removeClass("idleField").addClass("focusField");  
	                if (this.value == this.defaultValue){  
	                    this.select(); 
	                }
	            });
	            jQuery('#stafflists tr:last input[type="text"]').blur(function() {  
	                jQuery(this).removeClass("focusField").addClass("idleField");
	                var fd = jQuery(this);                 
	                var fname = fd.attr('id').split(":");
                	var fval = fd.val();
                	if(false!=fname[1]) updateField(fname,fval);

	            });
	        	//focus first text field
	        	jQuery("#stafflists tr:last").find('input:first').focus();
    		}
    	});

    });
    
    /**************************************************************************************************
    *	Full Directory > Search
    **************************************************************************************************/
    jQuery("#searchdirectory").keyup(function(e){ 
        if(e.which === 13) {
        	e.preventDefault();
        	var searchpath = paths.pluginurl+"&search="+encodeURIComponent(jQuery("#searchdirectory").val());
        	window.location = searchpath;
        }
    });
    
    /**************************************************************************************************
    *	Sortable Columns
    **************************************************************************************************/
    jQuery( "#sortable1, #sortable2" ).sortable({ connectWith: ".connectedSortable",
    											  stop: function( event, ui ){
    												  var data = {  action:'stafflist_sort',
    														  		active: jQuery("#sortable1").sortable('toArray'),
    														  		inactive:jQuery("#sortable2").sortable('toArray') };
    												  jQuery.post(ajaxurl, data, function(response){
    													  //console.log(response);
    												  });
    											  }
    }).disableSelection();
    
    /**************************************************************************************************
    *	Exporting
    **************************************************************************************************/
    jQuery(".stafflist_export").on("click", function(){
    	var exporttyp = jQuery(this).attr("rel");
    	var exportloc = paths.pluginurl;
    	var query = window.location.search.replace('?','').split('&');
        for(var q = 0, qlen = query.length; q < qlen; q++) {
	          var qArr = query[q].split('=');
	          if(qArr[0]=="search"||qArr[0]=="s" && qArr[1]!="") exportloc += "&"+qArr[0]+"="+qArr[1];
	    }
        exportloc+= ("&export=" + (exporttyp=="csv" ? "csv" : "xlsx"));
    	window.open( exportloc );
    });

    /**************************************************************************************************
    *	Show the Donate Form for the Kind of Heart
    **************************************************************************************************/
	var $donateform = jQuery("#stafflistwrap div.donate").html();
	jQuery("#stafflistwrap div.donate").html("<form action='https://www.paypal.com/cgi-bin/webscr' method='post' id='donate' target='_blank'>"+$donateform+"</form>").css("display","block");

});

/**************************************************************************************************
*	AJAX Function for On the Fly Updates
**************************************************************************************************/
function updateField(fname,fval){
    showLoading(fname[0]+":"+fname[1]);  		// shows updating gif
	jQuery.post(ajaxurl, {
		action: 'ajax_update',
		fval: fval,
		fname: fname							// query is built in ajax function; returns true/false
	}, function(data) {
		//alert(data); 							// changes default value
		document.getElementById(fname[0]+":"+fname[1]).defaultValue = escape(fval);
		hideLoading(fname[0]+":"+fname[1]);		// hides updating gif
	});
	return;
}
/**************************************************************************************************
*	Set some custom titles for Standard Columns
**************************************************************************************************/
function stafflistRename(titles){
	jQuery.post(ajaxurl, {
		action: 'stafflist_rename',
		data: titles
	}, function(resp) {
		if(resp) jQuery("#response_rename").html(resp.msg).show();
	});
	return;
}
/**************************************************************************************************
*	Indicators
**************************************************************************************************/
function showLoading(div){
	if(document.getElementById(div)) {
		document.getElementById(div).className = 'updateField';
	}
}
function hideLoading(div){
	if(document.getElementById(div)) {
		document.getElementById(div).className = 'idleField';
	}
}
/**************************************************************************************************
*	Done
**************************************************************************************************/