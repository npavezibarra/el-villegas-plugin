<?php
// helpers.php

// Helper function to get the course URL from the WooCommerce product
function get_course_url_from_product($product_id) {
    // Find the course ID linked to this product
    $course_id = get_post_meta($product_id, '_related_course', true);

    // If a course ID is found, get the URL
    if (!empty($course_id)) {
        return get_permalink($course_id);
    }

    return '#'; // Return a default link if no course is found
}
