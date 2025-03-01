<?php
ob_start();

// (1) Dejamos la función get_latest_quiz_percentage SÓLO si aún la usas para el "Primer Test". 
function get_latest_quiz_percentage($user_id, $quiz_id) {
    $quiz_attempts = get_user_meta($user_id, '_sfwd-quizzes', true);
    $latest_attempt = null;

    if (!empty($quiz_attempts) && is_array($quiz_attempts)) {
        foreach ($quiz_attempts as $attempt) {
            if (isset($attempt['quiz']) && (int)$attempt['quiz'] === (int)$quiz_id) {
                if (is_null($latest_attempt) || $attempt['time'] > $latest_attempt['time']) {
                    $latest_attempt = $attempt;
                }
            }
        }
    }

    if ($latest_attempt) {
        // score / count * 100
        $percentage_correct = round(($latest_attempt['score'] / $latest_attempt['count']) * 100, 2);
        return [true, $percentage_correct];
    }

    return [false, 0];
}

// (2) Tu función principal "mostrar_comprar_stats" donde cambias SOLO la parte final
function mostrar_comprar_stats() {
    // Mantén todo tal cual...
    $user_id = get_current_user_id();
    $course_id = get_the_ID();
    $is_enrolled = sfwd_lms_has_access($course_id, $user_id);

    // Buscar product_id...
    $product_id = get_post_meta($course_id, '_linked_woocommerce_product', true);
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

    // Primer Quiz ID y URL...
    $first_quiz_id = get_post_meta($course_id, '_first_quiz_id', true);
    if (!empty($first_quiz_id)) {
        $quiz_post = get_post($first_quiz_id);
        if ($quiz_post) {
            $first_quiz_url = home_url('/evaluaciones/' . $quiz_post->post_name . '/');
        }
    } else {
        $first_quiz_url = '#';
    }

    // Progreso del curso...
    $total_lessons = count(learndash_get_course_steps($course_id));
    $completed_lessons = learndash_course_get_completed_steps_legacy($user_id, $course_id);
    $percentage_complete = ($total_lessons > 0) ? min(100, ($completed_lessons / $total_lessons) * 100) : 0;

    // =============== INTERFAZ para NO logueados =================
    if (!is_user_logged_in()) {
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
            <?php
            // Botón de iniciar sesión
            function get_login_redirect_url_for_course() {
                $args = array(
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                );
                $pages = get_posts($args);
                $login_page_id = null;
                $course_id_found = null;

                foreach ($pages as $page_id) {
                    $content = get_post_field('post_content', $page_id);
                    if (has_shortcode($content, 'villegas_login_register')) {
                        // Buscar el shortcode y sus atts
                        preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
                        foreach ($matches as $shortcode_match) {
                            if ($shortcode_match[2] === 'villegas_login_register') {
                                $atts = shortcode_parse_atts($shortcode_match[3]);
                                if (isset($atts['course_id'])) {
                                    $login_page_id = $page_id;
                                    $course_id_found = $atts['course_id'];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if (!$login_page_id || !$course_id_found) {
                    return wp_login_url();
                }

                global $post;
                $current_course_id = $post->ID;

                $login_url = get_permalink($login_page_id);
                $redirect_url = add_query_arg('fref', $current_course_id, $login_url);

                return $redirect_url;
            }
            $login_redirect_url = get_login_redirect_url_for_course();
            ?>
            <button style="width: 100%; background-color: #4c8bf5; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer;"
                    onclick="window.location.href='<?php echo esc_url($login_redirect_url); ?>'">
                Iniciar Sesión
            </button>
            </div>
        </div>
        <?php
    }
    // =============== INTERFAZ para Logueados pero NO inscritos =================
    elseif (!$is_enrolled) {
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
                // Primer Test
                list($has_completed_quiz, $percentage_correct) = get_latest_quiz_percentage($user_id, $first_quiz_id);
                if ($has_completed_quiz) {
                    echo '<div class="examen-inicial">';
                    echo "<strong>$percentage_correct%</strong>"; // Show percentage
                    echo '<p id="primer-test-legend">Primer Test</p>';
                    echo '</div>';
                } else {
                    ?>
                    <button onclick="window.location.href='<?php echo esc_url($first_quiz_url); ?>'" 
                            class="button exam-inicial-btn" 
                            style="flex: 1; background-color: #2196f3; color: white; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer; text-align: center;">
                        Examen Inicial
                    </button> 
                    <?php
                }
                ?>

                <button onclick="window.location.href='<?php echo esc_url(get_permalink($product_id)); ?>'"
                        class="button buy-button" 
                        style="flex: 1; background-color: #4c8bf5; color: white; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer; text-align: center;">
                    Comprar Curso
                </button>
            </div>
        </div>
        <?php
    }
    // =============== INTERFAZ para Logueados e inscritos en el curso =================
    else {
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
                    // Conservamos la lógica del primer test tal cual
                    list($has_completed_quiz, $percentage_correct) = get_latest_quiz_percentage($user_id, $first_quiz_id);
                    if ($has_completed_quiz) {
                        echo '<div class="quiz-result" style="background-color: #e0e0e0; border-radius: 5px; text-align: center;">';
                        echo "<strong>$percentage_correct%</strong>";
                        echo '<p id="primer-test-legend">Primer Test</p>';
                        echo '</div>';
                    } else {
                        echo '<button onclick="window.location.href=\'' . esc_url($first_quiz_url) . '\'" style="width: auto; color: white; border: none; width: 100%; height: auto; background: #2196f3; padding: 10px 0px; border-radius: 5px; font-size: 14px; text-align: center;">Examen Inicial</button>';
                    }
                    ?>
                </div>

                <div id="final-test-button" class="tooltip" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
    <?php
    global $wpdb;

    // Fetch Final Quiz ID: Get the last quiz attached to the course
    $final_quiz_id = 0;
    $course_steps = learndash_course_get_steps_by_type($course_id, 'sfwd-quiz');
    if (!empty($course_steps)) {
        $final_quiz_id = end($course_steps);
    }

    // Get the URL of the Final Quiz
    $final_quiz_url = ($final_quiz_id) ? get_permalink($final_quiz_id) : '';

    // Get the latest attempt for Final Quiz
    $latest_attempt_final = $wpdb->get_row($wpdb->prepare(
        "SELECT activity_id FROM {$wpdb->prefix}learndash_user_activity 
        WHERE user_id = %d 
        AND post_id = %d 
        AND activity_type = 'quiz' 
        ORDER BY activity_completed DESC 
        LIMIT 1",
        $user_id,
        $final_quiz_id
    ));

    $final_quiz_score = 0;
    $has_completed_final_quiz = false;

    if ($latest_attempt_final) {
        $final_quiz_score = $wpdb->get_var($wpdb->prepare(
            "SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity_meta 
            WHERE activity_id = %d 
            AND activity_meta_key = 'percentage'",
            $latest_attempt_final->activity_id
        ));
        $has_completed_final_quiz = ($final_quiz_score !== null && $final_quiz_score > 0);
    }

    // If user has completed all lessons and not taken the final quiz yet, show the button
    if ((int)$percentage_complete >= 100 && !empty($final_quiz_url) && !$has_completed_final_quiz) {
        echo '<button onclick="window.location.href=\'' . esc_url($final_quiz_url) . '\'" style="width: 100%; background-color: #4c8bf5; color: white; border: none; padding: 10px 0px; border-radius: 5px; font-size: 12px;">
                Examen Final
              </button>';
    } elseif ($has_completed_final_quiz) {
        // Display Final Quiz score instead of the button
        echo '<div class="quiz-result" style="background-color: #e0e0e0; border-radius: 5px; text-align: center; padding: 10px;">
                <strong>' . esc_html($final_quiz_score) . '%</strong>
                <p style="font-size: 9px;">Examen Final</p>
              </div>';
    } else {
        // If progress is not 100% and user hasn't attempted the quiz, disable the button
        echo '<button id="final-evaluation-button" style="width: 100%; background-color: #ccc; color: #333; border: none; padding: 10px 0px; border-radius: 5px; font-size: 14px; cursor: not-allowed;">
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

    // Opcional: si necesitas devolver algo en vez de imprimir directamente, usa ob_get_clean().
}

if (headers_sent($file, $line)) {
    echo "Headers already sent in $file on line $line";
}
?>
