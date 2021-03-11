<?php
// require_once('test_form.php');
// require_once("$CFG->libdir/formslib.php");
// require_once($CFG->dirroot . '/blocks/simple_rank/test_form.php');
require_once('simple_rank_form.php');

class block_simple_rank extends block_base {

    // initialise the title of the block.
    public function init() {
        $this->title = 'Leaderboard';
    }

    // This function tells moodle what to display when the block is loaded.
    // Here we call our renderer.
    public function get_content() {
        if ($this->content !== null) {
          return $this->content;
        }
   
        $this->content = new stdClass;
        $this->content->text   = '';
        $this->content->footer = '';
        
        // $mform = new simple_rank_form();
        // $this->content->text = $mform->render();]

        // global $DB;
        // $DB->delete_records('block_simple_rank_points');
        // $DB->delete_records('block_simple_rank_teams');
        // $DB->delete_records('student_teams');
        // $DB->delete_records('student_levels');

        // Check if the leaderboard size has been set, if not then use a default value of 5.
        $to_display = 5;
        if (isset($this->config->leaderboard_size)) {
            $to_display = $this->config->leaderboard_size;
        }

        

        // If the leaderboard type is set to individual (0) in the block edit page, display the individual leaderboard.
        // else, display the team leaderboard.
        
        if ($this->config->leaderboard_type == 0) {
            echo '<br>';
            echo '<br>';
            echo 'Individual leaderboard';
            $renderer = $this->page->get_renderer('block_simple_rank');
            $this->content->text = $renderer->simple_rank_block_individual($to_display, $this->page->course);
        }
        else {
            $renderer = $this->page->get_renderer('block_simple_rank');
            $this->content->text = $renderer->simple_rank_block_team($to_display, $this->page->course);
        }

        
        return $this->content;
    }

    // called immediately after init() and before get_content()
    // basically only used to check whether the educator has edited the title of the block.
    public function specialization() {
        
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = 'Leaderboard';            
            } else {
                $this->title = $this->config->title;
            }
        }
    }

    // does what it says on the tin
    // dictates whether multiple blocks can be instantiated simultaneously.
    public function instance_allow_multiple() {
        return true;
      }

    // enables global configuration
    function has_config() {
        return true;
    }
}