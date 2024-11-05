<?php
// email-customization.php

// Include helper functions
require_once plugin_dir_path(__FILE__) . 'helpers.php';

// Add "Go to Course" button in the WooCommerce order email
add_action('woocommerce_email_order_details', 'add_course_button_to_email', 20, 4);

function add_course_button_to_email($order, $sent_to_admin, $plain_text, $email) {
    // Loop through the items in the order
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $course_url = get_course_url_from_product($product_id);

        echo '<p style="text-align: center; margin-top: 20px;">';
        echo '<a href="' . esc_url($course_url) . '" style="background-color: #4c8bf5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ir al Curso</a>';
        echo '</p>';
    }
}
