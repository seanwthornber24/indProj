<?php

defined('MOODLE_INTERNAL') || die();

// This array tells us what observer to use in order to handle a specific event.
$observers = array(
    array(
        // Event for a forum discussion being created.
        'eventname' => '\mod_forum\event\discussion_created',
        'callback'  => 'block_simple_rank_observer::discussion_created_handler'
    ),

    array(
        'eventname' => '\mod_forum\event\post_created',
        'callback'  => 'block_simple_rank_observer::reply_created_handler'
    ),

    array(
        'eventname' => '\mod_feedback\event\response_submitted',
        'callback'  => 'block_simple_rank_observer::feedback_submitted_handler'
    )
);