<?php

class block_simple_rank_functions {

    // Function to get all of the enrolled students on a course and their total points value.
    public static function getEnrolledStudentsAndPoints($courseid) {
        
        // define global variables.
        global $DB, $OUTPUT, $COURSE;

        // First we need to remove all points that have been accumulated on different course pages.
        $points = $DB->get_records('block_simple_rank_points');
        $points = json_decode(json_encode($points), true);
        $students = array();
        foreach ($points as $value) {
            array_push($students, $value);
        }

        $items_to_keep = array();
        for ($i = 0; $i < count($students); $i++) {
            if ($students[$i]['courseid'] == $courseid) {
                array_push($items_to_keep, $students[$i]);
            }
        }

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
        $students = array_values($students);

        // Sort the array so that the students with the highest points will be at the top of the leaderboard
        $temp_sort = array();
        foreach ($students as $index) {
            array_push($temp_sort, $index['points']);
        }
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

        $students_sorted = array();
        foreach ($ordered_index_list as $index) {
            array_push($students_sorted, $students[$index]);
        }

        // Now get only necessary information - i.e. ID, student name, and points value.
        $users = json_decode(json_encode($DB->get_records('user')), true);
        $student_points = array();
        foreach($students_sorted as $value) {
            $temp_array = array();
            $temp_studentid = $value['studentid'];
            foreach ($users as $value2) {
                if ($temp_studentid == $value2['id']) {
                    array_push($temp_array, $value2['id']);
                    array_push($temp_array, $value2['firstname'] . ' ' . $value2['lastname']);
                }
            }
            array_push($temp_array, $value['points']);
            array_push($student_points, $temp_array);
        }

        // now need to get the students level also.
        $sql = "SELECT * FROM mdl_student_levels WHERE courseid=" . $courseid;
        $student_levels = $DB->get_records_sql($sql);
        $student_levels = json_decode(json_encode($DB->get_records_sql($sql)), true);

        $student_points_with_levels = array();
        foreach ($student_points as $student_point) {
            $levelFound = false;
            foreach ($student_levels as $student_level) {
                if ($student_point[0] == $student_level['studentid']) {
                    $levelFound = true;
                    array_push($student_point, $student_level['level']);
                    array_push($student_points_with_levels, $student_point);
                }
            }
            if (!$levelFound) {
                array_push($student_point, 0);
                array_push($student_points_with_levels, $student_point);
            }
        }

        return $student_points_with_levels;
    }

    // Function to get a list of all of the students on a selected team given a team and a course.
    public static function getAllStudentsOnTeam($teamID, $courseid) {

        // define global variables.
        global $DB;
        
        // First simply get a list of users enrolled on the course
        $context = context_course::instance($courseid);
        $users = get_enrolled_users($context);
        $users = json_decode(json_encode($users), true);

        // Now get a list of student ID's that belong to the specified team.
        $student_teams = $DB->get_records('student_teams');
        $student_teams = json_decode(json_encode($student_teams), true);
        $studentIDs_on_team = array();
        foreach ($student_teams as $student) {
            if ($student['teamid'] == $teamID) {
                array_push($studentIDs_on_team, $student['studentid']);
            }
        }

        // Get other information about the student.
        $students_on_team = array();
        foreach ($studentIDs_on_team as $studentID) {
            foreach ($users as $student) {
                // print_r($student);
                if ($studentID == $student['id']) {
                    $toPush = array();
                    array_push($toPush, $studentID);
                    array_push($toPush, $student['firstname'] . ' ' . $student['lastname']);

                    array_push($students_on_team, $toPush);
                }
            }
        }

        return $students_on_team;
    }

    // Expands on the method above, getting all the students on the team, but this time also getting the points total of the students.
    public static function getStudentsOnTeamWithPoints($teamID, $courseid) {

        $functions = new block_simple_rank_functions;
        $student_points = $functions->getEnrolledStudentsAndPoints($courseid);
        $students_on_team = $functions->getAllStudentsOnTeam($teamID, $courseid);
        $team_students_with_points = array();
        foreach($students_on_team as $student) {
            $has_points = false;
            foreach($student_points as $value) {
                if ($student[0] == $value[0]) {
                    $has_points = true;
                    array_push($team_students_with_points, array($student[1], $value[3], $value[2]));
                }
            }
            if (!$has_points) {
                print_r($has_points);
                array_push($team_students_with_points, array($student[1], 0, 0));
            }
        }
        return $team_students_with_points;

    }

    // Function to update a given students level in the student_levels table.
    public static function updateStudentLevel($studentid, $courseid) {

        // define global variables.
        global $DB;

        // First need an array holding all of the level_thresholds that have been set in the settings page.
        // Probably not the most elegant solution for this.
        $level_thresholds = array();
        for ($i = 1; $i <= 10; $i++) {
            array_push($level_thresholds, get_config('block_simple_rank', 'level_' . $i . '_threshold'));
        }
        // array_push($level_thresholds, get_config( 'block_simple_rank', 'level_1_threshold'));
        // array_push($level_thresholds, get_config('block_simple_rank', 'level_2_threshold'));
        // print_r($level_thresholds);
        // echo '<br>';

        // Get the speciic students points for the given course. 
        $functions = new block_simple_rank_functions;
        $all_student_points = $functions->getEnrolledStudentsAndPoints($courseid);
        // print_r($all_student_points);
        // echo '<br>';
        $individual_student_points = array();
        foreach ($all_student_points as $student) {
            if ($studentid == $student[0]) {
                $individual_student_points = $student[2];
            }
        }
        // print_r("Points student has: ");
        // print_r($individual_student_points);
        // echo '<br>';

        // Compare the students points to the level thresholds, and set the students level to the correct level.
        $student_level = 0;
        $levelSet = false;
        for ($i = 0; $i < count($level_thresholds); $i++) {
            if ($individual_student_points >= $level_thresholds[$i]) {
                $student_level = $i + 1;
                $levelSet = true;
            }
        }
        if (!$levelSet) {
            $student_level = 0;
        }
        print_r("Level student is: ");
        print_r($student_level);
        echo '<br>';

        // Then insert this record into the table if the student doesn't already have a record in the table.
        // Otherwise, simply update the level field instead of creating new records and deleting old ones.
        $record = new \stdClass();
        $record->studentid = $studentid;
        $record->courseid = $courseid;
        $record->level = $student_level;
        $sql = "SELECT * FROM mdl_student_levels WHERE studentid=$studentid AND courseid=$courseid";
        if (!$DB->record_exists_sql($sql)) {
            $DB->insert_record('student_levels', $record);
            // print_r($DB->get_records_sql($sql));
        }
        else {
            $DB->set_field_select('student_levels', 'level', $student_level, "studentid=" . $studentid . " AND courseid=" . $courseid);
            // print_r($DB->get_records_sql($sql));
        }
        if ($levelSet) {
            return $student_level;
        }
    }

    public static function getStudentLevel($studentid, $courseid) {
        
        global $DB;
        $current_student_level = 0;
        $sql = "SELECT level FROM mdl_student_levels WHERE studentid=$studentid AND courseid=$courseid";
        if ($DB->record_exists_sql($sql)) {
            $current_student_level = $DB->get_field_sql($sql);
        }
        return $current_student_level;
    }

    public static function getStudentProgressBar($studentid, $courseid) {

        // define global variables
        global $DB;

        $functions = new block_simple_rank_functions;
        $students = $functions->getEnrolledStudentsAndPoints($courseid);
        $student_points = 0;
        foreach ($students as $student) {
            if ($studentid == $student[0]) {
                $student_points = $student[2];
            }
        }

        // first need to get the students level for the given course.
        $current_student_level = 0;
        $sql = "SELECT level FROM mdl_student_levels WHERE studentid=$studentid AND courseid=$courseid";
        if ($DB->record_exists_sql($sql)) {
            $current_student_level = $DB->get_field_sql($sql);
        }
        $next_level = $current_student_level + 1;
        $points_to_next_level = get_config('block_simple_rank', 'level_' . $next_level . '_threshold') - $student_points;

        echo '<br>';
        echo 'Current student level: ';
        print_r($current_student_level);
        echo '<br>';
        if ($current_student_level != 10) {
            $difference_between_levels = get_config('block_simple_rank', 'level_' . $next_level . '_threshold') - get_config('block_simple_rank', 'level_' . $current_student_level . '_threshold');
            
            $student_points = $student_points - get_config('block_simple_rank', 'level_' . $current_student_level . '_threshold');
            // print_r($student_points);
            echo '<br>';


            $progress_bar = '<div class="progress_bar_container">
                <p class="progress_text"> Progress to next level </p>
                <label for="progress_bar" class="progress_label">' . $current_student_level . '</label>
                <progress id="progress_bar" value="' . $student_points .'" max="' . $difference_between_levels .'"> </progress> 
                <label for="progress_bar" class="progress_label">' . $next_level . '</label>
                <p class="progress_text">' . $points_to_next_level . ' points until level ' . $next_level . '</p>
            </div>';
            return $progress_bar;
        }
        else {
            $progress_bar = '<div class="progress_bar_container"> 
                <progress id="progress_bar" value="100" max="100"> 100% </progress> 
            </div>';
            return $progress_bar;
        }
        // $difference_between_levels = (int)get_config('block_simple_rank', 'level_' . (int)$current_student_level + 1 . '_threshold') - (int)get_config('block_simple_rank', 'level_' . $current_student_level . '_threshold');
        // echo 'Points difference: ';
        // print_r($difference_between_levels);

        // $progress_bar = '<div class="progress_bar_container"> 
        //     <progress id="progress_bar" value="' . $current_user_points .'" max="300"> </progress> 
        // </div>';
    }


    // public static function assignEventPoints()
}