<?php

// Add course slug to body class on lesson pages
function add_course_slug_to_body_class($classes) {
    if (is_singular('sfwd-lessons')) { // Check if we're on a lesson page
        global $post;

        // Get the course ID associated with the lesson
        $course_id = learndash_get_course_id($post->ID);

        // Retrieve the course slug
        if ($course_id) {
            $course = get_post($course_id);
            if ($course) {
                $course_slug = $course->post_name;
                $classes[] = $course_slug; // Add course slug to the body classes
            }
        }
    }
    return $classes;
}
add_filter('body_class', 'add_course_slug_to_body_class');

