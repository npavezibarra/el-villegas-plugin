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


add_filter('the_title', 'custom_learndash_lesson_title', 10, 2);
function custom_learndash_lesson_title($title, $post_id) {
    if (is_singular('sfwd-lessons') && in_the_loop()) {
        $title = '📘 ' . $title; // Example modification: adding an icon
    }
    return $title;
}

/* PASAR A CHECKOUT INMEDIATAMENTE (solo para cursos) */

add_action('woocommerce_add_to_cart', 'redirect_to_checkout_on_add_to_cart');

function redirect_to_checkout_on_add_to_cart() {
    if (!is_admin() && !wp_doing_ajax()) {
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}


add_action('wp_footer', 'redirect_to_checkout_js');

function redirect_to_checkout_js() {
    // Only add JavaScript on WooCommerce pages to avoid unnecessary loading
    if (is_shop() || is_product_category() || is_product()) {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                // Listen for WooCommerce's AJAX add to cart event
                $(document.body).on('added_to_cart', function() {
                    // Redirect to the checkout page
                    window.location.href = '<?php echo esc_url(wc_get_checkout_url()); ?>';
                });
            });
        </script>
        <?php
    }
}







