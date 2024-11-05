<?php
// order-confirmation-page.php

// Include helper functions
require_once plugin_dir_path(__FILE__) . 'helpers.php';

// Add "Go to Course" button on the WooCommerce order confirmation (thank you) page
add_action('woocommerce_thankyou', 'add_course_button_to_thankyou_page');

function add_course_button_to_thankyou_page($order_id) {
    $order = wc_get_order($order_id);

    if ($order) {
        // Retrieve the first product in the order and get its related course URL
        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $course_url = get_course_url_from_product($product_id);
            break; // Only need the first product to get the course URL
        }

        // Display the button before the order details table
        if ($course_url) {
            echo '<p style="text-align: center; margin-top: 20px;">';
            echo '<a href="' . esc_url($course_url) . '" style="background-color: #4c8bf5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Ir al Curso</a>';
            echo '</p>';
        }
    }
}
