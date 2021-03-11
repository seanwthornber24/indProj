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


// class group_creator_form extends moodleform {

//     /**
//      * A form for selecting a starting date and an ending date.
//      *
//      * @return void
//      */
//     public function definition() {
//         $mform = & $this->_form; 
//         // $mform->addElement('header', 'h', 'testing header');

//         // Parameters required for the page to load.
//         $mform->addElement('hidden', 'studentid');
//         $mform->setType('studentid', PARAM_INT);
//         $mform->addElement('hidden', 'courseid');
//         $mform->setType('courseid', PARAM_INT);

//         // The form elements for selecting dates with defaults set to the current date range.
//         // $mform->addElement('html', 'test <br>');
//         // $mform->addElement('html', 'bruh');


//         // $select = $mform->addElement('select', 'colours', 'placeholder', array('red', 'blue', 'green'));
//         // $select->setMultiple(true);

//         // $mform->addElement('hmtl', $USER->id);
//         // The buttons to update the leaderboard with new dates or reset to the default dates.
//         $buttonarray = array();
//         $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('update', 'block_leaderboard'));
//         $buttonarray[] = $mform->createElement('cancel', 'resetbutton', get_string('resettodefault', 'block_leaderboard'));
//         $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        
//     }
// }

// parameters that need to be passed to the page
$studentid = required_param('studentid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

// dunno if this is needed i'll be honest
$data = new stdClass();
$data->studentid = $studentid;
$data->courseid = $courseid;

// $student_teams = $DB->get_records('student_teams');
// $student_teams = json_decode(json_encode($student_teams), true);
// print_r($student_teams);

// Get the ID, fname and lname of the students on the course.
// Need to only get those who aren't already in a team, so need to consult student_teams database and
// only add if studentID !exists in there.
$teams_DB = $DB->get_records('block_simple_rank_teams');
$teams_DB = json_decode(json_encode($teams_DB), true);

$student_teams = $DB->get_records('student_teams');
$student_teams = json_decode(json_encode($student_teams), true);
$already_in_team = array();
foreach ($student_teams as $student) {
    $teamID = $student['teamid'];
    $course_team_belongs_to = -1;
    foreach ($teams_DB as $team) {
        if ($teamID == $team['id']) {
            $course_team_belongs_to = $team['courseid'];
        }
    }
    if ($course_team_belongs_to == $courseid) {
        array_push($already_in_team, $student['studentid']);
    }
}

$context = context_course::instance($courseid);
$users = get_enrolled_users($context);
$users = json_decode(json_encode($users), true);
$fname_lastname = array();
foreach($users as $value) {
    // print_r($value);
    if (!in_array($value['id'], $already_in_team)) {
        $tmp = array();
        array_push($tmp, $value['id']);
        array_push($tmp, $value['firstname'] . ' ' . $value['lastname']);
        array_push($fname_lastname, $tmp);
    }
}
// echo count($fname_lastname);
// print_r($fname_lastname); // might want to sort this
// print_r(count($user_records));
// print_r($test);


// $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// $PAGE->requires->js(new moodle_url('/blocks/simple_rank/javascript/functions.js'));
$PAGE->set_url('/blocks/simple_rank/group_form_admin_view.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading('Group Form');

echo $OUTPUT->header();

// $DB->delete_records('block_simple_rank_teams');
// $DB->delete_records('student_teams');

$teams_DB = $DB->get_records('block_simple_rank_teams');
$temp_arr = json_decode(json_encode($teams_DB), true);
$teams = array();
$teams = array_values($teams);
    foreach ($temp_arr as $value) {
        if ($courseid == $value['courseid']) {
            $tmp = array();
            array_push($tmp, $value['id']);
            array_push($tmp, $value['teamname']);
            array_push($teams, $tmp);
        }
    }
$table = new html_table();
$table->head = array('Student ID', 'Student Name');
foreach($teams as $team) {
    $row = new html_table_row($team);
    $table->data[] = $row;
}
$table_output = '<p1 id="teams_table">' . html_writer::table($table) . '</p1>';

// echo $course;
echo '<h3 class="teams_page_header">View teams</h3>';
// echo "<p> just testing baybee </p>";
// echo "student ID is: " . $studentid . '<br>';
// echo "course ID is: " . $courseid . '<br>' . '<br>';
// $sql = "SELECT id FROM block_simple_rank_teams WHERE teamname='bruhh'";
// echo $DB->get_field_sql($sql);

$viewTeams = '<form action="group_form_admin_view.php?courseid=' . $courseid . '&studentid=' . $studentid . '" method="POST">';

$select_team_to_display = '<div class="button_container"> <label for="teams">Select a team to display:</label>
<select name="teams" id="teams">';
foreach($teams as $team) {
    $select_team_to_display .= '<option value="' . implode(",", array($team[0], $team[1])) . '">' . implode(",", array($team[0], $team[1])) . '</option>';
}
$select_team_to_display .= '</select> </div>';

$viewTeamsButton = '<div class="button_container"> <button id="show_full_teams_table"> View members of the selected team </button> </div>';



$viewTeams .= $select_team_to_display;
$viewTeams .= $viewTeamsButton;
// $viewTeams .= $table_output;
$viewTeams .= '</form>';
echo $viewTeams;
if(isset($_POST['teams'])) {
    // echo $table_output;
    $teamID = explode(",", $_POST['teams'])[0];
    $functions = new block_simple_rank_functions;
    $students_on_team = $functions->getAllStudentsOnTeam($teamID, $courseid);
    
    $table = new html_table();
    $table->head = array('Student ID', 'Student Name');
    foreach($students_on_team as $student) {
        $row = new html_table_row($student);
        $table->data[] = $row;
    }
    $table_output = '<p1 id="team_table">' . html_writer::table($table) . '</p1>';
    // $output = '<form action="group_form_admin_view.php?courseid=' . $courseid . '&studentid=' . $studentid . '" method="POST">';
    $output = '<br>' . '<h5> Viewing students on team: ' . explode(",", $_POST['teams'])[1] . '</h5>';
    $output .= $table_output;
    echo $output;
}

// echo $table_output . '<br>';

// $start = required_param('start', PARAM_RAW);
// $end = required_param('end', PARAM_RAW);

// $mform = new group_creator_form();
// $mform->set_data($data);

// $multiselect = '<select class="students_multiselect" name="students" id="students_multiselect" multiple>';
// foreach ($fname_lastname as $student) {
//     $multiselect .= '<option value="' . $student . '">' . $student . '</option>';
// }
// $multiselect .= '</select>';
// $multiselect .= '<div class="result"></div>';
// $multiselect .= '<script>
//         selectElement = document.querySelector(\'.students_multiselect\');
        
//         selectElement.addEventListener(\'change\', (event) => {
//         const result = document.querySelector(\'.result\');
//         result.textContent = `You like ${event.target.value}`;
//         });
// </script>';
// echo $multiselect;


// if ($mform->get_data()) {
//     // print_r($mform->select);
//     $url = new moodle_url('/course/view.php', array('id' => $courseid));
//     redirect($url);
// }

// $mform->display();

echo '<hr class="divider"';
echo '<br>';
echo '<h3 class="teams_page_header">Delete teams</h3>';

$delete_team = '<form id="form_delete" method="POST">';
$select_team_to_delete = '<div class="button_container"> <label for="teams">Select a team to delete:</label>
<select name="team_to_delete" id="team_to_delete">';
foreach($teams as $team) {
    $select_team_to_delete .= '<option value="' . implode(",", array($team[0], $team[1])) . '">' . implode(",", array($team[0], $team[1])) . '</option>';
}
$select_team_to_delete .= '</select> </div>';
$delete_team .= $select_team_to_delete;
$delete_team .= '<div class="button_container"> <button id="delete_button" name="delete_button" onclick="confirmDelete()"> Delete selected team </button> </div>';
$delete_team .= '<script>
function confirmDelete() {
    if (confirm(\'Are you sure you want to delete this team?\')) {
        yourformelement.submit();
    } else {
        return false;
    }
 }

</script>';
$delete_team .= '</form>';
echo $delete_team;

if (isset($_POST['delete_button'])) {
    $teamID = explode(",", $_POST['team_to_delete'])[0];
    // $sql = "SELECT * FROM mdl_student_teams WHERE teamid=" . $teamID;
    // $test_arr = $DB->get_records_sql($sql);
    // print_r($test_arr);
    // echo '<br>';

    // $sql = "SELECT * FROM mdl_block_simple_rank_teams WHERE id=" . $teamID;
    // $test_arr = $DB->get_records_sql($sql);
    // print_r($test_arr);
    $sql_where = "teamid=" . $teamID;
    $DB->delete_records_select('student_teams', $sql_where);
    $sql_where = "id=" . $teamID;
    $DB->delete_records_select('block_simple_rank_teams', $sql_where);
    echo '<meta http-equiv="refresh" content="0">';
}

/////////////////

echo '<hr class="divider"';
echo '<br>';
echo '<h3 class="teams_page_header">Create teams</h3>';
echo '<br>';
$selectGroups = '<form id="form_test" action="group_form_admin_view.php?courseid=' . $courseid . '&studentid=' . $studentid . '" method="POST">';

$team_name_field = '<label for="team_name">Team name:</label>
<input type="text" id="team_name" name="team_name"><br><br>';

$multiselect = '<select id="students_multiselect" name="students[]" id="students_multiselect" multiple>';
foreach ($fname_lastname as $student) {
    $multiselect .= '<option value="' . implode(",", array($student[0], $student[1])) . '">' . implode(",", array($student[0], $student[1])) . '</option>';
}
$multiselect .= '</select>';

$selectGroups .= $team_name_field;
$selectGroups .= $multiselect;
$selectGroups .= '<br>';
$selectGroups .= '<br>';
$selectGroups .= '<button id="create_team_button" onclick="confirmCreate()"> Create team of selected students </button>';
$selectGroups .= '<script>
function confirmCreate() {
    if (confirm(\'Are you sure you want to create this team?\')) {
        yourformelement.submit();
    } else {
        return false;
    }
 }

</script>';
$selectGroups .= '</form>';
echo $selectGroups;
echo '<br';
echo '<br>';
echo '<br>';
// phpinfo();

if(isset($_POST['students'])) {
    // echo $_POST['team_name'];
    // echo '<br>';
    $students = $_POST['students']; // or $_GET['category'] if your form method was GET
    // foreach ($students as $student){
    //     echo "student ID: " . explode(",", $student)[0] . " corresponds to: " . explode(",", $student)[1];
    //     echo '<br>';
    // }
    if ($_POST['team_name'] != null) {
        $new_team = new \stdClass();
        $new_team->teamname = $_POST['team_name'];
        $new_team->courseid = $courseid;
        $teamID = $DB->insert_record('block_simple_rank_teams', $new_team);
        foreach ($students as $student) {
            $new_record = new \stdClass();
            $new_record->studentid = explode(",", $student)[0];
            $new_record->teamid = $teamID;
            $DB->insert_record('student_teams', $new_record);

        }
        echo '<meta http-equiv="refresh" content="0">';
    }
    else {
        echo '<h5 id="empty_team_error"> Please provide a name for the team. </h5>';
    }
}

echo $OUTPUT->footer();


// return $output;



