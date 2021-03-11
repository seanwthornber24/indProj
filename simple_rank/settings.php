<?php

// defined('MOODLE_INTERNAL') || die();

// global $DB;
// $users = $DB->get_records('user');
// $test = json_decode(json_encode($users), true);
// // print_r($test);
// $fname_lastname = array();
// foreach($test as $value) {
//     // print_r($value);
//     array_push($fname_lastname, $value['firstname'] . ' ' . $value['lastname']);
// }
// print_r($fname_lastname);
defined('MOODLE_INTERNAL') || die();

if($ADMIN->fulltree) {

        $settings->add(new admin_setting_heading(
                'headerconfig',
                'Points awarded',
                'Configure the points awarded for certain tasks'
            ));
    
        $settings->add(new admin_setting_configtext(
                'block_simple_rank/forumpoints',
                'Points for a forum post',
                'How many points should be awarded for a forum post?',
                10 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/forumreplypoints',
                'Points for a forum reply',
                'How many points should be awarded for a forum reply?',
                15 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/feedbacksubmittedpoints',
                'Points for submitting feedback',
                'How many points should be awarded when a student submits feedback?',
                30 # default value
            ));


        $settings->add(new admin_setting_heading(
                'headerconfig2',
                'Level thresholds',
                'Configure the points needed to reach different levels'
            ));
            
        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_1_threshold',
                'Level 1 points threshold',
                'How many points are required to reach level one?',
                10 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_2_threshold',
                'Level 2 points threshold',
                'How many points are required to reach level two?',
                50 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_3_threshold',
                'Level 3 points threshold',
                'How many points are required to reach level three?',
                100 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_4_threshold',
                'Level 4 points threshold',
                'How many points are required to reach level four?',
                150 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_5_threshold',
                'Level 5 points threshold',
                'How many points are required to reach level five?',
                200 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_6_threshold',
                'Level 6 points threshold',
                'How many points are required to reach level six?',
                200 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_7_threshold',
                'Level 7 points threshold',
                'How many points are required to reach level seven?',
                200 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_8_threshold',
                'Level 8 points threshold',
                'How many points are required to reach level eight?',
                250 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_9_threshold',
                'Level 9 points threshold',
                'How many points are required to reach level nine?',
                300 # default value
            ));

        $settings->add(new admin_setting_configtext(
                'block_simple_rank/level_10_threshold',
                'Level 10 points threshold',
                'How many points are required to reach level ten?',
                350 # default value
            ));

    }