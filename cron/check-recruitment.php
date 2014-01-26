<?php
/*
Script Name: check-recruitment.php
Script URI: http://github.com/mehtryx/rsi-threads/cron
Description: Executes against an RSI-THREAD object to find the recruitment thread
Author: mehtryx
Version: 0.1.0
Author URI: http://github.com/mehtryx

Usage:

	This script is intended to be run as a cron job, the output can (and should) be directed to a log file.
	
	To execute, modify the $destination_file variable to a correct path and file name, then add the following to yoru crontab:
	
	* * * * * /usr/bin/php /path/to/check-recruitment.php >> /path/to/log/CRON_LOG.txt 2> /dev/null
	
	Of course be sure to verify the path to php, recommend manually running command once before actually adding to crontab
*/
$path = dirname( __FILE__ );
date_default_timezone_set('UTC'); // If the server has this set in the php.ini then this line can be removed.

// Note this is a relative path, adjust if this script moves
include ( $path . '/../rsi-forum-thread-check.php' );

$source_file = '';
$destination_file = '/path/to/replaced/file.php'; // This has to be set based on what file in the site we are changing

$now = new DateTime();
$log_date = $now->format('Y-m-d H:i:s');
$threshold = 15; // Set this to the position number our script needs to be within

$rsi_threads = new RSI_THREADS();
$imperium_thread = $rsi_threads->search_discussions( '16594' ); // 16594 is discussion were tracking


if ( $imperium_thread === false ) {
	// Thread was not found, or error talking ot RSI Forums
	// Default status to red, better to false positve thread alert, then admins have visual cue
	print_r( $log_date . "\tERROR\t(CHECK-RECRUITMENT)\tThere has been an error talking to RSI forums\n" );
	$source_file = $path . '/html-fragments/recruitment-red.php';
}
else if ( $imperium_thread === 0 || $imperium_thread > $threshold ) {
	// Thread was not found in the pages searched, or is beyond the threshold
	print_r( $log_date . "\tWARN\t(CHECK-RECRUITMENT)\tThread position outside threshold, value returned=" . $imperium_thread . "\n" );
	$source_file = $path . '/html-fragments/recruitment-red.php';
}
else if ( $imperium_thread <= $threshold && $imperium_thread > 0 ) {
	// Thread was found in page searched, and is within threshold
	print_r( $log_date . "\tINFO\t(CHECK-RECRUITMENT)\tThread position is good, found at position=" . $imperium_thread . "\n" );
	$source_file = $path . '/html-fragments/recruitment-green.php';
}

// Now copy the correct file
if ( !copy( $source_file, $destination_file ) ) {
	print_r( $log_date . "\tERROR\t(CHECK-RECRUITMENT)\tUnable to copy " . $source_file . " to " . $destination_file . "\n" );
}
else {
	print_r( $log_date . "\tINFO\t(CHECK-RECRUITMENT)\tCopied " . $source_file . " to " . $destination_file . "\n" );
}
// End of script