<?php

require_once("$CFG->libdir/formslib.php");
// require_once ('test.html');
// require_once('edit_form.php');

class simple_rank_form extends moodleform {
    
    function definition() {
        
        // Define global variables.
        global $CFG, $DB, $OUTPUT, $COURSE, $USER;

        // Instantiate table and add column headers.
        $table = new html_table();
        $table->head = array('Name', 'Points');

        // Get an object of the fields from the database and then convert this into a 2D array
        $points = $DB->get_records('block_simple_rank_points');
        $temp_arr = json_decode(json_encode($points), true);
        $students = array();
        foreach ($temp_arr as $value) {
            array_push($students, $value);
        }
        echo "<br>";
        echo "<br>";
        echo "<br>";
        
        // Remove all elements that don't belong to the current course.
        $courseid  = $COURSE->id;
        $items_to_keep = array();
        for ($i = 0; $i < count($students); $i++) {
            if ($students[$i]['courseid'] == $courseid) {
                array_push($items_to_keep, $students[$i]);
            }
        }
        // print_r($items_to_keep);
        // echo "<br>";
        $students = $items_to_keep;

        // Add up all points values for a single student and then remove all non-unique student ID entries
        $indexes_to_remove = array();
        for ($i = 0; $i < count($students); $i++) {
            $points_to_add = 0;
            $current_student_id = $students[$i]['studentid'];
            if ($i !== count($students) - 1) {
                for ($j = $i + 1; $j < count($students); $j++) {
                    if ($students[$j]['studentid'] == $current_student_id) {
                        $points_to_add += $students[$j]['points'];
                        array_push($indexes_to_remove, $j);
                    }
                }
            }
            $students[$i]['points'] += $points_to_add;
        }
        foreach(array_unique($indexes_to_remove) as $index) {
            unset($students[$index]);
        }
        print_r("Record of all points generated: ");
        $students = array_values($students);
        print_r($students);
        echo "<br>";
        echo "<br>";


        // Sort the array so that the students with the highest points will be at the top of the leaderboard
        $temp_sort = array();
        foreach ($students as $index) {
            array_push($temp_sort, $index['points']);
        }
        print_r($temp_sort);
        echo "<br>";

        $ordered_student_points = array();
        $ordered_index_list = array();
        for ($i = 0; $i < count($temp_sort); $i++) {
            $maxNum = 0;
            $indexOfMax = -1;
            for ($j = 0; $j < count($temp_sort); $j++) {
                if ($temp_sort[$j] > $maxNum && !in_array($j, $ordered_index_list)) {
                    $maxNum = $temp_sort[$j];
                    $indexOfMax = $j;
                }
            }
            array_push($ordered_index_list, $indexOfMax);
        }
        print_r($ordered_index_list); 
        echo "<br>";

        $students_sorted = array();
        foreach ($ordered_index_list as $index) {
            array_push($students_sorted, $students[$index]);
        }


        // Get the first and last name of the student so that they can be displayed
        // Append the points value also.
        $users = json_decode(json_encode($DB->get_records('user')), true);
        $student_points = array();
        foreach($students_sorted as $value) {
            $temp_array = array();
            $temp_studentid = $value['studentid'];
            foreach ($users as $value2) {
                if ($temp_studentid == $value2['id']) {
                    array_push($temp_array, $value2['firstname'] . ' ' . $value2['lastname']);
                    // array_push($temp_array, $value2['lastname']);
                }
            }
            array_push($temp_array, $value['points']);
            array_push($student_points, $temp_array);
        }

        print_r("Total points for this course: ");
        print_r($student_points);
        echo "<br>";
        echo "<br>";
        print_r("Points for forum post: " . get_config('block_simple_rank', 'forumpoints'));
        echo "<br>";
        print_r("Students to display on leaderboard: " . get_config('block_simple_rank', 'students_to_display_on_leaderboard'));
        echo "<br>";
        print_r("Course ID is: " . $courseid);
        echo "<br>";
        print_r("ID of user viewing is: " . $USER->id);
        echo "<br>";


        // If a max number of students to display has been set then make sure no more than that amount
        // are displayed.
        $students_to_display = get_config('block_simple_rank', 'students_to_display_on_leaderboard');
        if ($students_to_display == null || count($student_points) < $students_to_display) {
            // print_r("null");
            foreach ($student_points as $value) {
                $row = new html_table_row($value);
                $table->data[] = $row;
            }
        }
        else {
            $x = get_config('block_simple_rank', 'students_to_display_on_leaderboard');
            for ($i = 0; $i < $x; $i++) {
                $row = new html_table_row($student_points[$i]);
                $table->data[] = $row;
            }
        }

        $mform = $this->_form;
        
        // $url = new moodle_url('/blocks/simple_rank/edit_form.php', array('id' => $courseid));

        $mform->addElement('html', '<h2 id="top_x_students">Top ' . $students_to_display . ' students </h2>');
        $mform->addElement('html', '<h5 id="top_x_students">Your rank is: ' . 'placeholder</h5>');
        $mform->addElement('html', html_writer::table($table));

        if ($students_to_display != null && (count($student_points) != $students_to_display && count($student_points) > $students_to_display)) {
            $mform->addElement('header', 'full_leaderboard_header', 'View full leaderboard');
            $table_full = new html_table();
            $table_full->head = array('Name', 'Points');
            foreach ($student_points as $value) {
                $row = new html_table_row($value);
                $table_full->data[] = $row;
            }
            $mform->addElement('html', html_writer::table($table_full));
            $mform->setExpanded('full_leaderboard_header', false);
        }

        $url = new moodle_url('/blocks/simple_rank/index.php', array('courseid' => $courseid, 'studentid' => $USER->id));
        $to_index_page = '<form action="blocks/simple_rank/index.php?courseid=' . $courseid . '&studentid=' . $USER->id . '" method="POST">';
        $to_index_page .= '<div class="button_container"> <button id="show_full_teams_table"> View members of the selected team </button> </div>';
        $to_index_page .= '</form>';
        $mform->addElement('html', $to_index_page);
        echo $to_index_page;

        // $url = new moodle_url('/blocks/simple_rank/index.php', array('id' => $courseid, 'studentid' => $USER->id));
        // $admins = get_admins();
        // foreach($admins as $admin) {
        //     if ($USER->id == $admin->id) {
        //         $mform->addElement('header', 'admin_only', 'Group settings');
        //         $mform->setExpanded('admin_only', false);
        //         // $output = "test";
        //         $output = $OUTPUT->single_button($url, 'View leaderboard in full', 'GET', array('id' => $courseid, 'studentid' => $USER->id));
        //         $mform->addElement('html', $output);
        //         // echo $output;
        //     }
        // }

        // if (user_has_role_assignment($USER->id, 5)) {
        //     $mform->addElement('button', 'view_full', 'View full leaderboard');
        // }

        // $students_test = array();
        // foreach ($students as $value) {
        //     array_push($students_test, $value[0] . ' ' . $value[1]);
        // }
        // $mform->addElement('header', 'headerconfig', 'Teams');
        // $mform->addElement('select', 'colors', 'colours mate', $students_test);
        // $mform->addElement('submit', 'submitbutton', 'submit');
        // echo $OUTPUT->single_button($url, 'test', 'get');
        
    }

}