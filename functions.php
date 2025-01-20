<?php

function allow_pending_role_users_access_quiz( $has_access, $post_id, $user_id ) {
    // Get the user's role(s)
    $user = get_userdata( $user_id );
    $user_roles = (array) $user->roles;

    // Check if the user has the 'pending' role and is logged in
    if ( in_array( 'pending', $user_roles ) && is_user_logged_in() ) {
        // Allow access to the quiz for users with 'pending' role
        $has_access = true;
    }
    
    return $has_access;
}
add_filter( 'learndash_is_course_accessable', 'allow_pending_role_users_access_quiz', 10, 3 );

// Add a button "Ir al Curso" after the product name on the order-received page
add_action('woocommerce_order_item_meta_end', 'add_course_button_after_product_name', 10, 3);

function add_course_button_after_product_name($item_id, $item, $order) {
    // Get the product ID from the order item
    $product_id = $item->get_product_id();

    // Retrieve the course ID associated with this product
    $course_meta = get_post_meta($product_id, '_related_course', true);

    // Check if course_meta is serialized
    if (is_serialized($course_meta)) {
        $course_meta = unserialize($course_meta);
    }

    // If course_meta is an array, get the first item (assuming one course)
    if (is_array($course_meta) && isset($course_meta[0])) {
        $course_id = $course_meta[0];
    } else {
        $course_id = $course_meta;
    }

    // Check if a valid course ID was retrieved and generate the button if it exists
    if (!empty($course_id) && is_numeric($course_id)) {
        // Generate the course URL
        $course_url = get_permalink($course_id);

        // Display the button with the course URL
        echo '<br><a href="' . esc_url($course_url) . '" class="button" style="display: inline-block; margin-top: 10px; padding: 5px 10px; background-color: black; color: #fff; text-decoration: none; border-radius: 3px; font-size: 12px;">Ir al Curso</a>';
    }
}

/* PASAR A CHECKOUT INMEDIATAMENTE (solo para cursos) */

function custom_redirect_to_checkout() {
    if (is_product() || is_shop()) {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                // Listen for the WooCommerce AJAX add to cart event
                $(document.body).on('added_to_cart', function() {
                    // Redirect to checkout page
                    window.location.href = '<?php echo esc_url(wc_get_checkout_url()); ?>';
                });
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'custom_redirect_to_checkout');

// Redirect to checkout for non-AJAX add to cart (for single product pages)
function non_ajax_redirect_to_checkout($url) {
    // Ensure we are on the front end and not in the admin area
    if (!is_admin()) {
        $url = wc_get_checkout_url();
    }
    return $url;
}
add_filter('woocommerce_add_to_cart_redirect', 'non_ajax_redirect_to_checkout');

/* LEARNDAH LEADERBOARD FILTER */

// Hook into the leaderboard query to modify it
add_filter('learndash_quiz_results', 'filter_latest_quiz_results', 10, 2);

function filter_latest_quiz_results($quiz_results, $quiz_id) {
    global $wpdb;

    // Get all users who have taken the quiz
    $user_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->prefix}learndash_user_activity 
        WHERE course_id = %d AND post_id = %d",
        $quiz_id, $quiz_id
    ));

    $latest_results = array();

    foreach ($user_ids as $user_id) {
        // Get the latest quiz attempt for each user
        $latest_result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}learndash_user_activity 
            WHERE course_id = %d AND post_id = %d AND user_id = %d
            ORDER BY activity_completed DESC LIMIT 1",
            $quiz_id, $quiz_id, $user_id
        ));

        if ($latest_result) {
            // Store the result in the array
            $latest_results[] = $latest_result;
        }
    }

    return $latest_results;
}


/* TEST SHORTCODE */

// Shortcode minimalista para probar con ID de curso = 7359
add_shortcode('prueba_final_quiz_7359', function() {
    $user_id   = get_current_user_id();
    $course_id = 7359;

    $analytics = new LearnDashCourseAnalytics($user_id, $course_id);
    $final_quiz_data = $analytics->get_final_quiz();

    $output  = '<p>Intentos: ' . $final_quiz_data['attempts'] . '</p>';
    $output .= '<p>Último Score: ' . $final_quiz_data['score'] . '</p>';
    $output .= '<p>Último Porcentaje: ' . $final_quiz_data['percentage'] . '</p>';

    return $output;
});
