#!/usr/bin/php
<?php 

set_include_path('.:/var/www/wordpress:/var/www/wordpress/wp-includes:/var/www/wordpress/wp-admin:/usr/share/php:/usr/share/pear');

// too complicated :D
//require_once( 'admin.php' );

$_SERVER['HTTP_HOST'] = 'wp.kapsi.fi';
//REMOTE_ADDR
//SERVER_NAME
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_NAME'] = 'wp.kapsi.fi';

require_once('wp-config.php');
require_once('pluggable.php');
require_once('plugin.php');
require_once('user.php');
require_once('formatting.php');
require_once('capabilities.php');
require_once('cache.php');
require_once('wp-db.php');
require_once('ms-functions.php');

function add_domain_to_blog($blog_id, $domain)
{
	global $wpdb;
	if( null == $wpdb->get_row( "SELECT blog_id FROM {$wpdb->blogs} WHERE domain = '$domain'" ) && 
		null == $wpdb->get_row( "SELECT blog_id FROM {$wpdb->dmtable} WHERE domain = '$domain'" ) ) 
	{
		// set primary
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->dmtable} SET active = 0 WHERE blog_id = %d", $wpdb->blogid ) );
		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->dmtable} ( `id` , `blog_id` , `domain` , `active` ) VALUES ( NULL, %d, %s, %d )", 
		$wpdb->blogid, $domain, 1 ) );
		print('Created domain mapping ' . $domain . "\n");
		return true;
	}
	else {
		print('Requested alias ' . $domain . ' already on database, skipping!');
		return true;
	}
}


$username = $_SERVER['argv'][1];
$email = $_SERVER['argv'][2];
if (count($_SERVER['argv']) == 4)
	$extdomain = $_SERVER['argv'][3];

if (empty ($username) or empty($email))
{
    print('Usage: ' . $_SERVER['argv'][0] . " <username> <email>\n");
	exit(1);
}

$email = sanitize_email( $email );

$title = 'Uusi blogi';
$blog = $username;
$domain=$username .'.wp-test.kapsi.fi';

$user_id = email_exists($email);
if ( !$user_id ) { // Create a new user with a random password
	$password = wp_generate_password( 12, false );
	print('Creating user ' . $email . ' passwd: ' . $password . ' username:' . $username . "\n");
	
	$user_id = wpmu_create_user( $username, $password, $email );
	if ( false == $user_id )
		die( __( 'There was an error creating the user.' ) );
	else
		wp_new_user_notification( $user_id, $password );
}

$id = wpmu_create_blog( $domain, '/', $title, $user_id , array( 'public' => 1 ), $current_site->id );

if ( !is_wp_error( $id ) ) {
    if ( !is_super_admin( $user_id ) && !get_user_option( 'primary_blog', $user_id ) )
		update_user_option( $user_id, 'primary_blog', $id, true );
	wpmu_welcome_notification( $id, $user_id, $password, $title, array( 'public' => 1 ) );
	print('Created blog http://' . $username . ".kapsi.fi/\n");
	if (isset($extdomain) && !empty($extdomain))
		add_domain_to_blog($id, $extdomain);
	exit;
}
else {
            die( $id->get_error_message() );
}


print("\n");

?>
