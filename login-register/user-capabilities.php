<?php

function allow_no_role_users_access_quiz( $has_access, $post_id, $user_id ) {
    // Get the user's role(s)
    $user = get_userdata( $user_id );
    $user_roles = (array) $user->roles;

    // Check if the user has no roles (empty roles array) and is logged in
    if ( empty( $user_roles ) && is_user_logged_in() ) {
        // Allow access to the quiz for users with "None" role
        $has_access = true;
    }
    
    return $has_access;
}
add_filter( 'learndash_is_course_accessable', 'allow_no_role_users_access_quiz', 10, 3 );
