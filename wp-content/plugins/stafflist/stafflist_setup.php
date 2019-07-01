<?php

$table_name = false;
$table_meta_name = false;
$charset_collate = false;

function stafflist_install() {
	
	global $wpdb;
	$staffdb = $wpdb->prefix . "stafflist";
	$staffmetadb = $wpdb->prefix . "stafflist_meta";
	$charset_collate = $wpdb->get_charset_collate();
	
	//$wpdb->query("DROP TABLE IF EXISTS {$staffdb}");
	//$wpdb->query("DROP TABLE IF EXISTS {$staffmetadb}");
	
	$sql = "CREATE TABLE {$staffdb} (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	firstname varchar(64) NOT NULL,
	lastname varchar(64),
	phone varchar(16),
	email varchar(64),
	department varchar(64),
	col6 varchar(64) DEFAULT NULL,
	col7 varchar(64) DEFAULT NULL,
	col8 varchar(64) DEFAULT NULL,
	col9 varchar(64) DEFAULT NULL,
	col10 varchar(64) DEFAULT NULL,
	col11 varchar(64) DEFAULT NULL,
	col12 varchar(64) DEFAULT NULL,
	col13 varchar(64) DEFAULT NULL,
	col14 varchar(64) DEFAULT NULL,
	col15 varchar(64) DEFAULT NULL,
	col16 varchar(64) DEFAULT NULL,
	col17 varchar(64) DEFAULT NULL,
	col18 varchar(64) DEFAULT NULL,
	col19 varchar(64) DEFAULT NULL,
	col20 varchar(64) DEFAULT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";
	
	//echo "SQL: $sql";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	$sql2 = "CREATE TABLE {$staffmetadb} (
	id tinyint(1) 	NOT NULL,
	name varchar(32),
	active tinyint(1) 	DEFAULT -1,
	colpos tinyint(1) 	DEFAULT 6,
	UNIQUE KEY id (id)
	) $charset_collate;";
	
	dbDelta( $sql2 );

	
	//insert sample data if none
	$count =  $wpdb->get_var("SELECT count(id) from {$staffdb}");
	if($count < 1) {
		$vals = array(  'firstname' => 'Lucille',
						'lastname' => 'Bluth',
						'phone' => '(212) 123-4567',
						'email' => 'lbluth@bluthinc.com',
						'department' => 'Human Resources'
		);
		$wpdb->insert( $staffdb, $vals);
	}
	
	$count =  $wpdb->get_var("SELECT count(id) from {$staffmetadb}");
	if($count < 1) {
		$defaults = array(	1 => "firstname",
							2 => "lastname",
							3 => "phone",
							4 => "email",
							5 => "department");
		for($col = 1; $col <= 20; $col++) {
			if(isset($defaults[$col])){ $wpdb->query("INSERT INTO {$staffmetadb} (id,name,active) VALUES ($col,'{$defaults[$col]}',1);" ); }
			else { $wpdb->query("INSERT INTO {$staffmetadb} (id) VALUES ($col);" ); }
		}
	}
	
	//check stafflist roles & capabilities
	checkStafflistRoles();
	
	//now the deactivate/uninstall function
	register_deactivation_hook( __FILE__, 'stafflist_uninstall' );
	register_uninstall_hook( __FILE__, 'stafflist_uninstall' );
}
/**************************************************************************************************
*	Roles & Capabilities
**************************************************************************************************/
function checkStafflistRoles(){
	$role_admin = get_role('administrator');
	//add the capability for admins
	if(!array_key_exists("edit_stafflist", (array) $role_admin->capabilities)) $role_admin->add_cap('edit_stafflist', true);

	$role_editor = get_role('stafflist_editor');
	//add the role of StaffList Editor for non-admin access
	if(!$role_editor) { 
		$result = add_role('stafflist_editor', __( 'StaffList Editor' ), array('read' => true));
		if(null !== $result ) {
			$role_editor = get_role('stafflist_editor');
			$role_editor->add_cap('edit_stafflist', true);
		}
	}
	//double-check the capabilities for the StaffList Editor
	if(!array_key_exists("edit_stafflist", (array) $role_editor->capabilities)) $role_editor->add_cap('edit_stafflist', true);
}
/**************************************************************************************************
 *	Uninstall Functions - Garbage Cleanup
**************************************************************************************************/
function stafflist_uninstall(){
	if(!current_user_can( 'activate_plugins' )) return;
	global $wpdb, $staffdb, $staffmetadb;
	$wpdb->query("DROP TABLE {$staffdb}");
	$wpdb->query("DROP TABLE {$staffmetadb}");
}


?>
