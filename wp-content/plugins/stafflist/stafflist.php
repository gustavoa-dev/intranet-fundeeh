<?php
/*
Plugin Name: StaffList
Plugin URI: http://wordpress.org/plugins/stafflist/
Description: A super simplified staff directory tool
Version: 2.6.3
Author: era404
Author URI: http://www.era404.com
License: GPLv2 or later.
Copyright 2014 ERA404 Creative Group, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/***********************************************************************************
*     Setup Plugin > Create Table
***********************************************************************************/
require_once("stafflist_setup.php"); global $wpdb;
// this hook will cause our creation function to run when the plugin is activated

register_activation_hook(   __FILE__, 'stafflist_install' );
register_deactivation_hook( __FILE__, 'stafflist_uninstall' );
register_uninstall_hook(    __FILE__, 'stafflist_uninstall' );

checkStafflistRoles();
	
/***********************************************************************************
*     Globals
***********************************************************************************/
define('RECORDS_PER_PAGE', 20);
define('STAFFLIST_URL', admin_url() . 'admin.php?page=stafflist');
$staffdb = 		$wpdb->prefix . "stafflist";
$staffmetadb = 	$wpdb->prefix . "stafflist_meta";


/***********************************************************************************
*     Setup Admin Menus
***********************************************************************************/
add_action( 'admin_menu', 'stafflist_admin_menu' );

function stafflist_admin_menu() {
	$page = add_menu_page('StaffList', 'StaffList', 'edit_stafflist', 'stafflist', 'stafflist_plugin_options', plugins_url('stafflist/img/admin_icon.png') );
	add_action( 'admin_print_styles-' . $page, 'stafflist_admin_styles' );
}
add_action( 'admin_init', 'stafflist_admin_init' );
function stafflist_admin_init() {
	wp_register_style( 'stafflist_admin', plugins_url('stafflist_admin.css', __FILE__) );
}
function stafflist_admin_styles() {
	wp_enqueue_style( 'stafflist_admin' );
}

/***********************************************************************************
*     Catch Export Requests before Output
***********************************************************************************/
function catchStafflistExportRequests(){
  $current_user = wp_get_current_user();
  if(isset($_GET['page']) && $_GET['page'] == 'stafflist' && 
  		isset($_GET['export']) && current_user_can( 'edit_stafflist' )) {
  			handleStafflistExports(); //before output
  }
}
add_action('init','catchStafflistExportRequests');

/***********************************************************************************
*     Preset Globals
*     If you wish to change the labels for Standard Columns, use the form called
*     Standard Column Titles on the StaffList plugin settings page.
*     "No Results Found." can be customized here, as well.
***********************************************************************************/
//get standard headers (for labeling)
$std = array("firstname"	=> "First Name", 
			 "lastname"		=> "Last Name", 
			 "email"		=> "Email", 
			 "department"	=> "Department", 
			 "phone"		=> "Phone");

//default number of rows
$rows = RECORDS_PER_PAGE;

//default messaging
$messaging = array(	"results" 	=> array("fdn" => "Results", 			"value" => "Results"),
					"noresults"	=> array("fdn" => "No Results", 		"value" => "No Results Found."), 
					"searchdir"	=> array("fdn" => "Search Directory", 	"value" => "Search Directory"), 
					"page"		=> array("fdn" => "Page", 				"value" => "Page"));

//get active columns & sort
$cols = array();

$spreadsheet_formats= array('application/vnd.ms-excel',
							'text/plain',
							'text/csv',
							'text/tsv',
							'text/comma-separated-values',
							'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'	
);

/***********************************************************************************
*     Add Required Scripts
***********************************************************************************/
add_action( 'admin_enqueue_scripts', 'setup_staff_admin_scripts' );
function setup_staff_admin_scripts(){
	//localize script 																		//jQuery + jQuery UI Sortable Required
	wp_register_script( 'stafflist-admin-script', plugins_url('/stafflist_admin.js', __FILE__), 	array('jquery', 'jquery-ui-sortable'), 1.0 );
	wp_localize_script( 'stafflist-admin-script', 'paths', 
		array( 'ajaxurl' 	=> admin_url( 'admin-ajax.php' ), 			// setting ajaxurl
			   'pluginurl' 	=> admin_url( 'admin.php?page=stafflist' )) // setting pluginurl
	); 	
	wp_enqueue_script( 'stafflist-admin-script'); 	
}

add_action( 'wp_ajax_ajax_update', 			'ajax_update' ); 	//for updates
add_action( 'wp_ajax_ajax_nextrow', 		'ajax_nextrow' ); 	//for updates
add_action( 'wp_ajax_stafflist_sort', 		'ajax_sort' ); 		//for sorting
add_action( 'wp_ajax_stafflist_rename',		'ajax_rename' ); 	//for custom titles


/***********************************************************************************
*     Build Admin Page
***********************************************************************************/
function stafflist_plugin_options() {
	if ( !current_user_can( 'edit_stafflist' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	global 	$wpdb,					//wpdb object
			$staffdb,$staffmetadb,	//stafflist tables
			$std, $cols, $rows,		//columns
			$spreadsheet_formats;	//csvs and the like
	
	$cols = $wpdb->get_results("SELECT id,name,colpos,
								CASE WHEN id<6 THEN name ELSE concat('col',id) END AS col
								FROM {$staffmetadb}
								WHERE active > 0
								ORDER BY colpos ASC, id ASC", ARRAY_A);
	
	//import handling (test CSV)
	$import_details = false;
	if(!empty($_FILES) && 0!=$_FILES['importfile']['size']){
		$uploads = wp_upload_dir();
		$ext = checkDatafile($_FILES['importfile']);
		$temp = $uploads['basedir'] . "/temp.{$ext}";
		if(!in_array($_FILES['importfile']['type'], $spreadsheet_formats)){ 
			showResults("Only CSV, XLS, XLSX imports are currently supported. This filetype was: {$_FILES['importfile']['type']}.",1); 
		} else {
			if(!move_uploaded_file($_FILES['importfile']['tmp_name'], $temp)) { 
				showResults("Could not upload the file to {$uploads['basedir']}. Check your site's directory permissions.", 1);
			} else {
				$import_details = stafflistImport(readCSVintoArray($temp));
			}
		}
	}

	//$_POST['action'] = "replace";
	if(!empty($_POST) && isset($_POST['action']) && isset($_POST['datafile']) && in_array($_POST['datafile'],array("csv","xls","xlsx"))){
		$ext = (string) $_POST['datafile'];
		$uploads = wp_upload_dir();
		$temp = $uploads['basedir'] . "/temp.{$ext}";
		if(!file_exists($temp)){
			showResults("Your datafile ({$temp}) could not be found. Check your site's directory permissions.", 1);
		} else {
			list($added,$notadded) = stafflistImport(readCSVintoArray($temp),true);
			$results = "Done.<br />Imported <strong>$added record".($added==1?"":"s")."</strong> into your StaffList Directory";
			if($notadded > 0) $results .= ", however <strong>$notadded record".($notadded==1?"":"s")."</strong> could not be imported.<br />Perhaps change the <em>clean</em> option and try the import again";
			$results .= ".";
			showResults( $results );
		}
	}
	
	//delete empty rows 
	deleteEmpty();

	//handle deleting records
	$cr = 0;
	if(isset($_GET['remove'])) {
		$q = "SELECT count(id) FROM $staffdb WHERE id=%s"; 
			$cr = $wpdb->get_var($wpdb->prepare($q, (int) $_GET['remove']) );
		$q = "DELETE FROM $staffdb WHERE id=%s"; 
			$wpdb->query($wpdb->prepare( $q, (int) $_GET['remove']) );
	}

	//handle sorting
	$s = $_GET['s'] = (!isset($_GET['s']) ? $cols[0]['id'] : $_GET['s']);
	$dir = strstr($s,"-")?"ASC":"DESC"; $default = false; $sort = false;
	//front-end sorting
	foreach($cols as $col){
		if(!$default) $default = $col['col'];
		if($col['id'] == str_replace("-","",$s)){ $sort = $col['col']; break; }
	}
	//back-end sorting
	if(!$sort && !is_numeric($s)){
		$sorts = array("last"=>"lastname","first"=>"firstname","dept"=>"department","email"=>"email");
		if(isset($sorts[ str_replace("-","",$s) ])) $sort = $sorts[ str_replace("-","",$s) ];
	}
	if(!$sort) $sort = $default;
	
	//handle search (use mb_strtolower, where available)
	$w = (isset($_GET['search']) && (string) trim($_GET['search'])!="" ? 
			(function_exists('imap_open') ? 
					mb_strtolower(wpesc($_GET['search']),'utf8') : 
					strtolower(wpesc($_GET['search']))) : 
			false);
	
	$where = ($w ? "WHERE LOWER(lastname) LIKE '%{$w}%' OR
						  LOWER(firstname) LIKE '%{$w}%' OR
						  LOWER(department)  LIKE '%{$w}%' OR
						  LOWER(email) LIKE '%{$w}%'" : "");
	//add nonstandard rows to search
	$nonstd = getNonstandardRows(1);
	if($w && !empty($nonstd)) foreach($nonstd as $k=>$v) $where.= " OR LOWER({$k}) LIKE '%{$w}%' ";
	
	//get count, first
	$count =  $wpdb->get_var("SELECT count(id) FROM $staffdb {$where}"); //echo "COUNT: $count<br /><br />";

	//handle paging
	$pg = array((int) $count, (int) $rows, (int) ceil($count/$rows));
	$p = $_GET['p'] = $pg[3] = (!isset($_GET['p']) || $_GET['p']<1 || $_GET['p']>$pg[2] ? 1 : $_GET['p']);
	$pg[4] = (int) ($pg[1]*$pg[3])-$pg[1];
	$pg[5] = (int) ($pg[4]+$pg[1])-1; if($pg[5]+1>$pg[0]) $pg[5]=($pg[0]-1);

	//build query
	$q =   "SELECT * FROM {$staffdb} {$where} ORDER BY {$sort} {$dir} LIMIT {$pg[4]}, {$pg[1]}"; //echo $q;
	$staff = $wpdb->get_results($q, ARRAY_A);									 		//myprint_r($staff);

	//build table 
/***********************************************************************************
*   Admin Page
***********************************************************************************/
	echo "<div id='stafflistwrap'>
			<h1>StaffList</h1>
		  	<div id='stafflist_instructions' data-expanded='0'>
		  		<h1>StaffList</h1>
			  	You can insert the StaffList directory into any WordPress page or post using the shortcode:<br /> 
			  		<tt>[stafflist]</tt><br /> 
				If you wish to change the default number of rows from {$rows} per page, use the shortcode attribute:<br /> 
			  		<tt style='color:#0073AA; font-weight:bold;'>[stafflist <span>rows=50</span>]</tt><br />  
				If you wish to use just a subset of records, use the shortcode attribute:<br /> 
			  		<tt style='color:#0073AA; font-weight:bold;'>[stafflist <span>subset=&quot;department:marketing&quot;</span>]</tt><br />
			  	If you have a column in your StaffList called &quot;Building&quot; and wish to show records from Building A and Building B, use:<br />
			  		<tt style='color:#0073AA; font-weight:bold;'>[stafflist <span>subset=&quot;building:a|b&quot;</span>]</tt><br />
			  	Searches are performed as you type and when the enter key is pressed.<br />
			  	If you wish to limit this function to just enter (or just type), use:<br />
			  		<tt style='color:#0073AA; font-weight:bold;'>[stafflist <span>on=&quot;enter&quot;</span>]</tt> or 
			  		<tt style='color:#0073AA; font-weight:bold;'>[stafflist <span>on=&quot;type&quot;</span>]</tt><br />

		  		<div class='sltabs'> 			
		  			<div class='sltab' style='float:right;'>Instructions</div>
		  		</div>
		  	</div>
		 ";
	
/***********************************************************************************
*     Directory
***********************************************************************************/
	echo "<div id='warning' class='orange' style='display:".($cr>0?"block":"none").";'>".(($cr>0)?"<strong>NOTE:</strong> [ $cr ] Staff Record removed.":"")."</div>";
	echo "<input type='text' id='searchdirectory' name='searchdirectory' value='{$w}' placeholder='Search Directory' />";
	
	global $stafflisturl; $stafflisturl = STAFFLIST_URL . ($w ? "&search={$w}" : "");
	echo renderAdminPager($pg);
	
	echo "<div style='width:98%; padding: 6px; margin: 0; border: 3px solid #cfcfcf; overflow: auto;'>
		  <table id='stafflists' style='border:1px solid #E8E8E8;'>";
	echo "<thead id='stafflisthead'><tr>
			<th><a href='{$stafflisturl}&s=last' title='Sort by Last Name A-Z' class='sort_a ".($_GET['s']=='last'?'selected':'')."'><span>Ascending</span></a> Last Name
				<a href='{$stafflisturl}&s=last-' title='Sort by Last Name Z-A' class='sort_d ".($_GET['s']=='last-'?'selected':'')."'><span>Descending</span></a>
			</th><th>&nbsp;</th>
			<th><a href='{$stafflisturl}&s=first' title='Sort by First Name Ascending' class='sort_a ".($_GET['s']=='first'?'selected':'')."'><span>Ascending</span></a> First Name
				<a href='{$stafflisturl}&s=first-' title='Sort by First Name Descending' class='sort_d ".($_GET['s']=='first-'?'selected':'')."'><span>Descending</span></a>
			</th><th>&nbsp;</th>
			<th><a href='{$stafflisturl}&s=dept' title='Sort by Department Ascending' class='sort_a ".($_GET['s']=='dept'?'selected':'')."'><span>Ascending</span></a> Department
				<a href='{$stafflisturl}&s=dept-' title='Sort by Department Descending' class='sort_d ".($_GET['s']=='dept-'?'selected':'')."'><span>Descending</span></a>
			</th><th>&nbsp;</th>
			<th><a href='{$stafflisturl}&s=email' title='Sort by Email Address Ascending' class='sort_a ".($_GET['s']=='email'?'selected':'')."'><span>Ascending</span></a> Email Address
				<a href='{$stafflisturl}&s=email-' title='Sort by Email Address Descending' class='sort_d ".($_GET['s']=='email-'?'selected':'')."'><span>Descending</span></a>
			</th><th>&nbsp;</th>
			<th>Phone / Ext</th><th>&nbsp;</th>";
	$activeCols = getNonstandardRows();
	echo "</tr></thead>";
	
	$i=0;
	foreach($staff as $k=>$s){
		$del = "<a href='{$stafflisturl}&remove={$s['id']}&p={$p}&s={$_GET['s']}' class='remove'
				   onclick='javascript:if(!confirm(\"Are you sure you want to delete this staff record?\")) return false;'
				   title='Permanently Delete This Staff Record' target='_self'
				/></a>";
		$i++;
		
		echo "<tr class='row' id='staff_{$s['id']}'>
		<td><input type='text' id='lastname:{$s['id']}' value='".esc_attr($s['lastname'])."' autocomplete='Off' /></td><td></td>
		<td><input type='text' id='firstname:{$s['id']}' value='".esc_attr($s['firstname'])."' autocomplete='Off' /></td><td></td>
		<td><input type='text' id='department:{$s['id']}' value='".esc_attr($s['department'])."' autocomplete='Off' /></td><td></td>
		<td><input type='text' id='email:{$s['id']}' value='".esc_attr($s['email'])."' autocomplete='Off' /></td><td></td>
		<td><input type='text' id='phone:{$s['id']}' value='".esc_attr($s['phone'])."' autocomplete='Off' /></td><td></td>";
		for($i = 1; $i <= $activeCols; $i++){
			$col = $i+5;
			$key = "col{$col}";
			echo "<td><input type='text' id='col{$col}:{$s['id']}' value='".esc_attr($s[$key])."' autocomplete='Off' /></td>";
			if($i < $activeCols) echo "<td></td>";
		}
		echo "<td>{$del}</td></tr>";
	}
	
	echo "</table></div><br />";
	echo "<div style='clear:both;'>".renderAdminPager($pg)."
			<a href='javascript:void(0);' title='Add New Staff Record' id='stafflist_new' name='stafflist_new'>Add New Staff Record</a>
		  <span class='exports'>Export 
			<a href='javascript:void(0);' title='Export All Staff Records to XLSX' class='stafflist_export' rel='xls' name='stafflist_export'>.XLSX</a>
			<a href='javascript:void(0);' title='Export All Staff Records to CSV' class='stafflist_export' rel='csv' name='stafflist_export'>.CSV</a>
		  </span>
		  </div>";
	
/***********************************************************************************
*     Import from CSV
***********************************************************************************/
	echo "<div style='clear:both;'>
	  <h2>Import Staff from Spreadsheet</h2>";
	
 	//is an import being performed?
	if($import_details){
		echo "<form id='importStaff' method='post'>".($temp?"<input type='hidden' name='datafile' value='{$ext}' />":"")."
				<table>
				<tr><td colspan='2'><strong>Your uploaded ".strtoupper($ext)." contains ( {$import_details[2]} ) rows and the following columns</strong>:</td></tr>
				<tr><td width='200'>Standard Columns:</td><td><span>".
					implode("</span><span>",$import_details[0])."</span></td></tr>";
		
		//are there nonstandard columns?
		if(!empty($import_details[1])) {
					echo "<tr><td>Non-standard Columns:</td><td><span>".
					implode("</span><span>",$import_details[1])."</span></td>";
		}
		echo "  </tr>
				<tr><td colspan='2'><br /><strong>";
		
		//is the import permitted?
		if($import_details[3]>0){	echo "Do you wish to append the directory, or replace the directory with the imported records?";
		} else {					echo "Because your existing columns don't match the new CSV, you will only have the option to replace the directory."; }
		
		//provide the switches for append/vs/replace
		echo "</strong></td></tr>
				<tr><td><input type='radio' name='action' value='append' ".(($import_details[3]>0) ? "checked='checked'" : "disabled='disabled'")." />Append &nbsp; &nbsp;
				        <input type='radio' name='action' value='replace' />Replace</td>
					<td>&nbsp;</td></tr>
				<tr><td colspan='2'><br /><strong>Do you wish to attempt to clean the imported values before import?</strong></td></tr>
				<tr><td><input type='radio' name='clean' value='1' checked='checked' />Clean &nbsp; &nbsp;
				        <input type='radio' name='clean' value='0' />Don't Clean</td>
					<td><input type='submit' name='submit' value='Do the Import' /></td></tr>
			</table>
			</form>
			<br /><strong>Or, import a different CSV:</strong>";
	}
	
	//import instructions
	$doccsv = "<a href='https://en.wikipedia.org/wiki/Comma-separated_values' title='What is a .csv File?' target='_blank'>CSV</a>";
	$docxls = "<a href='https://en.wikipedia.org/wiki/Microsoft_Excel' title='What is an .xls File?' target='_blank'>XLS</a>";
	$docxlsx= "<a href='https://en.wikipedia.org/wiki/Microsoft_Excel' title='What is an .xlsx File?' target='_blank'>XLSX</a>";
	
	$svgpath = plugins_url('stafflist/img/url.svg');
	$urlsvg = "<svg><use xlink:href='{$svgpath}#url'/></svg>";
	$urlhtml = "<span class='urlsample'>{$urlsvg}</span>";
	echo "<form id='importStaff' method='post' enctype='multipart/form-data'>
			<table>
			<tfoot>
				<tr>
					<td colspan='2'>You may import a [ {$doccsv}, {$docxls}, {$docxlsx} ] with up to 14 additional columns apart from the standard ones: <strong>firstname, lastname, email, phone, department</strong>.".
									"<p><strong>Using Links</strong>: If your spreadsheet contains a column called <strong>URL</strong>, <strong>Link</strong>, or <strong>Profile</strong>, 
									it will be depicted as an icon {$urlhtml} with the column value used as a link.</td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td><input type='file' name='importfile' />	</td>
					<td><input type='submit' name='submit' value='Upload' /></td>
			   </tr>
			</tbody>

		</table>
		</form>";
	$cols = $wpdb->get_results( "SELECT id,name,active FROM {$staffmetadb} 
								 WHERE name IS NOT NULL 
								 ORDER BY colpos,id", ARRAY_A);
	
/***********************************************************************************
*     Column Chooser & Ordering
***********************************************************************************/
	echo "<div style='clear:both;'>
	  	<h2>Choose and Order your Columns</h2>
		<h4>Active Columns</h4>These columns will be visible in the StaffList table, in the order you choose.
	
	<ul id='sortable1' class='connectedSortable'>";
	foreach($cols as $col){
		if($col['active']>0) {
			$cname = ($col['id']>5 ? $col['name'] : $std[ $col['name'] ]);
			echo "<li class='ui-state-default' id='col{$col['id']}'>{$cname}</li>";
		}
	}
	echo "</ul>
	
	<h4>Other Columns</h4>These columns will only be visible in the contact card, but will be searchable like the others.
	<ul id='sortable2' class='connectedSortable'>";
	foreach($cols as $col){
		if($col['active']<0) {
			$cname = ($col['id']>5 ? $col['name'] : $std[ $col['name'] ]);
			echo "<li class='ui-state-highlight' id='col{$col['id']}'>{$cname}</li>";
		}
	}
	echo "</ul>";


/**************************************************************************************************
*	Set some custom labels for Standard Columns
**************************************************************************************************/
	$titles = array(); //get the custom labels from wp_options table;
	foreach($std as $fd => $fdn){ $title = get_option("stafflist_rename_{$fd}"); $titles[$fd] = ($title ? esc_attr($title) : ""); }
	//also get custom labels for messaging
	foreach(array("results","noresults","searchdir","page") as $fd){ $title = get_option("stafflist_rename_{$fd}"); $titles[$fd] = ($title ? esc_attr($title) : ""); }
	//provide a form for updating these
	echo "<div style='clear:both; margin-bottom:60px; overflow: auto;'>
	<h2>Custom Labels & Messages</h2>

	<p>StaffList provides easy means for personalizing the labels shown above standard record columns.<br /> 
	For example, administrators might prefer the label <strong><em>Surname</em></strong> to the default <strong><em>Last Name</em></strong> label.<br />
	This form is provided for these quick customizations to your StaffList column labels, as well as some of the messaging.</p>

	<div id='response_rename' class='green' style='display:none;'></div>
	<form id='stafflistRename'>
	<table>
	<tr><td><label for='stafflist_rename_firstname'>First Name</td><td><input type='text' id='stafflist_rename_firstname' name='stafflist[rename][firstname]' value='{$titles['firstname']}' autocomplete='Off' /></label></td>
		<td><label for='stafflist_rename_phone'>Phone</td><td><input type='text' id='stafflist_rename_phone' name='stafflist[rename][phone]' value='{$titles['phone']}' autocomplete='Off' /></label></td></tr>
	<tr><td><label for='stafflist_rename_lastname'>Last Name</td><td><input type='text' id='stafflist_rename_lastname' name='stafflist[rename][lastname]' value='{$titles['lastname']}' autocomplete='Off' /></label></td>
		<td><label for='stafflist_rename_email'>Email</td><td><input type='text' id='stafflist_rename_email' name='stafflist[rename][email]' value='{$titles['email']}' autocomplete='Off' /></label></td></tr>
	<tr><td><label for='stafflist_rename_department'>Department</td><td><input type='text' id='stafflist_rename_department' name='stafflist[rename][department]' value='{$titles['department']}' autocomplete='Off' /></label></td>
		<td></td><td></td></tr>
	<tr class='renameDivider'><td colspan='4'><hr /></td></tr>
	<tr><td><label for='stafflist_rename_results'><em>Results</em></td><td><input type='text' id='stafflist_rename_results' name='stafflist[rename][results]' value='{$titles['results']}' autocomplete='Off' /></label></td>
		<td><label for='stafflist_rename_noresults'><em>No Results</em></td><td><input type='text' id='stafflist_rename_noresults' name='stafflist[rename][noresults]' value='{$titles['noresults']}' autocomplete='Off' /></label></tr>
	<tr><td><label for='stafflist_rename_searchdir'><em>Search Directory</em></td><td><input type='text' id='stafflist_rename_searchdir' name='stafflist[rename][searchdir]' value='{$titles['searchdir']}' autocomplete='Off' /></label></td>
		<td><label for='stafflist_rename_page'><em>Page</em></td><td><input type='text' id='stafflist_rename_page' name='stafflist[rename][page]' value='{$titles['page']}' autocomplete='Off' /></label></td></tr>
	<tr class='renameDivider'><td colspan='4'><hr /></td></tr>
	<tr><td></td><td></td>
		<td></td><td><div style='clear:both; text-align:right;'><input type='button' id='stafflistRenameColumns' value='Customize Labels' /></td></tr>
	</table>
	</form></div>";
	
/***********************************************************************************
*     End
***********************************************************************************/
	echo "</div></div>"; //stafflist wrapper

?>
<!-- paypal donations, please -->
<div class="footer">
	<div class="donate" style='display:none;'>	
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="464DEX6U6DL5N">
		<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" class="donate">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		<p>If <b>ERA404's StaffList WordPress Plugin</b> has made your life easier and you wish to say thank you, use the Secure PayPal link above to buy us a cup of coffee.</p>
	</div>
	<div class="bulcclub">
		<a href="https://www.bulc.club/?utm_source=wordpress&utm_campaign=stafflist&utm_term=stafflist" title="Bulc Club. It's Free!" target="_blank"><img src="<?php echo plugins_url('stafflist/img/bulcclub.png');?>" alt="Join Bulc Club. It's Free!" /></a>
		<p>For added protection from malicious code and spam, use Bulc Club's unlimited 100% free email forwarders and email filtering to protect your inbox and privacy. <strong><a href="https://www.bulc.club/?utm_source=wordpress&utm_campaign=stafflist&utm_term=stafflist" title="Bulc Club. It's Free!" target="_blank">Join Bulc Club &raquo;</a></strong></p>
	</div>
</div>
</div><!--end donations form -->
<footer><small>See more <a href='http://profiles.wordpress.org/era404/' title='WordPress plugins by ERA404' target='_blank'>WordPress plugins by ERA404</a> or visit us online: <a href='http://www.era404.com' title='ERA404 Creative Group, Inc.' target='_blank'>www.era404.com</a>. Thank you for using StaffList.</small></footer>
<?php 
}
/**************************************************************************************************
*	Render a Dynamic Pager (backend)
**************************************************************************************************/
$stafflisturl = "";
function renderAdminPager($pg){
	$cur = $pg[3]; 
	$last = $pg[2];
	global $stafflisturl; //includes search

	//one page or less?
	if($pg[0]<1 || $last<2) return("");
	//previous
	$html = ($cur > 1 ? "<p class='pager'><a href='{$stafflisturl}&p=".($cur-1)."&s={$_GET['s']}'>Previous </a></p>" : ""); //<
	//pages
	if($cur<=3){
		for($page=1; $page<=min($last,5); $page++) $html .= ($page == $cur ? renderAdminPage(" current disabled", $cur) : renderAdminPage("", $page));
		if($last>5) $html .= renderAdminPage(" disabled", "...") . renderAdminPage("", $last);
	} elseif($cur>=($last-3)){
		if(($last-4)>1) $html .= renderAdminPage("", 1);
		if($last>=7) $html .= renderAdminPage(" disabled", "...");
		for($page=($last-4); $page<=$last; $page++) $html .= ($page == $cur ? renderAdminPage(" current disabled", $cur) : renderAdminPage("", $page));
	} else {
		$html .= renderAdminPage(" default", 1);
		if($last>7) $html .= renderAdminPage(" disabled", "...");
		$html .= renderAdminPage("", $cur-1);
		$html .= renderAdminPage(" current disabled", $cur);
		$html .= renderAdminPage("", $cur+1);
		if($last>7) $html .= renderAdminPage(" disabled", "...");
		$html .= renderAdminPage("", $last);
	}
	//next
	$html .= (($cur < $pg[2] && $pg[2] > 1) ? "<p class='pager'><a href='{$stafflisturl}&p=".($cur+1)."&s={$_GET['s']}'> Next</a></p>" : ""); //>
	//page numbering
	$html .= "<div class='pageNum'>Page: ".($pg[3])." ( ".($pg[4]+1)."-".($pg[5]+1)." of ".($pg[0])." )</div>";
	return($html);
}
function renderAdminPage($style, $page){
	global $stafflisturl; //includes search
	return("<p class='pager{$style}'>".(is_numeric($page)?"<a href='{$stafflisturl}&p={$page}&s={$_GET['s']}'>{$page}</a>":$page)."</p>"); 
}
/***********************************************************************************
*     Frontend Helper functions
************************************************************************************
	*     Build Directory
	********************************************************************************/
	function ajax_build(){
		unset($_POST['action']);
		global 	$wpdb,					//wpdb obj
				$staffdb,$staffmetadb, 	//stafflist tables
				$std, $cols, $rows,		//columns
				$messaging;				//no results

		$cols = $wpdb->get_results("SELECT id,name,colpos,
									CASE WHEN id<6 THEN name ELSE concat('col',id) END AS col
									FROM {$staffmetadb}
									WHERE active > 0
									ORDER BY colpos ASC, id ASC", ARRAY_A);
		
		//sort / page / search
		$limit = (empty($_POST) ? array("sort"=>$cols[0]['id'],"page"=>1,"search"=>"") : $_POST);
		if(!isset($limit['sort']) || $limit['sort'] == "") $limit['sort'] = $cols[0]['id'];
		if(!isset($limit['search'])) $limit['search'] = "";
		
		//handle subsets
		$where = "WHERE 1=%d "; $args = array(1);
		if(isset($_POST['subset']) && $subset = checkSubset($_POST['subset'])){
			list($sscol,$ssvals) = explode(":",trim($subset));
			if($sscol!="" && $ssvals!=""){
				$ssvals = explode("|",$ssvals);
				foreach($ssvals as $k=>$ssval){
					$ssvals[$k] = "$sscol=%s";
					$args[] = $ssval;
				}
				$where .= " AND ( ".implode(" OR ", $ssvals)." ) \n";
			}
		}
		
		//handle searching
		$w = (@!isset($limit['search']) || trim($limit['search']=="") ? false : wpesc($limit['search']));
		if($w){
			$wparts = explode(" ", $w);
			foreach($wparts as $wpart){
				$likew = '%' . $wpdb->esc_like((string) trim($wpart)) . '%';
				$args = array_merge($args, array($likew, $likew, $likew, $likew, $likew));
				$where .= "AND (	lastname  LIKE %s OR  
									firstname LIKE %s OR  
									department LIKE %s OR
									phone LIKE %s OR
									email LIKE %s ";
				//add nonstandard rows to search
				$nonstd = getNonstandardRows(1);
				foreach($nonstd as $k=>$v) {
					$where.= " OR $k LIKE %s ";
					$args[] = $likew;
				}
				$where .= ") \n";
			}
		}
		//echo "WHERE: <br /><br />$where<br /><br />";

		//handle sorting
		$dir = $limit['dir'] = strstr($limit['sort'],"-")?"DESC":"ASC"; $default = false; $sort = false;
		foreach($cols as $col){
			if(!$default) $default = $col['col'];
			if($col['id'] == str_replace("-","",$limit['sort'])){ $sort = $col['col']; break; }
		}
		if(!$sort) $sort = $default;

		//total matching records (for pager)
		$query = $wpdb->prepare("SELECT count(id) FROM $staffdb {$where}", $args); //echo $query;
		$count = $wpdb->get_var( $query );
		//echo "COUNT: $count";
		
		//if $_POST['rows'] use that instead of default RECORDS_PER_PAGE;
		if(isset($_POST['rows']) && is_numeric($_POST['rows']) && $_POST['rows'] > 0 && $_POST['rows'] <= 100) $rows = $_POST['rows'];
		
		//handle paging
		$pg = array((int) $count, (int) $rows, (int) ceil($count/$rows));
		$pg[3] = (int) (!isset($limit['page']) || $limit['page']<1 || $limit['page']>$pg[2] ? 1 : $limit['page']);
		$pg[4] = (int) ($pg[1]*$pg[3])-$pg[1];
		$pg[5] = (int) ($pg[4]+$pg[1])-1; if($pg[5]+1>$pg[0]) $pg[5]=($pg[0]-1);
		$limit['page'] = $pg;
		//echo "SORT: $sort DIR: $dir LIMIT: {$limit['sort']} ";
		
		//handle customized column titles
		$titles = array(); //get the custom titles from wp_options table;
		foreach($std as $fd => $fdn){ $title = get_option("stafflist_rename_{$fd}"); $titles[$fd] = ($title ? $title : ""); }
			
		//svg refs
		$svgpath = plugins_url('stafflist/img/url.svg');
		$urlsvg = "<svg><use xlink:href='{$svgpath}#url'/></svg>";
		$svgpath = plugins_url('stafflist/img/card.svg');
		$cardsvg = "<svg><use xlink:href='{$svgpath}#card'/></svg>";
		
		//begin constructing html
		$html = "<table class='stafflists'>\n".
				"\t<thead class='stafflisthead'><tr>\n".
					"\t\t<th style='width:48px;' class='hdrcard'>\n".
					"\t\t\t<svg aria-hidden='true'><title>StaffList Contact Card</title><use xlink:href='{$svgpath}#card'/></svg>\n".
					"\t\t</th>\n";

		foreach($cols as $col){
			$colname = (array_key_exists($col['name'], $std) ? 	//standard/nonstandard?
							(""!=$titles[ $col['name'] ] ? 		//standard customized?
									$titles[ $col['name'] ] : 	//use standard customized.
									$std[$col['name']]) : 		//use standard
							esc_html($col['name'])				//use nonstandard
						);
			$colname = esc_attr(str_replace(" ","&nbsp;", $colname));
			$styles = array("a"=>"","d"=>"");
			
			//selected sort
			if($col['id']==str_replace("-","",$limit['sort'])) $styles[($limit['dir']=="DESC"?"d":"a")] = "selected";
			
			//ignore sort on URL column
			if(in_array(strtolower($colname),array("url","profile","link"))){
				$html .= 	"\t\t<th>{$colname}</th>\n";
			} else {
				$html .= 	"\t\t<th rel='{$col['id']}'>\n".
							"\t\t\t<span class='sort sort_a {$styles['a']}'>Ascending</span>\n".
							"\t\t\t<a href='javascript:void(0);' onclick='sl_sort(this,{$col['id']});' ".
							"class='sort' title='Sort by {$colname}'>{$colname}</a>\n".
							"\t\t\t<span class='sort sort_d {$styles['d']}'>Descending</span>\n".
							"\t\t</th>\n";
			}
		}
		$html .= "\t</tr></thead>\n\t<tbody>\n";
	
		//build query
		$q =   "SELECT * FROM {$staffdb} {$where} ORDER BY {$sort} {$dir} LIMIT {$pg[4]}, {$pg[1]}"; //echo $q;
		$query = $wpdb->prepare($q,$args); //echo $query;
		$staff = $wpdb->get_results( $query, ARRAY_A);
		if(count($staff)<1){
			$none = get_option("stafflist_rename_noresults"); //use custom message, if set
			$none = ( $none ? esc_attr($none) : $messaging['noresults']['value'] );
			$html .= "<tbody><tr><td colspan='".(count($cols)+1)."' class='stafflist_noresults'>{$none}</td></tr>";
		}
		
		//iterate
		foreach($staff as $i=>$s){
			if($w){ //stylize matched characters
				$wparts = explode(" ", $w);
				foreach($wparts as $wpart){
					$find = '/('.$wpart.')/i';
					$repl = '**$1**';
					foreach($s as $sk=>$sv) $s[$sk] = preg_replace($find,$repl,$sv);
				}
				foreach($s as $sk=>$sv) $s[$sk] = preg_replace("/(\*){2}([^\*]*)?(\*){2}/", "<strong>$2</strong>", $sv);
			}
			$t = array(); //cleanup tipsy obj

			//tipsy object
			$t = createTipsyObject($s);
			
			//column data
			$html .= 	"\t\t<tr><td><p class='contactcard' rel='".json_encode($t)."'>{$cardsvg}</p></td>\n";
			foreach($cols as $col){
				switch(strtolower($col['name'])){
					case "email":
					//special case for email to use mailto hyperlink
						if(""!=trim($s['email']) && strstr($s['email'],"@")){
							$cleanemail = strip_tags($s['email']);
							$cleantitle = "Email ".esc_html(strip_tags($s['firstname']." ".$s['lastname']));
							$html .= "\t\t\t<td><a href='mailto:{$cleanemail}' title='{$cleantitle}'>{$s['email']}</a></td>\n";
						} else {
							$html .= "\t\t\t<td>{$s['email']}</td>\n";
						}
						break;
					case "phone":
					//special case for phone to use tel hyperlink
						if(""!=trim($s['phone']) && ""!=($cleannumber = preg_replace('/[^0-9]/', '', $s['phone']))){
							$cleantitle = "Call ".esc_html(strip_tags($s['firstname']." ".$s['lastname']));
							$html .= "\t\t\t<td><a href='tel:{$cleannumber}' title='{$cleantitle}'>{$s['phone']}</a></td>\n";
						} else {
							$html .= "\t\t\t<td>{$s['phone']}</td>\n";
						}
						break;
					case "url":
					case "profile":
					case "link":
					//special case for url to use dashicon instead
						$hasUrl = true;
						$urlcol = $col['col'];
						$cleanurl = strip_tags(stripslashes(preg_replace('/\s+/','',trim($s[$urlcol]))));
						$cleanurl = filter_var($cleanurl, FILTER_VALIDATE_URL);
						$cleanurl = esc_url($cleanurl);
						if(""!=$cleanurl){
							$cleantitle = "Link to: ".esc_html(strip_tags($s['firstname']." ".$s['lastname']));
							$html .= "\t\t\t<td><a href='{$cleanurl}' title='{$cleantitle}' target='_blank'>{$urlsvg}</a></td>\n";
						} else {
							$html .= "\t\t\t<td>&nbsp;</td>\n";
						}
						break;
					//all others
					default: 
						$html .= "\t\t\t<td>{$s[$col['col']]}</td>\n";
						break;
				}
			}
			$html .= "\t\t</tr>\n";
		}
		$html .= "</tbody></table>\n";
		header('Content-type: application/json');
		die(json_encode(array("html"=>$html,"pager"=>$pg)));
	}
	
	/*******************************************************************************
	*     Include Javacripts
	********************************************************************************/
	add_action( 'wp_enqueue_scripts', 'setup_staff_scripts' );
	function setup_staff_scripts() {
		global $messaging;
		
		//custom messaging
		$pagelabel = 	get_option("stafflist_rename_page"); //use custom message, if set
		$pagelabel = 	( !empty($pagelabel) ? esc_attr($pagelabel) : $messaging['page']['value'] );
		$results = 		get_option("stafflist_rename_results"); //use custom message, if set
		$results = 		( !empty($results) ? esc_attr($results) : $messaging['results']['value'] );
		
		//localize script
		wp_register_script('stafflist-script', plugins_url('/stafflist.js', __FILE__), array('jquery'), 1.0 );
		wp_localize_script('stafflist-script', 'stafflistpaths', 
				array( 	'ajaxurl' 	=> admin_url( 'admin-ajax.php' ), 	//setting pluginurl
						'pagelabel'	=> esc_attr($pagelabel),		 	//customized messaging, if set
						'results'	=> esc_attr($results)
				)	
		); 
		wp_enqueue_script( 'stafflist-script' ); 	// jQuery will be included automatically
		wp_enqueue_script( 'tipsy-script', plugins_url('/lib/tipsy/tipsy.js', __FILE__), array('jquery'), 1.3 ); 		// jQuery will be included automatically
	}
	
	add_action('wp_ajax_ajax_build', 'ajax_build');
	add_action('wp_ajax_nopriv_ajax_build', 'ajax_build');

	/*******************************************************************************
	*     Create Tipsy Object
	********************************************************************************/
	$allcols = false;
	function createTipsyObject($s){
		global $wpdb, $staffmetadb, $std, $allcols;
		//columns to use
		if(!$allcols){
			$nonstd = $wpdb->get_results("SELECT id,name,colpos,
										  CASE WHEN id<6 THEN name ELSE concat('col',id) END AS col
										  FROM {$staffmetadb} ORDER BY colpos ASC", ARRAY_A);
			foreach($nonstd as $col) {
				if(isset($std[$col['col']])){ $allcols[$col['col']] = $std[$col['col']]; }
				else { $allcols[$col['col']] = esc_html($col['name']); }
			}
		}
		//customized labels?
		foreach($std as $fd => $fdn){ 
			$title = get_option("stafflist_rename_{$fd}"); 
			if($title) $allcols[$fd] = esc_attr($title); 
		}
		//build the object
		foreach($allcols as $ck=>$ct){
			if(trim($ct)=="") continue; //skip unassigned columns
			if(array_key_exists($ck, $s) && trim($s[ $ck ])!="" && !is_null($s[ $ck ])){
				$t[ $ct ] = str_replace("'", '&apos;', trim($s[ $ck ]));
			}
		}
		//return
		return($t);
	}
/**************************************************************************************************
*	AJAX Functions
***************************************************************************************************
	*	StaffList Admin > Updates individual record fields
	***********************************************************************************************/
	function ajax_update() {
		global $wpdb,$staffdb,$staffmetadb;
		$valid = array("firstname","lastname","phone","email","department");
		$nonstd = $wpdb->get_results("SELECT * FROM {$staffmetadb} WHERE name IS NOT NULL AND id>5", ARRAY_A);
		foreach($nonstd as $col) $valid[] = "col".$col['id'];

		//build query from passed vars
		$fval = 	(string) stripslashes($_POST['fval']);
		$fname = 	(string) stripslashes($_POST['fname'][0]);
		$id = 		(int) 	 stripslashes($_POST['fname'][1]);
		if(!in_array($fname, $valid)) die();
	
		$q = "UPDATE $staffdb SET {$fname} = %s WHERE id = %d";
		$pq = $wpdb->prepare( $q, $fval, $id );								//echo $pq;
		$wpdb->query( $pq );
		die(); // stop executing script
	}
	/**********************************************************************************************
	*	StaffList Admin > Set some custom labels for Standard Columns (and "No Results Found." message)
	***********************************************************************************************/
	function ajax_rename(){
		global $wpdb, $std, $messaging; parse_str($_POST['data']); $resp = array();

		//rename standard column labels
		foreach($std as $fd => $fdn){
			if(isset($stafflist['rename'][$fd])){
				$title = sanitize_option("stafflist_rename_{$fd}", (string) stripslashes(trim($stafflist['rename'][$fd])));
				if(update_option("stafflist_rename_{$fd}", $title)){
					if(""!=$title && strlen($title)>0){
						$resp[] = "<strong>{$fdn}</strong> was renamed <em>{$title}</em>.";
					} else {
						$resp[] = "<strong>{$fdn}</strong> uses the default title <em>{$std[$fd]}</em>.";
					}
				}
			}
		}
		//rename some messaging too
		foreach($messaging as $fd => $f){
			if(isset($stafflist['rename'][$fd])){
				$title = sanitize_option("stafflist_rename_{$fd}", (string) stripslashes(trim($stafflist['rename'][$fd])));
				if(update_option("stafflist_rename_{$fd}", $title)){
					if(""!=$title && strlen($title)>0){
						$resp[] = "<strong>{$f['fdn']}</strong> was customized to <em>{$title}</em>.";
					} else {
						$resp[] = "<strong>{$f['fdn']}</strong> uses the default message <em>{$f['value']}</em>.";
					}
				}
			}
		}
		header('Content-type: application/json');
		die(json_encode(array("msg" => implode("<br />",$resp))));
	}
	
/**************************************************************************************************
*	Admin Helper Functions
***************************************************************************************************
	*	StaffList Admin > Add a Blank Row to the Full Directory
	***********************************************************************************************/
	function ajax_nextrow() {
		global $wpdb,$staffdb;
		//get last ID (for inserts)
		$nextid = $wpdb->get_var("SELECT (max(id)+1) FROM {$staffdb} ORDER BY id ASC LIMIT 1");
		$wpdb->insert( $staffdb, array( 'id'=>$nextid ));
		exit($nextid);
	}
	/**********************************************************************************************
	*	StaffList Admin > Require that Standard Fields are Completed
	***********************************************************************************************/
	function deleteEmpty() {
		global $wpdb,$staffdb;
		$dq = "DELETE FROM {$staffdb} WHERE ( firstname='' OR firstname IS NULL )
					                    AND ( lastname='' OR lastname IS NULL )
									    AND ( department='' OR department IS NULL )
									    AND ( phone='' OR phone IS NULL )
									    AND ( email='' OR email IS NULL )";
		$wpdb->query($dq);
	}
	/**********************************************************************************************
	*	StaffList Admin > Error/Success
	***********************************************************************************************/
	function showResults($results, $iserror=false){
		echo "<div id='sl_results' class='".($iserror?"sl_error":"")."'>{$results}</div>";
		return;
	}
	/**********************************************************************************************
	*	StaffList Admin > Inserts a New Record (standard fields)
	***********************************************************************************************/
	function getNonstandardRows($arrayonly=false){
		global $wpdb, $staffmetadb;
		$nonstd = $wpdb->get_results("SELECT * FROM {$staffmetadb} WHERE id > 5 AND name IS NOT NULL", ARRAY_A);
	
		if($arrayonly){
			$return = array();
			foreach($nonstd as $k=>$v) $return["col{$v['id']}"]=$v['name'];
			return($return);
		}
		$active = 0;
		foreach($nonstd as $k=>$col) {
			echo "<th>{$col['name']}</th><th>&nbsp;</th>";
			$active++;
		}
		return($active);		
	}
/**************************************************************************************************
*	Import Functions
**************************************************************************************************/
function stafflistImport($data, $perform = false){
	global $wpdb, $staffdb, $staffmetadb;
	$added 	= 0; $notadded = 0;
	$total 	= (count($data)-1);							//myprint_r($data);

	//define valid columns
	$std 	= array();
	$nonstd = array();

	//capture header row & check validity
	$cols = $data[0]; 									//myprint_r($cols);

	$cells = array();
	foreach($cols as $cnum=>$cname){
		$colname = strtolower(trim(str_replace(" ","_",$cname)));
		if($colname=="") continue;

		//firstname
		if(in_array($colname, array("first_name","firstname","fname","first","fn"))){
			$std[$cnum]="firstname"; $cells[$cnum]="firstname"; continue; }
		//lastname
		if(in_array($colname, array("last_name","lastname","lname","last","ln"))){
			$std[$cnum]="lastname"; $cells[$cnum]="lastname"; continue; }	
		//phone
		if(in_array($colname, array("phone_number","phonenumber","telephone","phone","tel"))){
			$std[$cnum]="phone"; $cells[$cnum]="phone"; continue; }
		//email
		if(in_array($colname, array("email_address","emailaddress","email"))){
			$std[$cnum]="email"; $cells[$cnum]="email"; continue; }	
		//department
		if(in_array($colname, array("department_name","departmentname","department","dept"))){
			$std[$cnum]="department"; $cells[$cnum]="department"; continue; }

		$nonstd[$cnum] = wpesc($cname);
		$cells[$cnum]  = wpesc($cname);	

	}
																				//myprint_r($std); myprint_r($nonstd);

	$set = $wpdb->get_var("SELECT count(id) FROM {$staffmetadb} WHERE name IN ('".implode("','",$nonstd)."')");
	$allow = ($set == count($nonstd) ? 1 : -1);
	if(!$perform) return(array($std,$nonstd,$total,$allow));
	$clean = (isset($_POST['clean']) && (int) $_POST['clean'] === 1 ? true : false);
	/**************************************************************************************************
	*	Continue Doing the Import
	**************************************************************************************************/
	array_shift($data);
	//check to make sure the imported columns match the existing columns, for an append
	if($_POST['action'] == "append"){
		$set = $wpdb->get_var("SELECT count(id) FROM {$staffmetadb} WHERE name IN ('".implode("','",$nonstd)."')");
		if($set != count($nonstd)) return(showResults("Can't do an append, the columns don't match.",1));
		//otherwise, get the column numbers
		foreach($nonstd as $k=>$colname){
			$q = "SELECT id FROM {$staffmetadb} WHERE name=%s";
			$cnum = $wpdb->get_var($wpdb->prepare($q, $colname));
			if(!$cnum) return(showResults("Can't reuse $colname column, possibly because of how it's named.",1));
			$nonstd[$k] = array($colname, $cnum);
		}
	//clear the stafflist table for a complete replacement
	} else {
		$wpdb->query("TRUNCATE {$staffdb};");
		$nonstdnames = $nonstd;
		
		//removing old nonstandard columns, reordering standard columns
		for($i = 1; $i <= 20; $i++) {
			$fixed = array("firstname","lastname","phone","email","department");
			if(isset($fixed[ ($i-1) ])){ /*reset standard*/
				$fixedcolname = $fixed[ ($i-1) ];
				$q = "UPDATE {$staffmetadb} SET name=%s, active=".(in_array($fixedcolname,$std)?1:-1).", colpos=%d WHERE id=%d";
				$wpdb->query($wpdb->prepare($q, $fixedcolname, $i, $i ));
			} else { /*reset nonstandard*/
				$q = "UPDATE {$staffmetadb} SET name=NULL, active=-1, colpos=%d WHERE id=%d";
				$wpdb->query($wpdb->prepare($q, $i, $i ));
			}
		}
		$col = (int) 6;
		for($i = 1; $i <= 15; $i++) {
			$nonstdcolumn = (count($nonstdnames)>0 ? array_shift($nonstdnames) : NULL);
			if(trim($nonstdcolumn)=="") continue;
			$q = "UPDATE {$staffmetadb} SET name=%s, active=%d WHERE id=%d";
			$wpdb->query($wpdb->prepare($q, $nonstdcolumn, (is_null($nonstdcolumn)?-1:1), $col ));
			$col++;
		}
		//myprint_r($nonstd);
		foreach($nonstd as $k=>$colname){
			$q = "SELECT id FROM {$staffmetadb} WHERE name=%s";
			$cnum = $wpdb->get_var($wpdb->prepare($q, $colname));
			if(!$cnum || $cnum < 5 || $cnum > 20) return(showResults("($cnum) Can't reuse $colname column, possibly because of how it's named.",1));
			$nonstd[$k] = array($colname, $cnum);
		}
	}
																			//myprint_r($cells); myprint_r($std); myprint_r($nonstd); //exit; 
	foreach($data as $rownum => $row){
																			//echo "ROW: ".print_r($row,true)."<br />";
		if(count($row) < count($cells)) continue;							//bad row; skip
		$record = array(); $cast = array();
		foreach($cells as $cnum => $cname){
			if(trim($row[$cnum])!=""){
				$recordvalue = ( $clean ? cleanText($row[$cnum]) : $row[$cnum] );
				if(array_key_exists($cnum, $std)){
					$record[$cname] = $recordvalue;
				} else { $record["col".$nonstd[$cnum][1]] = $recordvalue; }
				$cast[] = "%s";
			}
		}																	//myprint_r($record); //exit;
		
		if(!empty($record)){
			$q = "INSERT INTO {$staffdb} (".implode(",",array_keys($record)).") VALUES (".implode(",",$cast).")";
			if($wpdb->query($wpdb->prepare($q, $record))){ $added++; } else { $notadded++; }
			//echo "$q<br />"; myprint_r($record); $wpdb->print_error();
		}

	} //end data loop
	return( array($added,$notadded) );
}

/**************************************************************************************************
*	Removes non-standard characters, attempts to conform text to utf8 (clean)
**************************************************************************************************/ 
function cleanText($string, $convert=true){
	ini_set('mbstring.substitute_character', "none"); 
	
	//do we clean/convert the text to UTF8 on import?
	$utf8 = ($convert ? iconv(mb_detect_encoding($string, mb_detect_order(), true), "UTF-8", $string) : $string);
	
	// First, replace UTF-8 characters.
	$utf8 = str_replace(	array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
	 						array("'", "'", '"', '"', '-', '--', '...'),
	 						$utf8 
			);
	// Next, replace their Windows-1252 equivalents.
	$utf8 = str_replace(	array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
	 						array("'", "'", '"', '"', '-', '--', '...'),
	 						$utf8
			);
	$recordvalue = str_replace(chr(160), " ", $utf8);		//converts an a0 space to a correct space
	$recordvalue = trim($recordvalue);						//now trims whitespace
	$recordvalue = trim($recordvalue,'"');					//and quotes
	$recordvalue = trim($recordvalue,",;");					//and bad delimiters
	//echo "BEFORE: $string --> AFTER: $recordvalue<br />";
	return($recordvalue);
}
/**************************************************************************************************
*	Using PHPExcel to Parse Datafile
**************************************************************************************************/ 
function readCSVintoArray($file){
	require_once(plugin_dir_path( __FILE__ ) . "lib/phpexcel/PHPExcel.php");
	
	$arr = array();
	$phpexcel = new PHPExcel(); $format = 'xls';

	if(strpos(strtolower($file), '.xlsx')){
		$reader = new PHPExcel_Reader_Excel2007();
		$reader->setReadDataOnly(true);
	} elseif(strpos(strtolower($file), '.xls')){
		$reader = new PHPExcel_Reader_Excel5();
		$reader->setReadDataOnly(true);
	} elseif(strpos(strtolower($file), '.ods')){
		$format = 'ods';
		$reader = new PHPExcel_Reader_OOCalc();
		$reader->setReadDataOnly(true);
	} elseif(strpos(strtolower($file), '.csv')){
		$format = 'csv';
		$reader = new PHPExcel_Reader_CSV();
	} else {
		die('File format not supported!');
	}
	
	$phpexcel = $reader->load($file);
	$worksheet = $phpexcel->getActiveSheet();
	$lastrow = $worksheet->getHighestRow();
	$lastcol = $worksheet->getHighestColumn();
	for($row = 1; $row <= $lastrow; ++$row) {
		$data = $worksheet->rangeToArray("A{$row}:{$lastcol}{$row}", null, true, true, true);
		if(!empty($data[ $row ])) $arr[] = $vals = array_values($data[ $row ]);
	}
	//myprint_r($arr);
	return($arr);
}
/**************************************************************************************************
*	Check Datafile (returns extension)
**************************************************************************************************/
function checkDatafile($farr){
	$name = $farr['name'];
	$type = $farr['type'];
	switch($type){
		case "application/excel":
		case "application/vnd.ms-excel":
		case "application/x-excel":
		case "application/x-msexcel":
		case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
			if(strstr($name,".xlsx")) return("xlsx");
			if(strstr($name,".xls")) return("xls");
			if(strstr($name,".csv")) return("csv");
			return("xls");
			break;
		case "text/plain":
		case "text/tsv":
		case "text/csv":
		case "text/comma-separated-values":
		default:
			return("csv");
			break;
	}
}
/**************************************************************************************************
*	Admin Active/Sort Functions
**************************************************************************************************/ 
function ajax_sort(){
	global $wpdb, $staffmetadb; $pos = 1;
	
	$active = isset($_POST['active'])?$_POST['active']:array();
	if(!empty($active)){	
		foreach($active as $k=>$col){
			$id = str_replace("col", "", $col);
			$q = "UPDATE $staffmetadb SET colpos=%d, active=%d WHERE id=%d";
			$pq = $wpdb->prepare( $q, $pos, 1, $id ); $wpdb->query( $pq );
			$pos++;
		}
	}
	$inactive = isset($_POST['inactive'])?$_POST['inactive']:array();
	if(!empty($inactive)){	
		foreach($inactive as $k=>$col){
			$id = str_replace("col", "", $col);
			$q = "UPDATE $staffmetadb SET colpos=%d, active=%d WHERE id=%d";
			$pq = $wpdb->prepare( $q, $pos, -1, $id ); $wpdb->query( $pq );
			$pos++;
		}
	}
}
/**************************************************************************************************
*	Create a Short Code for the StaffList Directory
**************************************************************************************************/
function insert_stafflist( $atts ) {
	global 	$rows, $messaging;
	$subset = false;
	$on = "both";

	//number of results rows
	if(isset($atts['rows']) && is_numeric($atts['rows']) && $atts['rows'] > 0 && $atts['rows'] <= 100) $rows = $atts['rows'];
	//subsets (e.g.: type:agency)
	if(isset($atts['subset']) && ""!=trim($atts['subset']) && $subset = checkSubset($atts['subset'])) $subset = htmlspecialchars($subset,ENT_QUOTES);
	//search on
	if(isset($atts['on']) && in_array($atts['on'],array("enter","type"))) $on=$atts['on'];
	//custom messaging
	$searchdir = get_option("stafflist_rename_searchdir"); //use custom message, if set
	$searchdir = ( $searchdir ? esc_attr($searchdir) : $messaging['searchdir']['value'] );

	wp_register_style('stafflist', plugins_url('stafflist.css', __FILE__) ); wp_enqueue_style('stafflist');
	return("<div class='staffwrapper on{$on}'>
				<div class='pagerblock'>
					<div class='stafflistctl'>
						<input type='hidden' rel='sl_sort' value='1' autocomplete='Off'>
						<input type='hidden' rel='sl_page' value='1' autocomplete='Off'>
						<input type='hidden' rel='sl_rows' value='{$rows}' autocomplete='Off'>".
						($subset ? "<input type='hidden' rel='sl_subs' value='{$subset}' autocomplete='Off'>" : "").
						"<label for='sl_search' id='searchDirectory'>{$searchdir}</label>".
						"<input type='text' class='sl_search' id='sl_search' rel='sl_search' value='' placeholder='{$searchdir}' autocomplete='Off' arial-labelledby='searchDirectory'>
						<button type='button' class='search-clear'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 96 96'><path fill='#AAAAAB' d='M96 14L82 0 48 34 14 0 0 14l34 34L0 82l14 14 34-34 34 34 14-14-34-34z'/></svg></button>
					</div>
				</div>
				<div class='staffdirectory'>
					<div class='staffpager'></div>
					<table class='stafflists'></table>
					<div class='staffpager'></div>
				</div>
			</div>");
}
add_shortcode( 'stafflist', 'insert_stafflist' );
/**************************************************************************************************
*	Check Short Code Attributes
**************************************************************************************************/
function checkSubset($in){
	global $wpdb, $staffmetadb;	
	$cols = $wpdb->get_results("SELECT id,name,colpos,
								CASE WHEN id<6 THEN name ELSE concat('col',id) END AS col
								FROM {$staffmetadb}
								ORDER BY colpos ASC, id ASC", ARRAY_A);
	if(!preg_match("/^(\w*)\:(.*)$/", $in, $parts)) return (false);
	list($unused,$column,$value) = $parts;

	foreach($cols as $arr) {
		if(strtolower($arr['col'] ) == $column) return(sanitize_text_field($in));
		if(strtolower($arr['name']) == $column) return("{$arr['col']}:".sanitize_text_field($value));
	}
	return(false);
}
/**************************************************************************************************
*	Exports
**************************************************************************************************/
function handleStafflistExports(){
	global 	$wpdb,$cols,			//wpdb object
			$staffdb,$staffmetadb;	//csvs and the like

	//column names
	$arr = $wpdb->get_results(	"SELECT id,name,colpos,
								CASE WHEN id<6 THEN name ELSE concat('col',id) END AS col
								FROM {$staffmetadb}
								ORDER BY id ASC", ARRAY_A);
	$cols = array(); foreach($arr as $col) $cols[] = $col['name'];

	//sorting
	$sorts = array("last"=>"lastname","first"=>"firstname","dept"=>"department","email"=>"email");
	$s = (isset($_GET['s']) && array_key_exists(rtrim($_GET['s'],"-"), $sorts) ? $_GET['s'] : "last-");
	$dir = strstr($s,"-")?"ASC":"DESC";
	$sort = $sorts[ str_replace("-","",$s) ];
	
	//filter by searched keyword
	$w = (isset($_GET['search']) && (string) trim($_GET['search'])!="" ? strtolower($wpdb->_real_escape( stripslashes( trim($_GET['search']) ))) : false);
	$where = ($w ? "WHERE LOWER(lastname) LIKE '%{$w}%' ".
					  "OR LOWER(firstname) LIKE '%{$w}%' ".
					  "OR LOWER(department) LIKE '%{$w}%' ".
					  "OR LOWER(email) LIKE '%{$w}%'" : "");
	$nonstd = getNonstandardRows(1); //add nonstandard rows to search
	if($w && !empty($nonstd)) foreach($nonstd as $k=>$v) $where.= " OR LOWER({$k}) LIKE '%{$w}%' ";
	
	//query
	$q =   "SELECT * FROM {$staffdb} {$where} ORDER BY {$sort} {$dir}";
	$staff = $wpdb->get_results($q, ARRAY_A);

	//build the output
	require_once(plugin_dir_path( __FILE__ ) . "lib/phpexcel/PHPExcel.php");
	$objPHPExcel = new PHPExcel(); $row = 2;
	$objPHPExcel->getActiveSheet()->fromArray( $cols, NULL, 'A1');
	foreach($staff as $st){
		unset($st['id']);
		$objPHPExcel->getActiveSheet()->fromArray( $st, NULL, "A{$row}"); 
		$row++; 
	}

	$objPHPExcel->getActiveSheet()->setTitle('StaffList');
	$objPHPExcel->setActiveSheetIndex(0);
	
	header('Cache-Control: max-age=0');
	header('Cache-Control: max-age=1');
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header ('Cache-Control: cache, must-revalidate');
	header ('Pragma: public');
	
	if($_GET['export']=="csv"){
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.date("Ymd").'_StaffList.csv"');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
		$objWriter->save('php://output');
		exit;
	} else {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.date("Ymd").'_StaffList.xls"');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
}

/**************************************************************************************************
*	Developer Functions
**************************************************************************************************/
if(!function_exists("wpesc")){ function wpesc($in){ /*_real_escape() adds double slashes */
	global $wpdb; $out =  $wpdb->_real_escape( stripslashes( trim($in) )); return(stripslashes($out)); 
}};
if(!function_exists("myprint_r")){	function myprint_r($in) { echo "<pre>"; print_r($in); echo "</pre>"; return; }};
?>