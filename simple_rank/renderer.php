<?php

defined('MOODLE_INTERNAL') || die;
require_once('edit_form.php');

class block_simple_rank_renderer extends plugin_renderer_base {

    public function simple_rank_block_individual($num_students_to_display) {

        // Define global variables.
        global $CFG, $DB, $OUTPUT, $COURSE, $USER;



        $functions = new block_simple_rank_functions;
        $currentLevelBeforeUpdate = $functions->getStudentLevel($USER->id, $COURSE->id);
        $changedLevel = $functions->updateStudentLevel($USER->id, $COURSE->id);
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "<br>";
        print_r($currentLevelBeforeUpdate);
        echo "<br>";
        print_r($changedLevel);
        echo "<br>";
        if ($changedLevel > $currentLevelBeforeUpdate) {
            echo '<script language="javascript">';
            echo 'alert("Congratulations, you have reached level ' . $changedLevel . '!")';
            echo '</script>';
        }

        // Instantiate table and add column headers.
        $table = new html_table();
        $table->head = array('Rank', 'Name', 'Level', 'Points');

        // Get an object of the fields from the database and then convert this into a 2D array
        $courseid = $COURSE->id;
        $functions = new block_simple_rank_functions;
        $student_points = $functions->getEnrolledStudentsAndPoints($courseid);
        
        print_r("Total points for this course: ");
        print_r($student_points);
        echo "<br>";
        echo "<br>";
        print_r("Points for forum post: " . get_config('block_simple_rank', 'forumpoints'));
        echo "<br>";
        print_r("Students to display on leaderboard: " . $num_students_to_display);
        echo "<br>";
        print_r("Course ID is: " . $courseid);
        echo "<br>";
        print_r("ID of user viewing is: " . $USER->id);
        echo "<br>";

        $current_user_points = null;
        foreach($student_points as $value) {
            if ($USER->id == $value[0]) {
                $current_user_points = $value[2];
            }
        }

        // So that we can display the rank column, we also need to push the students index in the sorted
        // list + 1 (so as to offset 0).
        $tmp = array();
        for ($i = 0; $i < count($student_points); $i++) {
            $toPush = array();
            array_push($toPush, $i + 1);
            array_push($toPush, $student_points[$i][1]);
            array_push($toPush, $student_points[$i][3]);
            array_push($toPush, $student_points[$i][2]);
            array_push($tmp, $toPush);
        }
        $student_points = $tmp;
        echo "<br>";
        print_r("Total points for this course: ");
        print_r($student_points);
        

        // If a max number of students to display has been set then make sure no more than that amount
        // are displayed.
        // $students_to_display = get_config('block_simple_rank', 'num_students_to_display_on_leaderboard');
        if ($num_students_to_display == null || count($student_points) < $num_students_to_display) {
            // print_r("null");
            foreach ($student_points as $value) {
                $row = new html_table_row($value);
                $table->data[] = $row;
            }
        }
        else {
            $x = $num_students_to_display;
            for ($i = 0; $i < $x; $i++) {
                $row = new html_table_row($student_points[$i]);
                $table->data[] = $row;
            }
        }

        // Make sure the title of the block does not say a greater number of students than there are to
        // display.
        // e.g. doesn't say top 6 students when only 5 exist in database for example.
        if ($num_students_to_display < count($student_points)) {
            $output = '<h2 id="top_x_students_title">Top ' . $num_students_to_display . ' students</h2>';
        }
        else if (count($student_points) == 0) {
            $output = '<h2 id="top_x_students_title">Currently no students to display </h2>';
        }
        else {
            $output = '<h2 id="top_x_students_title">Top ' . count($student_points) . ' students</h2>';
        }

        if ($current_user_points != null) {
            $output .= '<p id="top_x_students_subtitle">You have ' .  $current_user_points . ' points</p>';
        }
        else {
            $output .= '<p id="top_x_students_subtitle">You have 0 points, participate in the course to earn some!</p>';
        }
        $output .= $functions->getStudentProgressBar($USER->id, $courseid);

        $sql = "SELECT * FROM mdl_student_levels";
        if ($DB->record_exists_sql($sql)){
            $output .= '<p1>' . html_writer::table($table) .'</p1>';
        }
        // If we're displaying only x amount of students, and x is less than the number of students that have points
        // then display a button that allows us to view the full leaderboard.
        if ($num_students_to_display != null && (count($student_points) != $num_students_to_display && count($student_points) > $num_students_to_display)) {
            $table_full = new html_table();
            $table_full->head = array('Rank', 'Name', 'Level', 'Total Points');
            // foreach ($student_points as $value) {
            //     $row = new html_table_row($value);
            //     $table_full->data[] = $row;
            // }
            for ($i = $num_students_to_display; $i < count($student_points); $i++) {
                $row = new html_table_row($student_points[$i]);
                $table_full->data[] = $row;
            }
            $output .= '<div class="button_container">
                <button id="show_full_leaderboard_button" onclick="showTable();changeButtonText();">View rest of the leaderboard</button>' . 
            '</div>' . 
            '<p id="full_leaderboard">' . '<br>' . html_writer::table($table_full) . '</p>' . 
            '<script>
            function showTable() {
                var fullTable = document.getElementById("full_leaderboard");
                if (fullTable.style.display === "block") {
                    fullTable.style.display = "none";
                } else {
                    fullTable.style.display = "block";
                }
            }

            function changeButtonText() {
                var button = document.getElementById("show_full_leaderboard_button")
                if (button.innerHTML == "View rest of the leaderboard") {
                    button.innerHTML = "Hide rest of the leaderboard";
                }
                else {
                    button.innerHTML = "View rest of the leaderboard";
                }
            }
            </script>';
        }

        if (user_has_role_assignment($USER->id, 5)) {
            $url = new moodle_url('/blocks/simple_rank/group_form_student_view.php', array('courseid' => $courseid, 'studentid' => $USER->id));
            $output .= $OUTPUT->single_button($url, 'View your team page', 'get');
        }

        $admins = get_admins();
        foreach($admins as $admin) {
            if ($USER->id == $admin->id) {
                $url = new moodle_url('/blocks/simple_rank/group_form_admin_view.php', array('courseid' => $courseid, 'studentid' => $USER->id));
                $output .= '<br>' . '<br>' . $OUTPUT->single_button($url, 'Configure student teams', 'get');
            }
        }

        // $output .= $OUTPUT->heading('test');

        return $output;
    }

    public function simple_rank_block_team($num_teams_to_display) {

        echo '<br>';
        echo '<br>';
        echo '<br>';

        // Define global variables.
        global $CFG, $DB, $OUTPUT, $COURSE, $USER;

        // Get all of the enrolled students and their points.
        $courseid = $COURSE->id;
        $functions = new block_simple_rank_functions;
        $student_points = $functions->getEnrolledStudentsAndPoints($courseid);
        print_r($student_points);
        echo '<br>';

        // basically for each enrolled student, add the team ID(s) that they are a part of into an array
        // by checking against student teams DB. Then, check this against teams DB and add the team name
        // ONLY if it belongs to the course you're on. We can then use our function getAllStudentsOnTeam
        // and total up the points.

        $student_teams = $DB->get_records('student_teams');
        $student_teams = json_decode(json_encode($student_teams), true);
        $all_teamIDs = array();
        foreach ($student_points as $student) {
            foreach($student_teams as $team) {
                if ($student[0] == $team['studentid'] && !in_array($team['teamid'], $all_teamIDs)) {
                    array_push($all_teamIDs, $team['teamid']);
                }
            }
        }
        print_r($all_teamIDs);
        echo '<br>';

        $teams = $DB->get_records('block_simple_rank_teams');
        $teams = json_decode(json_encode($teams), true);
        $teams_on_course = array();
        foreach($all_teamIDs as $teamID) {
            foreach ($teams as $team) {
                if ($team['id'] == $teamID && $team['courseid'] == $courseid) {
                    array_push($teams_on_course, array($teamID, $team['teamname']));
                }
            }
        }
        print_r($teams_on_course);
        echo '<br>';

        $teams_on_course_with_points = array();
        foreach($teams_on_course as $team) {
            $total_points = 0;
            foreach($functions->getStudentsOnTeamWithPoints($team[0], $courseid) as $student) {
                $total_points += $student[2];
            }
            array_push($teams_on_course_with_points, array($team[1], $total_points));
        }
        print_r($teams_on_course_with_points);

        $tmp = array();
        for ($i = 0; $i < count($teams_on_course_with_points); $i++) {
            $toPush = array();
            array_push($toPush, $i + 1);
            array_push($toPush, $teams_on_course_with_points[$i][0]);
            array_push($toPush, $teams_on_course_with_points[$i][1]);
            array_push($tmp, $toPush);
        }
        $teams_on_course_with_points = $tmp;

        $table = new html_table();
        $table->head = array('Rank', 'Team name', 'Total points');
        if ($num_teams_to_display == null || count($teams_on_course_with_points) < $num_teams_to_display) {
            // print_r("null");
            foreach ($teams_on_course_with_points as $value) {
                $row = new html_table_row($value);
                $table->data[] = $row;
            }
        }
        else {
            $x = $num_teams_to_display;
            for ($i = 0; $i < $x; $i++) {
                $row = new html_table_row($teams_on_course_with_points[$i]);
                $table->data[] = $row;
            }
        }

        $output = '';
        if ($num_teams_to_display < count($teams_on_course_with_points)) {
            $output = '<h2 id="top_x_students_title">Top ' . $num_teams_to_display . ' teams</h2>';
        }
        else {
            $output = '<h2 id="top_x_students_title">Top ' . count($teams_on_course_with_points) . ' teams</h2>';
        }
        $output .= '<br>';
        $output .= '<p1>' . html_writer::table($table) .'</p1>';

        if ($num_teams_to_display != null && (count($teams_on_course_with_points) != $num_teams_to_display && count($teams_on_course_with_points) > $num_teams_to_display)) {
            $table_full = new html_table();
            $table_full->head = array('Rank', 'Team Name', 'Total Points');

            for ($i = $num_teams_to_display; $i < count($teams_on_course_with_points); $i++) {
                $row = new html_table_row($teams_on_course_with_points[$i]);
                $table_full->data[] = $row;
            }
            $output .= '<div class="button_container">
                <button id="show_full_leaderboard_button" onclick="showTable();changeButtonText();">View rest of the leaderboard</button>' . 
            '</div>' . 
            '<p id="full_leaderboard">' . '<br>' . html_writer::table($table_full) . '</p>' . 
            '<script>
            function showTable() {
                var fullTable = document.getElementById("full_leaderboard");
                if (fullTable.style.display === "block") {
                    fullTable.style.display = "none";
                } else {
                    fullTable.style.display = "block";
                }
            }

            function changeButtonText() {
                var button = document.getElementById("show_full_leaderboard_button")
                if (button.innerHTML == "View rest of the leaderboard") {
                    button.innerHTML = "Hide rest of the leaderboard";
                }
                else {
                    button.innerHTML = "View rest of the leaderboard";
                }
            }
            </script>';
        }

        if (user_has_role_assignment($USER->id, 5)) { // i.e. user is student
            $url = new moodle_url('/blocks/simple_rank/group_form_student_view.php', array('courseid' => $courseid, 'studentid' => $USER->id));
            $output .= '<br>' . '<br>' . $OUTPUT->single_button($url, 'View your team page', 'get');
        }
        $admins = get_admins();
        foreach($admins as $admin) {

            if ($USER->id == $admin->id) {
                $url = new moodle_url('/blocks/simple_rank/group_form_admin_view.php', array('courseid' => $courseid, 'studentid' => $USER->id));
                $output .= '<br>' . '<br>' . $OUTPUT->single_button($url, 'Configure student teams', 'get');
            }
        }

        return $output;

    }

}
