<?php

/*

[quiz_analytics]
Displays a list of the user’s completed quizzes and calculates the performance variation between the first and last attempts.

[user_quizzes]
Shows a simple list of all quizzes completed by the logged-in user, including title, date, percentage, and points.

[quiz_variation]
Displays the change in performance (percentage and points) between the first and last quizzes attempted by the user.

*/

// Define shortcodes
function shortcode_display_user_quizzes() {
    $user_id = get_current_user_id();
    $quizAnalytics = new QuizAnalytics($user_id);

    $quizzes = $quizAnalytics->get_user_quizzes();

    if (empty($quizzes)) {
        return "No quizzes found for this user.";
    }

    $output = '<ul>';
    foreach ($quizzes as $quiz) {
        $output .= '<li>';
        $output .= '<strong>Title:</strong> ' . esc_html($quiz['title']) . '<br>';
        $output .= '<strong>Date:</strong> ' . esc_html($quiz['date']) . '<br>';
        $output .= '<strong>Percentage:</strong> ' . esc_html($quiz['percentage']) . '%<br>';
        $output .= '<strong>Points:</strong> ' . esc_html($quiz['points']);
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('user_quizzes', 'shortcode_display_user_quizzes');

function shortcode_display_quiz_variation() {
    $user_id = get_current_user_id();
    $quizAnalytics = new QuizAnalytics($user_id);

    $variation = $quizAnalytics->calculate_variation();

    if (!$variation) {
        return "Not enough quizzes to calculate variation.";
    }

    $output = '<p>';
    $output .= '<strong>Percentage Variation:</strong> ' . esc_html($variation['percentage_variation']) . '%<br>';
    $output .= '<strong>Points Variation:</strong> ' . esc_html($variation['points_variation']);
    $output .= '</p>';

    return $output;
}
add_shortcode('quiz_variation', 'shortcode_display_quiz_variation');