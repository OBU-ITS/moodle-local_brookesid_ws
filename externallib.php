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

/*
 * Brookes ID web service - external library
 *
 * @package    local_brookesid_ws
 * @author     Yvonne Aburrow
 * @copyright  2019, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
 
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . "/badgeslib.php");
require_once($CFG->dirroot . "/badges/lib.php");
require_once($CFG->dirroot . "/config.php");
require_once($CFG->dirroot . "/course/lib.php");

class local_brookesid_ws_external extends external_api {

/*****
 *  GET EMAIL 
 *  MOODLE MESSAGES 
 *****/	
 	public static function get_email_parameters() {
		return new external_function_parameters(array());
	}
	
 	public static function get_email() {
		global $USER, $DB;

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

		$sql = 'SELECT m.id, m.subject, m.fullmessage, m.timecreated
				FROM {message} m
				WHERE m.useridfrom = "2"
				AND m.useridto = ?';
                // $sql->bindValue(':uidto',$uidto, ':uidfrom', $uidfrom);
		$new_email_records = $DB->get_records_sql($sql, array($USER->id));

		$new_emails = array();
		foreach ($new_email_records as $e) {
			$new_emails[] = array(
				'message_id' => $e->id,
				'subject' => $e->subject,
				'fullmessage' => $e->fullmessage,
				'timecreated' => $e->timecreated,
				'status' => 'newemail'
			);
		}
		
		$sql = 'SELECT m.id, m.subject, m.fullmessage, m.timecreated
				FROM {message_read} m
				WHERE m.useridfrom = "2"
				AND m.useridto = ?'; 
		$read_email_records = $DB->get_records_sql($sql, array($USER->id));
		
		$read_emails = array();
		foreach ($read_email_records as $e) {
			$read_emails[] = array(
				'message_id' => $e->id,
				'subject' => $e->subject,
				'fullmessage' => $e->fullmessage,
				'timecreated' => $e->timecreated,
				'status' => 'reademail'
			);
		}

		// $count = count($read_email_records);
		
		// $countArr = array(
		//	 'emailcount' => $count
		// );
		
		return array_merge($new_emails, $read_emails);
		
		//return $countArr;
	}
	
	public static function get_email_returns() {
        return 
        new external_multiple_structure(
            new external_single_structure(
                array(
                    'message_id' => new external_value(PARAM_INT, 'the ID of the message'),
					'subject' => new external_value(PARAM_TEXT, 'message subject'),
                    'fullmessage' => new external_value(PARAM_RAW, 'full text of message'),
                    'timecreated' => new external_value(PARAM_INT, 'the time when the message was created'),
                    'status' => new external_value(PARAM_TEXT, 'message status - new or read')
                  // 'emailcount' => new external_value(PARAM_INT, 'the number of emails')
                )
           )
        );
	}
	

		/*****
		 *  BADGES ISSUED
		 *  Moodle badges
		 *****/
	
	public static function get_badges_issued_parameters() {
		return new external_function_parameters(array());
	}
	
	public static function get_badges_issued() {
		global $USER, $DB;

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

		$sql = 'SELECT bi.id AS issuedid, bi.dateissued, bi.dateexpire, b.id, b.name, b.description, c.idnumber '
			. 'FROM {badge_issued} bi '
			. 'JOIN {badge} b ON b.id = bi.badgeid '
			. 'JOIN {course} c ON c.id = b.courseid '
			. 'WHERE bi.userid = ? '
			. 'AND c.idnumber LIKE "CCA~%"';
		$badge_records = $DB->get_records_sql($sql, array($USER->id));

		$badges = array();
		foreach ($badge_records as $b) {
			$pos = strpos($b->idnumber, '~'); // We know there's at least one
			$code = substr($b->idnumber, ($pos + 1));
			$pos = strpos($code, '~');
			if ($pos === false) {
				$badge_type = '';
				$badge_category = '';
			} else {
				$badge_type = substr($code, 0, $pos);
				$badge_category = substr($code, ($pos + 1), ($pos + 1));
			}
			$badges[] = array(
				'badge_id' => $b->id,
				'badge_name' => $b->name,
				'badge_description' => $b->description,
				'badge_type' => $badge_type,
				'badge_category' => $badge_category,
				'issue_id' => $b->issuedid,
				'issue_date' => $b->dateissued,
				'issue_expires' => $b->dateexpire
			);
		}
		
		return $badges;
	}
	
	public static function get_badges_issued_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array (
					'badge_id' => new external_value(PARAM_INT, 'Badge ID'),
					'badge_name' => new external_value(PARAM_TEXT, 'Badge name'),
					'badge_description' => new external_value(PARAM_TEXT, 'Badge description'),
					'badge_type' => new external_value(PARAM_TEXT, 'Badge type'),
					'badge_category' => new external_value(PARAM_TEXT, 'Badge category'),
					'issue_id' => new external_value(PARAM_INT, 'Issue ID'),
					'issue_date' => new external_value(PARAM_INT, 'Issue date'),
					'issue_expires' => new external_value(PARAM_INT, 'Expiry date')
				)
			)
		);
	}
	
	/* NEXT BADGES */
	public static function get_nextbadges_parameters() {
		/* next badges suggested */
		return new external_function_parameters(array());
	}
		
	public static function get_nextbadges() {
		global $USER, $DB;

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
//		require_capability('moodle/badge:view', $context);

		/*** Select all badges that the user has not yet signed up for 
		 * (don't need to explicitly exclude those they've been awarded 
		 * because you have to sign up for something in order to be awarded it) 
		 ***/
		$sql = 'SELECT nb.id AS badgeid, nb.name, nb.description, c.idnumber, c.id AS courseid, c.shortname AS coursename, c.summary AS coursedescription
				FROM {badge} nb
				JOIN {course} c ON c.id = nb.courseid
				WHERE c.idnumber LIKE "CCA~%"
				AND nb.courseid NOT IN (SELECT c.id
						FROM {course} c 
						JOIN {enrol} en ON en.courseid = c.id 
						JOIN {user_enrolments} ue ON ue.enrolid = en.id 
						WHERE ue.userid = ?
						AND c.idnumber LIKE "CCA~%")' ;
		$next_badge_records = $DB->get_records_sql($sql, array($USER->id));
		/* User's course categories mapped to faculty abbreviations*/
		$faculties = self::get_faculties(); 
		$next_badges = array();
		if(substr( $USER->username, 0, 1 ) === "p") {
			$is_staff = 1;
		} else {
			$is_staff = 0;
		}
		foreach ($next_badge_records as $nb) {
			$pos = strpos($nb->idnumber, '~'); // We know there's at least one
			$code = substr($nb->idnumber, ($pos + 1));
			$pos = strpos($code, '~');
			if ($pos === false) {
				$next_badge_type = '';
				$next_badge_category = '';
			} else {
				$next_badge_type = substr($code, 0, $pos);
				$next_badge_category = substr($code, ($pos + 1), ($pos + 1));
			}
			if (in_array($next_badge_type, $faculties) || $is_staff == 1) {
				$next_badges[] = array(
					'next_badge_id' => $nb->badgeid,
					'next_badge_name' => $nb->name,
					'next_badge_type' => $next_badge_type,
					'next_badge_category' => $next_badge_category,
					'next_badge_description' => $nb->description,
					'coursecode' => $nb->idnumber,
					'courseid' => $nb->courseid,
					'coursename' => $nb->coursename,
					'coursedescription' => $nb->coursedescription
				);
			}
		}
		
		return $next_badges;
	}
	
	public static function get_nextbadges_returns() {
		/*Moodle badges that the user could go for next*/
		return new external_multiple_structure(
			new external_single_structure(
				array (
					'next_badge_id' => new external_value(PARAM_INT, 'Next Badge ID'),
					'next_badge_name' => new external_value(PARAM_TEXT, 'Next Badge name'),
					'next_badge_type' => new external_value(PARAM_TEXT, 'Next Badge type'),
					'next_badge_category' => new external_value(PARAM_TEXT, 'Next Badge category'),
					'next_badge_description' => new external_value(PARAM_TEXT, 'Next Badge description'),
					'coursecode' => new external_value(PARAM_TEXT, 'course code'),
					'courseid' => new external_value(PARAM_INT, 'Course ID'),
					'coursename' => new external_value(PARAM_TEXT, 'course name'),
					'coursedescription' => new external_value(PARAM_RAW, 'course description')
				)
			)
		);
	}
	
		/* ALL BADGES */
	public static function get_all_badges_parameters() {
		/* all badges in the scheme */
		return new external_function_parameters(array());
	}
		
	public static function get_all_badges() {
		global $USER, $DB;

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
//		require_capability('moodle/badge:view', $context);

		/* select all badges  */
		$sql = 'SELECT ab.id, ab.name, ab.description, c.idnumber, c.shortname AS coursename, c.summary AS coursedescription '
			. 'FROM {badge} ab '
			. 'JOIN {course} c ON c.id = ab.courseid '
			. 'WHERE c.idnumber LIKE "CCA~%"';
		$all_badge_records = $DB->get_records_sql($sql);

		$all_badges = array();
		foreach ($all_badge_records as $ab) {
			$pos = strpos($ab->idnumber, '~'); // We know there's at least one
			$code = substr($ab->idnumber, ($pos + 1));
			$pos = strpos($code, '~');
			if ($pos === false) {
				$all_badge_type = '';
				$all_badge_category = '';
			} else {
				$all_badge_type = substr($code, 0, $pos);
				$all_badge_category = substr($code, ($pos + 1), ($pos + 1));
			}
			$all_badges[] = array(
				'all_badge_id' => $ab->id,
				'all_badge_name' => $ab->name,
				'all_badge_type' => $all_badge_type,
				'all_badge_category' => $all_badge_category,
				'all_badge_description' => $ab->description,
				'coursename' => new external_value(PARAM_TEXT, 'course name'),
				'coursedescription' => new external_value(PARAM_RAW, 'course description')
			);
		}
		
		return $all_badges;
	}
	
	public static function get_all_badges_returns() {
		/*list of all Moodle badges in the scheme */
		return new external_multiple_structure(
			new external_single_structure(
				array (
					'all_badge_id' => new external_value(PARAM_INT, 'Badge ID'),
					'all_badge_name' => new external_value(PARAM_TEXT, 'Badge name'),
					'all_badge_type' => new external_value(PARAM_TEXT, 'Badge type'),
					'all_badge_category' => new external_value(PARAM_TEXT, 'Badge category'),
					'all_badge_description' => new external_value(PARAM_TEXT, 'Badge description'),
					'coursename' => new external_value(PARAM_TEXT, 'course name'),
					'coursedescription' => new external_value(PARAM_RAW, 'course description')
				)
			)
		);
	}
	
			/* ALL COURSES */
	public static function get_all_courses_parameters() {
		/* all badges in the scheme */
		return new external_function_parameters(array());
	}
		
	public static function get_all_courses() {
		global $USER, $DB;

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
//		require_capability('moodle/badge:view', $context);

		/* select all badges  */
		$sql = 'SELECT c.id, c.fullname, c.shortname, c.summary, c.idnumber  '
			. 'FROM {course} c '
			. 'WHERE c.idnumber LIKE "CCA~%"';
		$all_course_records = $DB->get_records_sql($sql);

		$all_courses = array();
		foreach ($all_course_records as $c) {
			$pos = strpos($c->idnumber, '~'); // We know there's at least one
			$code = substr($c->idnumber, ($pos + 1));
			$pos = strpos($code, '~');
			if ($pos === false) {
				$all_course_type = '';
				$all_course_category = '';
			} else {
				$all_course_type = substr($code, 0, $pos);
				$all_course_category = substr($code, ($pos + 1), ($pos + 1));
			}
			$all_courses[] = array(
				'all_course_id' => $c->id,
				'all_course_name' => $c->fullname,
				'all_course_shortname' => $c->shortname,
				'all_course_type' => $all_course_type,
				'all_course_category' => $all_course_category,
				'all_course_description' => $c->summary
			);
		}
		
		return $all_courses;
	}
	
	public static function get_all_courses_returns() {
		/*list of all Moodle courses (activities) in the scheme */
		return new external_multiple_structure(
			new external_single_structure(
				array (
					'all_course_id' => new external_value(PARAM_INT, 'course ID'),
					'all_course_name' => new external_value(PARAM_TEXT, 'course name'),
					'all_course_shortname' => new external_value(PARAM_TEXT, 'course short name'),
					'all_course_type' => new external_value(PARAM_TEXT, 'course type'),
					'all_course_category' => new external_value(PARAM_TEXT, 'course category'),
					'all_course_description' => new external_value(PARAM_RAW, 'course description')
				)
			)
		);
	}
	
		/* YOUR ACTIVITIES */
	public static function get_activities_parameters() {
		/* badge activities that the student is signed up for */
		return new external_function_parameters(array());
	}
		
	public static function get_activities() {
		global $USER, $DB;

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
//		require_capability('moodle/badge:view', $context);

		/* select all co-curricular activities that the user has signed up for, but not yet had a badge issued */
		$sql = 'SELECT DISTINCT ac.id, ac.shortname, ac.summary, ac.idnumber, bi.dateissued, ue.timecreated  
				FROM {course} ac 
				JOIN {enrol} en ON en.courseid = ac.id 
				JOIN {user_enrolments} ue ON ue.enrolid = en.id
				LEFT JOIN {badge} b ON b.courseid = ac.id 
				LEFT JOIN {badge_issued} bi ON bi.badgeid = b.id
				WHERE ac.idnumber LIKE "CCA~%"
				AND ue.userid = ?  
				AND (bi.userid IS NULL OR bi.userid <> ue.userid)';
			
		$activities_records = $DB->get_records_sql($sql, array($USER->id, $USER->id));

		$activities = array();
		foreach ($activities_records as $ac) {
			$pos = strpos($ac->idnumber, '~'); // We know there's at least one
			$code = substr($ac->idnumber, ($pos + 1));
			$pos = strpos($code, '~');
			if ($pos === false) {
				$activity_type = '';
				$activity_category = '';
			} else {
				$activity_type = substr($code, 0, $pos);
				$activity_category = substr($code, ($pos + 1), ($pos + 1));
			}
			$activities[] = array(
				'activity_id' => $ac->id,
				'activity_name' => $ac->shortname,
				'activity_type' => $activity_type,
				'activity_category' => $activity_category,
				'activity_description' => $ac->summary,
				'signed_up' => $ac->timecreated
			);
		}
		
		return $activities;
	}
	
	public static function get_activities_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array (
					'activity_id' => new external_value(PARAM_INT, 'activity ID'),
					'activity_name' => new external_value(PARAM_TEXT, 'activity name'),
					'activity_type' => new external_value(PARAM_TEXT, 'activity type'),
					'activity_category' => new external_value(PARAM_TEXT, 'activity category'),
					'activity_description' => new external_value(PARAM_RAW, 'activity description'),
					'signed_up' => new external_value(PARAM_INT, 'The date when the user enroled for the activity')
                )
			)
        );
	}
	
		/* GET PRIVACY SETTINGS */
	public static function get_privacy_setting_parameters() {
		return new external_function_parameters(array());
	}

 	public static function get_privacy_setting() {

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);
		
        // Capability checking
//		require_capability('moodle/badge:view', $context);

		return array('badgeprivacysetting' => (get_user_preferences('badgeprivacysetting') == 1));
}

	public static function get_privacy_setting_returns() {
		return new external_single_structure(
			array (
				'badgeprivacysetting' => new external_value(PARAM_BOOL, 'The value of the preference')
			)
        );
	}

 	/* EXPORT */
	public static function get_export_parameters() {
		return new external_function_parameters(array());
	}		
	
 	public static function get_export() {
		return true;
	}
	
	public static function get_export_returns() {
		return new external_value(PARAM_TEXT, 'Exports badges from Moodle');
	}

/****
 * functions that update Moodle 
 * 
 ****/
	
	/* SET PRIVACY SETTINGS */
	public static function set_privacy_setting_parameters() {
		return new external_function_parameters(
			array(
				'badgeprivacysetting' => new external_value(PARAM_BOOL, 'The value of the preference')
			)
		);
	}
	
 	public static function set_privacy_setting($badgeprivacysetting) {
		global $DB, $USER;

		// Parameter validation
		$params = self::validate_parameters(
			self::set_privacy_setting_parameters(), array(
				'badgeprivacysetting' => $badgeprivacysetting
			)
		);
		
		// Context validation
		$context = context_system::instance();
		self::validate_context($context);
		
		// Database wants an INT value not a boolean so convert the preference
		if ($params['badgeprivacysetting']) {
			$value = 1;
		}
		else {
			$value = 0;
		}
		
		set_user_preference('badgeprivacysetting', $value);

		return array('badgeprivacysetting' => (get_user_preferences('badgeprivacysetting') == 1));
	}

	public static function set_privacy_setting_returns() {
		return new external_single_structure(
			array (
				'badgeprivacysetting'  => new external_value(PARAM_BOOL, 'The value of the preference')
			)
        );
	}	
	
	/* SIGN UP FOR A BADGE (IE ENROLL FOR THE CORRESPONDING COURSE)*/
	public static function set_badge_signup_parameters() {
		return new external_function_parameters(
			array(
				'courseid' => new external_value(PARAM_INT, 'The course ID of the badge to sign up for')
			)
		);
	}
	
 	public static function set_badge_signup($courseid) {
		global $USER;
		
		$params = self::validate_parameters(
			self::set_badge_signup_parameters(), array(
				'courseid' => $courseid
			)
		);

		// Check that the self enrolment plugin is installed
		$enrol = enrol_get_plugin('self');
		if (empty($enrol)) {
			throw new moodle_exception('canntenrol', 'enrol_self');
		}
		
		// Check a plugin instance exists (and is enabled) for this course
		$instance = null;
		$enrolinstances = enrol_get_instances($params['courseid'], true);
		foreach ($enrolinstances as $courseenrolinstance) {
			if ($courseenrolinstance->enrol == 'self') {
				$instance = $courseenrolinstance;
				break;
			}
		}
		if (empty($instance)) {
			throw new moodle_exception('canntenrol', 'enrol_self');
		}
		
		// Check that they can actually enroll
		if (!$enrol->can_self_enrol($instance, true)) {
			throw new moodle_exception('canntenrol', 'enrol_self');
		}

		// OK, enroll
		$enrol->enrol_self($instance);

		return array('courseid' => $params['courseid']);
	}

	public static function set_badge_signup_returns() {
		return new external_single_structure(
			array(
				'courseid' => new external_value(PARAM_INT, 'The course ID of the badge signed up for')
			)
		);
	}
	
	/* CANCEL A BADGE (IE UNENROLL FROM THE CORRESPONDING COURSE) */
	public static function set_badge_cancel_parameters() {
		return new external_function_parameters(
			array(
				'courseid' => new external_value(PARAM_INT, 'The course ID of the badge to cancel')
			)
		);
	}
	
 	public static function set_badge_cancel($courseid) {
		global $USER;
		
		$params = self::validate_parameters(
			self::set_badge_cancel_parameters(), array(
				'courseid' => $courseid
			)
		);

		// Check that the self enrolment plugin is installed
		$enrol = enrol_get_plugin('self');
		if (empty($enrol)) {
			throw new moodle_exception('canntenrol', 'enrol_self');
		}
		
		// Check a plugin instance exists (and is enabled) for this course
		$instance = null;
		$enrolinstances = enrol_get_instances($params['courseid'], true);
		foreach ($enrolinstances as $courseenrolinstance) {
			if ($courseenrolinstance->enrol == 'self') {
				$instance = $courseenrolinstance;
				break;
			}
		}
		if (empty($instance)) {
			throw new moodle_exception('canntenrol', 'enrol_self');
		}
		
		// Check thay can actually unenroll themselves from this course
        if (!$enrol->allow_unenrol($instance) || !has_capability("enrol/self:unenrolself", context_course::instance($params['courseid']))) {
			throw new moodle_exception('canntenrol', 'enrol_self');
		}

		// OK, unenroll
		$enrol->unenrol_user($instance, $USER->id);

		return array('courseid' => $params['courseid']);
 	}

	public static function set_badge_cancel_returns() {
		return new external_single_structure(
			array(
				'courseid' => new external_value(PARAM_INT, 'The course ID of the badge unenrolled from')
			)
		);
	}
	
	/***
	 * set message as READ
	 * (move message from mdl_messages table to mdl_messages_read table)
	 */

	public static function set_message_read_parameters() {
		return new external_function_parameters(
			array(
				'messageid' => new external_value(PARAM_INT, 'The id of the message that has been read'),
				'timeread' => new external_value(PARAM_INT, 'The time when the message was read')
			)
		);
	}
	
	public static function set_message_read($messageid, $timeread) {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/message/lib.php");

        // Check if private messaging between users is allowed.
        if (empty($CFG->messaging)) {
            throw new moodle_exception('disabled', 'message');
        }

        // Warnings array, it can be empty at the end but is mandatory.
        $warnings = array();

        // Validate params.
        $params = array(
            'messageid' => $messageid,
            'timeread' => $timeread
        );
        $params = self::validate_parameters(self::set_message_read_parameters(), $params);

        // Validate context.
        $context = context_system::instance();
        self::validate_context($context);

        $message = $DB->get_record('message', array('id' => $params['messageid']), '*', MUST_EXIST);

        if ($message->useridto != $USER->id) {
            throw new invalid_parameter_exception('Invalid messageid, you cannot mark this message as read');
        }

        $messageid = message_mark_message_read($message, $params['timeread']);

        $results = array(
            'messageid' => $messageid,
           //'warnings' => implode($warnings)
			'warnings' => $warnings
        );
        return $results;
    }

	public static function set_message_read_returns()  {
        return new external_single_structure(
            array(
                'messageid' => new external_value(PARAM_INT, 'the id of the message in the message_read table'),
                //'warnings' => new external_value(PARAM_TEXT, 'warnings output by the system')
                'warnings' => new external_warnings()
            )
        );
    }

	private static function get_faculties() { 
		global $DB, $USER;

		$role = $DB->get_record('role', array('shortname' => 'student'), 'id', MUST_EXIST);
		
		$sql = 'SELECT c.category'
			. ' FROM {user_enrolments} ue'
			. ' JOIN {enrol} e ON e.id = ue.enrolid'
			. ' JOIN {context} ct ON ct.instanceid = e.courseid'
			. ' JOIN {role_assignments} ra ON ra.contextid = ct.id'
			. ' JOIN {course} c ON c.id = e.courseid'
			. ' WHERE ue.userid = ?'
				. ' AND (e.enrol = "database" OR e.enrol = "databaseextended" OR e.enrol = "lmb")'
				. ' AND ct.contextlevel = 50'
				. ' AND ra.userid = ue.userid'
				. ' AND ra.roleid = ?'
				. ' AND c.idnumber LIKE "_~%"';
		$courses = $DB->get_records_sql($sql, array($USER->id, $role->id));
		
		$faculties = array();
		foreach ($courses as $course) {
			if ($course->category == 131) {
			   $faculties[] = 'TDE';
			}
			else if ($course->category == 132) {
			   $faculties[] = 'HSS';
			}
			else if ($course->category == 133) {
			   $faculties[] = 'HLS';
			}
			else if ($course->category == 134) {
			   $faculties[] = 'BUS';
			}
		}
		if (empty($faculties)) {
			$faculties[] = 'UNI';
		}
		
		return $faculties;
	}
}	
