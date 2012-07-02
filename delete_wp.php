#!/usr/bin/php
<?php 

set_include_path('.:/var/www/wordpress:/var/www/wordpress/wp-includes:/var/www/wordpress/wp-admin:/var/www/wordpress/wp-admin/includes:/usr/share/php:/usr/share/pear');

// too complicated :D
//require_once( 'admin.php' );

$_SERVER['HTTP_HOST'] = 'wp.kapsi.fi';
//REMOTE_ADDR
//SERVER_NAME
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = 'wp.kapsi.fi';

require_once('wp-config.php');
#require_once('pluggable.php');
#require_once('plugin.php');
#require_once('user.php');
#require_once('formatting.php');
#require_once('capabilities.php');
#require_once('cache.php');
#require_once('wp-db.php');
#require_once('ms-functions.php');
require_once('ms.php');


$id = $_SERVER['argv'][1];

if (empty ($id) )
{
    print('Usage: ' . $_SERVER['argv'][0] . " <blog id|address>\n");
	exit(1);
}



if (!is_numeric($id))
{
	$res = $wpdb->get_row( "SELECT blog_id FROM {$wpdb->blogs} WHERE domain = '$id'");
}
else
{
	$res = $wpdb->get_row( "SELECT blog_id FROM {$wpdb->blogs} WHERE blog_id = '$id'");
}
if (empty($res))
{
	print("Blog " . $id . " not found!\n");
	exit;
}
else
{
	$id = $res->blog_id;
	// delete first domain mappings
	$res = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->dmtable} WHERE blog_id = %s", $id ) );
	//if (empty($res)) print("Deleted domain mapping\n");
	wpmu_delete_blog($id, true);
	print("Deleted blog " . $id. "\n");
}

?>
