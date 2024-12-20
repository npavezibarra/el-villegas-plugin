<?php
ob_start();

// Function to get the percentage of correct answers from the latest quiz attempt
function get_latest_quiz_percentage($user_id, $quiz_id) {
    $quiz_attempts = get_user_meta($user_id, '_sfwd-quizzes', true);
    $latest_attempt = null;

    // Check if there are any attempts recorded
    if (!empty($quiz_attempts)) {
        foreach ($quiz_attempts as $attempt) {
            if ($attempt['quiz'] == $quiz_id) {
                // Update latest attempt
                if (is_null($latest_attempt) || $attempt['time'] > $latest_attempt['time']) {
                    $latest_attempt = $attempt;
                }
            }
        }
    }

    // Determine if a quiz attempt was found
    if ($latest_attempt) {
        $percentage_correct = round(($latest_attempt['score'] / $latest_attempt['count']) * 100, 2);
        return [true, $percentage_correct];
    }

    return [false, 0];
}

// Function to display the progress bar and buttons
function mostrar_comprar_stats() {
    // Get the current user and course IDs
    $user_id = get_current_user_id();
    $course_id = get_the_ID();
    
    // Check if the user is enrolled in the course
    $is_enrolled = sfwd_lms_has_access($course_id, $user_id);

    // Retrieve the WooCommerce product ID related to the course
    // First, check if it's linked via the course meta key '_linked_woocommerce_product'
    $product_id = get_post_meta($course_id, '_linked_woocommerce_product', true);
    
    // If no product ID is found, search the product via WooCommerce meta (using '_related_course')
    if (empty($product_id)) {
        $args = array(
            'post_type' => 'product',
            'meta_query' => array(
                array(
                    'key' => '_related_course',
                    'value' => $course_id,
                    'compare' => 'LIKE',
                ),
            ),
            'posts_per_page' => 1,
        );
        $products = get_posts($args);
        if (!empty($products)) {
            $product_id = $products[0]->ID;
        }
    }

    // Retrieve the initial quiz ID and its URL
    $first_quiz_id = get_post_meta($course_id, '_first_quiz_id', true);
    if (!empty($first_quiz_id)) {
        $quiz_post = get_post($first_quiz_id);
        if ($quiz_post) {
            $first_quiz_url = home_url('/quizzes/' . $quiz_post->post_name . '/');
        }
    } else {
        $first_quiz_url = '#';
    }

    // Calculate course progress based on completed lessons
    $total_lessons = count(learndash_get_course_steps($course_id));
    $completed_lessons = learndash_course_get_completed_steps_legacy($user_id, $course_id);

    // Calculate percentage completion
    $percentage_complete = ($total_lessons > 0) ? min(100, ($completed_lessons / $total_lessons) * 100) : 0;

    // Display the appropriate buttons based on login and enrollment status
if (!is_user_logged_in()) {
    // User not logged in
    ?>
    <div class="progress-widget" style="display: flex; align-items: center; background-color: #eeeeee; padding: 20px 20px; border-radius: 10px; width: 100%;">
        <div class="progress-bar" style="flex: 1; width: 50%; margin-right: 20px;">
            <div style="background-color: #e0e0e0; height: 10px; border-radius: 5px; position: relative;">
                <div style="width: <?php echo esc_attr($percentage_complete); ?>%; background-color: #4c8bf5; height: 100%; border-radius: 5px;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #333;">
                <span>0%</span>
                <span>100%</span>
            </div>
        </div>
        <div class="buy-button" style="flex: 1; width: 50%; text-align: right;">

    <!-- LOGICA DEL BOTON QUE REDIRIGE A PAGINA ESPECIAL LOGIN -->
    <?php
            function get_login_redirect_url_for_course() {
                // Step 1: Search all pages for the `villegas_login_register` shortcode and retrieve `course_id`
                $args = array(
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'fields' => 'ids', // Only get post IDs for efficiency
                );
            
                $pages = get_posts($args);
                $login_page_id = null;
                $course_id = null;
            
                foreach ($pages as $page_id) {
                    $content = get_post_field('post_content', $page_id);
            
                    // Check if the page content has the `villegas_login_register` shortcode
                    if (has_shortcode($content, 'villegas_login_register')) {
                        preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
                        
                        foreach ($matches as $shortcode_match) {
                            if ($shortcode_match[2] === 'villegas_login_register') {
                                $atts = shortcode_parse_atts($shortcode_match[3]);
                                if (isset($atts['course_id'])) {
                                    $login_page_id = $page_id; // Get the page ID where the shortcode is found
                                    $course_id = $atts['course_id']; // Get the course_id parameter
                                    break 2; // Exit both loops if found
                                }
                            }
                        }
                    }
                }
            
                // Step 3: If the shortcode wasn't found, redirect to the default WordPress login page
                if (!$login_page_id || !$course_id) {
                    return wp_login_url(); // Default WordPress login page
                }
            
                // Step 4: Get the current course page ID (assuming this is called from a course page)
                global $post;
                $current_course_id = $post->ID;
            
                // Step 5: Generate the URL to redirect with `fref` parameter
                $login_url = get_permalink($login_page_id);
                $redirect_url = add_query_arg('fref', $current_course_id, $login_url);
            
                return $redirect_url;
            }        
        // Use the function to get the redirect URL
        $login_redirect_url = get_login_redirect_url_for_course();
    ?>

<button style="width: 100%; background-color: #4c8bf5; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer;"
        onclick="window.location.href='<?php echo esc_url($login_redirect_url); ?>'">
    Iniciar Sesión
</button>
        </div>
    </div>
    <?php
} elseif (!$is_enrolled) {
    // User logged in but not enrolled (must purchase the course)
    ?>
    <div class="progress-widget" style="display: flex; align-items: center; background-color: #eeeeee; padding: 20px 20px; border-radius: 10px; width: 100%;">
        <div class="progress-bar" style="flex: 1; width: 50%; margin-right: 20px;">
            <div style="background-color: #e0e0e0; height: 10px; border-radius: 5px; position: relative;">
                <div style="width: 0%; background-color: #ccc; height: 100%; border-radius: 5px;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #333;">
                <span>0%</span>
                <span>100%</span>
            </div>
        </div>
        <div class="action-buttons" style="flex: 1; display: flex; justify-content: space-between; align-items: center; gap: 20px;">
            <?php
                // Check quiz attempts and display result
                list($has_completed_quiz, $percentage_correct) = get_latest_quiz_percentage($user_id, $first_quiz_id);
                if ($has_completed_quiz) {
                    echo '<div class="examen-inicial">';
                    echo "<strong>$percentage_correct%</strong>"; // Show percentage
                    echo '<p id="primer-test-legend">Primer Test</p>';
                    echo '</div>';
                } else { ?>
            <button onclick="window.location.href='<?php echo esc_url($first_quiz_url); ?>'" 
                    class="button exam-inicial-btn" 
                    style="flex: 1; background-color: #2196f3; color: white; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer; text-align: center;">
                Examen Inicial
            </button> 
            <?php } ?>

            <button onclick="window.location.href='<?php echo esc_url(get_permalink($product_id)); ?>'"
            class="button buy-button" 
                    style="flex: 1; background-color: #4c8bf5; color: white; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer; text-align: center;">
                Comprar Curso
            </button>
        </div>
    </div>
    <?php
} else {
    // User is logged in and enrolled in the course
    ?>
    <div class="progress-widget" style="display: flex; align-items: center; background-color: #eeeeee; padding: 20px 20px; border-radius: 10px; width: 100%;">
        <div class="progress-bar" style="flex: 1; width: 50%; margin-right: 20px;">
            <div style="background-color: #e0e0e0; height: 10px; border-radius: 5px; position: relative;">
                <div style="width: <?php echo esc_attr($percentage_complete); ?>%; background-color: #4c8bf5; height: 100%; border-radius: 5px;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #333;">
                <span><?php echo esc_html(round($percentage_complete)); ?>%</span>
                <span>100%</span>
            </div>
        </div>
        <div class="test-buttons" style="flex: 1; text-align: right; display: flex; gap: 20px;">
            <div id="primer-test-score" class="quiz-score-display" style="width: 50%;">
                <?php
                // Check quiz attempts and display result
                list($has_completed_quiz, $percentage_correct) = get_latest_quiz_percentage($user_id, $first_quiz_id);
                if ($has_completed_quiz) {
                    // Instead of just showing the percentage, now we show a styled result
                    echo '<div class="quiz-result" style="background-color: #e0e0e0; border-radius: 5px; text-align: center;">';
                    echo "<strong>$percentage_correct%</strong>"; // Show percentage
                    echo '<p id="primer-test-legend">Primer Test</p>';
                    echo '</div>'; // Closing the quiz-result div
                } else {
                    echo '<button onclick="window.location.href=\'' . esc_url($first_quiz_url) . '\'" style="width: auto; color: white; border: none; width: 100%; height: auto; background: #2196f3; padding: 10px 0px; border-radius: 5px; font-size: 14px; text-align: center;">Examen Inicial</button>';
                }
                ?>
            </div>
            <div id="final-test-button" class="tooltip" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <?php

                // Fetch all quizzes linked to the course dynamically
                $quiz_query = new WP_Query(array(
                    'post_type' => 'sfwd-quiz',
                    'meta_key' => 'course_id',
                    'meta_value' => $course_id,
                    'posts_per_page' => -1,
                ));

                // Initialize final quiz URL and ID
                $final_quiz_url = '';
                $final_quiz_id = null; // Initialize the final quiz ID

                if ($quiz_query->have_posts()) {
                    while ($quiz_query->have_posts()) {
                        $quiz_query->the_post();
                        // Search for the quiz named 'Examen Final' (adjust if necessary)
                        if (stripos(get_the_title(), 'Examen Final') !== false) {
                            $final_quiz_url = get_permalink(); // Get the permalink of the final quiz
                            $final_quiz_id = get_the_ID(); // Get the ID of the final quiz
                            break; // Break once we find the desired quiz
                        }
                    }
                }

                wp_reset_postdata(); // Always reset the post data after querying

                // Check quiz attempts for the final exam (now using $final_quiz_id)
                list($has_completed_final_quiz, $final_percentage_correct) = get_latest_quiz_percentage($user_id, $final_quiz_id);

                // Get the total number of lessons and completed lessons
                $total_lessons = count(learndash_get_course_steps($course_id));
                $completed_lessons = learndash_course_get_completed_steps_legacy($user_id, $course_id);

                // Check if all lessons are completed
                if ($completed_lessons === $total_lessons && !empty($final_quiz_url)) {
                    // All lessons completed, show clickable button linking to the final quiz
                    echo '<button onclick="window.location.href=\'' . esc_url($final_quiz_url) . '\'" style="width: 100%; background-color: #4c8bf5; color: white; border: none; padding: 10px 0px; border-radius: 5px; font-size: 12px;">Examen Final</button>';
                } elseif ($has_completed_final_quiz) {
                    // Show the percentage in a styled container if the final quiz has been completed
                    echo '<div class="quiz-result" style="background-color: #e0e0e0; border-radius: 5px; text-align: center;">';
                    echo "<strong>$final_percentage_correct%</strong><p style='font-size: 9px;'>Examen Final</p>";
                    echo '</div>'; // Closing the quiz-result div
                } else {
                    // Not completed, show disabled button
                    echo '<button id="final-evaluation-button" style="width: 100%; background-color: #ccc; color: #333; border: none; padding: 10px 0px; border-radius: 5px; font-size: 14px; cursor: not-allowed; display: flex; align-items: center; justify-content: center;">
                            Examen Final
                        </button>';
                }
                ?>
                <span class="tooltiptext">Completa todas las lecciones de este curso para tomar el Examen Final</span>
            </div>
        </div>
    </div>
    <?php
}

}

if (headers_sent($file, $line)) {
    echo "Headers already sent in $file on line $line";
}
?>