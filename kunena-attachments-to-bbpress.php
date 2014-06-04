<?php

//Standalone script
//Execute only after you have imported kunena into bbPress

require( '/home/user/public_html/wordpress/wp-load.php' );
require( '/home/user/public_html/wordpress/wp-admin/includes/image.php' );

// Database Connection
$host="localhost";
$uname="db_username";
$pass="db_username_password";
$database = "";
$site_url = 'http://www.example.com';

$connection=mysql_connect($host,$uname,$pass);

echo mysql_error();

//or die("Database Connection Failed");
$selectdb=mysql_select_db($database) or die("Database could not be selected");
$result=mysql_select_db($database) or die("database cannot be selected <br>");

// Fetch Record from Database
$output = "";
$sql = mysql_query("SELECT * FROM `j25_kunena_attachments` ORDER BY `id` LIMIT 0,200");
$columns_total = mysql_num_fields($sql);

$count = 0;

// Get Records from the table
while ($row = mysql_fetch_array($sql)) {

	$old_filename = $site_url.'/'.$row['folder'].'/'.$row['filename'];
	$old_filename = str_replace(" ", "%20", $old_filename); // for filenames with spaces

	//Customize as needed
	$date = strftime('%Y/%m', strtotime('2014-03-27 19:50:54'));
	$uploads = wp_upload_dir($date);
	$new_upload_dir = $uploads['path'];
	$new_full_filename = $new_upload_dir.'/'.str_replace(" ", "", $row['filename']);

	if( copy($old_filename, $new_full_filename) ) {

		$parent_args = array(
			'post_type' => array('topic', 'reply'),
			'meta_key' => '_bbp_post_id',
			'meta_value' => $row['mesid']
		);
		$parent_query = new WP_Query($parent_args);
		$parent_query->get_posts();
		if($parent_query->have_posts()) {
			$parent_query->the_post();
			$attachment_data = array(
				'post_mime_type'	=> $row['filetype'],
				'post_title'		=> $row['filename'],
				'post_status'		=> 'inherit',
				'post_content'		=> '',
			);

			$attach_id = wp_insert_attachment($attachment_data, $new_full_filename, get_the_ID());
			if($attach_id) {
				update_post_meta($attach_id, '_bbp_attachment', 1);
				wp_generate_attachment_metadata($attach_id, $new_full_filename);
			}
		}
		wp_reset_postdata();
	}

	$count++;
}

echo $count;

mysql_close($connection);
exit;

?>
