<?php

// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brookes ID web service - service functions
 * @package   local_brookesid_ws
 * @author    Peter Welham / Yvonne Aburrow
 * @copyright 2016, Oxford Brookes University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
// Define the web service functions to install.
$functions = array(
	// inbox
	'local_brookesid_ws_get_email' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'get_email',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Fetches emails.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	// user's badges 
	'local_brookesid_ws_get_badges_issued' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'get_badges_issued',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Returns the badges issued to a user.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	// next badges
	'local_brookesid_ws_get_nextbadges' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'get_nextbadges',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Returns the next badge.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	// all badges
	'local_brookesid_ws_get_all_badges' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'get_all_badges',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Returns all badges in the scheme.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	// your activities
	'local_brookesid_ws_get_activities' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'get_activities',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Returns the activities the student has signed up for.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	//get privacy settings
	'local_brookesid_ws_get_privacy_setting' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'get_privacy_setting',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Gets my badge privacy setting from Moodle.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	//export
	'local_brookesid_ws_get_export' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'get_export',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Returns the object to be exported.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	//set privacy settings
	'local_brookesid_ws_set_privacy_setting' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'set_privacy_setting',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Posts my badge privacy setting to Moodle.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	//sign up for a badge 
	'local_brookesid_ws_set_badge_signup' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'set_badge_signup',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Posts my badge signup to Moodle.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	// de-enrol from badge
	'local_brookesid_ws_set_badge_cancel' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'set_badge_cancel',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Posts my badge signup cancellation.',
		'type'        => 'read',
		'capabilities'=> ''
	),
	// set message read
	'local_brookesid_ws_set_message_read' => array(
		'classname'   => 'local_brookesid_ws_external',
		'methodname'  => 'set_message_read',
		'classpath'   => 'local/brookesid_ws/externallib.php',
		'description' => 'Posts message read update.',
		'type'        => 'read',
		'capabilities'=> ''
	)
);

// Define the services to install as pre-build services.
$services = array(
	'Brookes ID web service' => array(
		'shortname' => 'brookesid_ws',
		'functions' => array(
			//inbox
			'local_brookesid_ws_get_email',
			// badges
			'local_brookesid_ws_get_badges_issued',
	        // next badges
	        'local_brookesid_ws_get_nextbadges',
	        // all badges
	        'local_brookesid_ws_get_all_badges',
	        // activities
	        'local_brookesid_ws_get_activities',
	        //get privacy settings
	        'local_brookesid_ws_get_privacy_setting',
	        //export
	        'local_brookesid_ws_get_export',
	        //set privacy settings
	        'local_brookesid_ws_set_privacy_setting',
	        //set privacy settings
	        'local_brookesid_ws_set_badge_signup',
	        //set privacy settings
	        'local_brookesid_ws_set_badge_cancel',
	        //set privacy settings
	        'local_brookesid_ws_set_message_read'
		),
		'restrictedusers' => 0,
		'enabled' => 1
	)
);
