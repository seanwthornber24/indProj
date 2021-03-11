<?php

// This file is part of Moodle - http://moodle.org/
//
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

require_once '../../config.php';
require_once("$CFG->libdir/formslib.php");
global $USER, $DB, $CFG;

// parameters that need to be passed to the page
$studentid = required_param('studentid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

// dunno if this is needed i'll be honest
$data = new stdClass();
$data->studentid = $studentid;
$data->courseid = $courseid;

$PAGE->set_url('/blocks/simple_rank/group_form_student_view.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading('Your team page');

echo $OUTPUT->header();

// First get an array of teams that belong to this specific course.
$teams_DB = $DB->get_records('block_simple_rank_teams');
$teams_DB = json_decode(json_encode($teams_DB), true);
$teams = array();
foreach($teams_DB as $team) {
    if ($team['courseid'] == $courseid) {
        array_push($teams, array($team['id'], $team['teamname']));
    }
}
$teamIDs = array();
foreach($teams as $team) {
    array_push($teamIDs, $team[0]);
}

// Then find the team (if any) that the student belongs to
$teamID_student_belongs_to = -1;
$student_teams = $DB->get_records('student_teams');
$student_teams = json_decode(json_encode($student_teams), true);
foreach($student_teams as $student_team) {
    if ($student_team['studentid'] == $studentid) {
        if (in_array($student_team['teamid'], $teamIDs)) {
            $teamID_student_belongs_to = $student_team['teamid'];
        }
    }
}
$team_name_student_belongs_to = '';
foreach($teams as $team) {
    if ($team[0] == $teamID_student_belongs_to) {
        $team_name_student_belongs_to = $team[1];
    }
}

if ($team_name_student_belongs_to != null) {
    echo '<h3 class="teams_page_header">' . $team_name_student_belongs_to . '</h3>';

    $table = new html_table();
    $table->head = array('Student Name', 'Level', 'Points');

    $functions = new block_simple_rank_functions;
    // $functions->updateStudentLevel($USER->id, $courseid);
    $team_students_with_points = $functions->getStudentsOnTeamWithPoints($teamID_student_belongs_to, $courseid);
    foreach($team_students_with_points as $student) {
        if ($student[2] == null) {
            $student[2] = 0;
        }
        $row = new html_table_row($student);
        $table->data[] = $row;
    }

    $table_output = '<p1 id="team_table">' . html_writer::table($table) . '</p1>';
    echo $table_output;
}
else {
    echo '<h3 class="teams_page_header"> You aren\'t yet part of a team, try again later! </h3>';
}


// class group_creator_form extends moodleform {

//     /**
//      * A form for selecting a starting date and an ending date.
//      *
//      * @return void
//      */
//     public function definition() {
//         $mform = & $this->_form; 
//         $mform->addElement('header', 'h', 'testing header');

//         // The form elements for selecting dates with defaults set to the current date range.
//         $mform->addElement('html', 'test <br>');
//         $mform->addElement('html', 'bruh');


//         $select = $mform->addElement('select', 'colours', 'placeholder', array('red', 'blue', 'green'));
//         $select->setMultiple(true);

//         // $mform->addElement('hmtl', $USER->id);
//         // The buttons to update the leaderboard with new dates or reset to the default dates.
//         $buttonarray = array();
//         $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('update', 'block_leaderboard'));
//         $buttonarray[] = $mform->createElement('cancel', 'resetbutton', get_string('resettodefault', 'block_leaderboard'));
//         $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

//     }
// }

// $mform = new group_creator_form();
// $mform->display();
// phpinfo();
echo $OUTPUT->footer();


// return $output;



