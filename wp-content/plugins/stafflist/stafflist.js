/* stafflist handlers */
jQuery(document).ready( function($) {
	build_stafflist();	
});

/**************************************************************************************************
*	Build a Dynamic Pager
**************************************************************************************************/
var sl_current_page = false,
    sl_total_pages = false;
var sl_pager = new function() {
	this.makeBtn = function(c,v){ return "<p class='pager"+c+"'>"+v+"</p>"; };
	this.build = function(pgobj){ //console.log(pgobj); console.log(stafflistpaths);
		//globals
		cur = sl_current_page = parseInt(pgobj[3]),
		last = sl_total_pages = parseInt(pgobj[2]);
		//one page?
		if(pgobj[0] < 1) return "<div class='pageNum'></div>";
		if(last < 2) return "<div class='pageNum'>"+stafflistpaths.results+": "+(pgobj[0])+"</div>";
		//previous
		html = (cur > 1 ? this.makeBtn(" wp-exclude-emoji prev", "") : this.makeBtn(" wp-exclude-emoji prev disabled", "")); //<
		//pages
		if(cur<=3){
			for(var p=1; p<=Math.min(last,5); p++){
				html += (p == cur ? this.makeBtn(" current disabled", cur) : this.makeBtn("", p));
			}
			if(last> 5) html += this.makeBtn(" disabled", "...");
			if(last> 5) html += this.makeBtn("", last);     
		} else if(cur>=(last-3)){
			if(last-4>1) html += this.makeBtn("", 1);
			if(last>7) html += this.makeBtn(" disabled", "...");
		    for(p=(last-4); p<=last; p++){
		    	if(p>0) html += (p == cur ? this.makeBtn(" current disabled", cur) : this.makeBtn("", p));
		    }
		} else {
			html += this.makeBtn(" default", 1);
			if(last>7) html += this.makeBtn(" disabled", "...");
			html += this.makeBtn("", cur-1); 
			html += this.makeBtn(" current disabled", cur); 
			html += this.makeBtn("", cur+1); 
		    if(last>7) html += this.makeBtn(" disabled", "...");
		    html += this.makeBtn("", last);
		}
		//next
		html += ((cur < pgobj[2] && pgobj[2] > 1) ? this.makeBtn(" wp-exclude-emoji next", "") : this.makeBtn(" wp-exclude-emoji next disabled", "")); //>
		//page numbering
		html += "<div class='pageNum'>"+stafflistpaths.pagelabel+": "+(pgobj[3])+" ("+(pgobj[4]+1)+" - "+(pgobj[5]+1)+" of "+(pgobj[0])+")</div>";
		return(html);
	};
}
/**************************************************************************************************
*	On Page Load, Insert the StaffList into the Container & Attach Listeners
**************************************************************************************************/
function build_stafflist() {

	jQuery("div.staffwrapper").each(function( index, item ){
		jQuery(this).attr("id","stafflist_"+index);
		var list = jQuery(this).find("div.staffdirectory table.stafflists");
		var form = jQuery(this).find("div.stafflistctl");
		var pagers = jQuery(this).find("div.staffdirectory div.staffpager");

		var data = {	action: 'ajax_build',
		        		rows: 	form.find("input[rel='sl_rows']").val() }
		
		//filter by subset, if needed
		if(form.find("input[rel='sl_subs']").length > 0) data.subset = form.find("input[rel='sl_subs']").val();

		//fetch stafflist
	 	jQuery.post(stafflistpaths.ajaxurl, data, function(response) {
			list.html(response.html);
			pagers.html(sl_pager.build(response.pager))
			      .find("p.pager:not(.disabled)").on("click",function(){
			    	  sl_page(jQuery(this));
			      });
			
	 	},'JSON');
	 	
	 	//prevent submit
	 	jQuery(item).find('.sl_search').keyup(function(e){
	 		var list = jQuery(this).closest("div.staffwrapper");
			var using = jQuery(this).val();
			//clearing the search
			var clear = jQuery(item).find(".search-clear");
			clear.css({display: (using.length > 0) ? "block" : "none" });
			clear.off().on("click", function(){
		 		clear.prev(".sl_search").val("");
		 		refine_stafflist(list.attr("id"),"");
			});
			//searching
	 		if(list.hasClass('ontype') && e.which==13) return(false);
	 		if(list.hasClass('onenter') && e.which!=13) return(false);
	 		refine_stafflist(list.attr("id"),using);
	 	});
	});
	
	//reapply tipsy listeners
	jQuery('.contactcard').tipsy({
		fade: false, 
		gravity: 'w',
		html: true,
		opacity: 1,
		live: true,
	   	title: function() {
	   		var s = JSON.parse(this.getAttribute('rel')); var html = "";
	   			for(key in s) html+= "<span class='sl_property'>"+key+"</span>: "+s[key]+"<br />";
	   			return "<p>"+html+"</p>";
			} 
	});
}
/**************************************************************************************************
*	Handle Sorting
**************************************************************************************************/
function sl_sort(el,col){
	var list = jQuery(el).closest("div.staffwrapper");
	var form = list.find("div.stafflistctl");
	var opt = form.find("input[rel='sl_sort']").val();
	opt = (opt == col) ? (opt.indexOf("-")<0 ? opt+"-" : opt) : col;
	form.find("input[rel='sl_sort']").val(opt);
	refine_stafflist(list.attr("id"));
	return;
}
/**************************************************************************************************
*	Handle Paging
**************************************************************************************************/
function sl_page(el){
	var list = el.closest("div.staffwrapper");
	var form = list.find("div.stafflistctl");

	if(el.hasClass('prev')){ page = (sl_current_page-1); }
	else if(el.hasClass('next')){ page = (sl_current_page+1); }
	else { page = el.html(); }

	if(page < 1 || page > sl_total_pages) return;
	
	list.find("div.staffdirectory p.pager").off("click");
	form.find("input[rel='sl_page']").val(page);
	refine_stafflist(list.attr("id"));
	return;
}
/**************************************************************************************************
*	Fetch Updated StaffList when changes are made to: sorting, paging, search
**************************************************************************************************/
function refine_stafflist(listid, using){
	var list = jQuery("#"+listid);
	//hide all tipsy
	jQuery('.tipsy').hide();
	
	var data = {
		action: 'ajax_build',
		rows: list.find("input[rel='sl_rows']").val(),
        sort: list.find("input[rel='sl_sort']").val(),
        page: list.find("input[rel='sl_page']").val(),
        search: using || list.find("input[rel='sl_search']").val()
	};
	
	//subsets
	if(list.find("input[rel='sl_subs']").length > 0) data.subset = list.find("input[rel='sl_subs']").val();

	//execute
 	jQuery.post(stafflistpaths.ajaxurl, data, function(response) {
		list.find("div.staffdirectory table.stafflists").html(response.html);
		list.find("div.staffdirectory div.staffpager").html(sl_pager.build(response.pager));
		list.find("p.pager").on("click",function(){
		    sl_page(jQuery(this));
		});
		
 	},'json');

 	return false;
}
/**************************************************************************************************
*	Done
**************************************************************************************************/