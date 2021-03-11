<?php
 
class block_simple_rank_edit_form extends block_edit_form {
    
    // This allows us to utilise the blocks configuration page.
    protected function specific_definition($mform) {
 
        // Title for the page.
        $mform->addElement('header', 'config_header', 'Edit ranking block');
 
        // Editing the title of the block.
        $mform->addElement('text', 'config_title', 'Edit block title');
        $mform->setDefault('config_title', 'Leaderboard');
        $mform->setType('config_title', PARAM_TEXT);

        // Editing the type of leaderboard to be displayed - i.e. individual leaderboard or team leaderboard.
        $mform->addElement('select', 'config_leaderboard_type', 'Set leaderboard type', array('Individual', 'Team'));
        $mform->setDefault('config_leaderboard_type', array('Individual'));

        // Editing the number of students/teams to display on the leaderboard.
        $mform->addElement('text', 'config_leaderboard_size', 'Number of students/teams to display');
        $mform->setDefault('config_leaderboard_size', 5);
        $mform->setType('config_leaderboard_size', PARAM_RAW);

        // Simple comment on the page to dictate how to display all students/teams.
        $mform->addElement('html', '<p id="leaderboard_size_description"> Leave blank to display all <br> students/teams </p>'); // might remove
    }

    
}