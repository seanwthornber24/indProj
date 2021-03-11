<?php

defined('MOODLE_INTERNAL') || die();

class block_simple_rank_observer {

    // Event to be carried out when a discussion is created in a course forum.
    public static function discussion_created_handler(\mod_forum\event\discussion_created $event) {

        global $DB, $USER, $CFG;
        
        // remember to uncomment this later
        // if (user_has_role_assignment($USER->id, 5)) { 

            // Create data for table and insert it into the table, call the function to update the students level at the end.
            $points = new stdClass();
            $points->studentid = $event->userid;
            // echo str_repeat("a", 5000); // just a test to see if this code executes
            $points->courseid = $event->courseid;
            $points->points = get_config('block_simple_rank', 'forumpoints');
            // print_r($points);
            $DB->insert_record('block_simple_rank_points', $points);

            // echo '<br>';
            // $message = new \core\message\message();
            // $message->component = 'block_simple_rank'; // Your plugin's name
            // $message->name = 'posts';
            // $message->userto = core_user::get_user($event->userid);
            // $message->userfrom = core_user::get_noreply_user();
            // $messageid = message_send($message);
            // redirect($CFG->wwwroot . '/course/view.php?id=' . $event->courseid, '<h4 id="points_notif">You have just gained ' . get_config('block_simple_rank', 'forumpoints') . ' points!</h4>');

        // }

    }

    public static function reply_created_handler(\mod_forum\event\post_created $event) {

        // echo str_repeat('a', 5000);
        // echo $event->courseid;
        global $DB, $USER, $CFG;
        $points = new stdClass();
        $points->studentid = $event->userid;
        $points->courseid = $event->courseid;
        $points->points = get_config('block_simple_rank', 'forumreplypoints');
        $DB->insert_record('block_simple_rank_points', $points);
        // $functions = new block_simple_rank_functions;
        // $functions->updateStudentLevel($event->userid, $event->courseid);
        // redirect($CFG->wwwroot . '/course/view.php?id=' . $event->courseid, '<h4 id="points_notif">You have just gained ' . get_config('block_simple_rank', 'forumreplypoints') . ' points!</h4>');

    }

    public static function feedback_submitted_handler(\mod_feedback\event\response_submitted $event) {

        global $DB, $USER, $CFG;
        $points = new stdClass();
        $points->studentid = $event->userid;
        $points->courseid = $event->courseid;
        $points->points = get_config('block_simple_rank', 'feedbacksubmittedpoints');
        $DB->insert_record('block_simple_rank_points', $points);

    }
    
}