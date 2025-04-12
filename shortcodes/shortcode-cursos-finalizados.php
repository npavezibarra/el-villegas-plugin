<?php
/**
 * Shortcode: [cursos_finalizados]
 * Muestra los cursos en los que el usuario está inscrito, con estado de quizzes y estadísticas.
 */

 function villegas_shortcode_cursos_finalizados() {
    if (!is_user_logged_in()) {
        return '<h2>Debes iniciar sesión para ver tus cursos.</h2>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $courses = ld_get_mycourses($user_id);

    if (empty($courses)) {
        return '<div style="display:flex; align-items:center; justify-content:center; height:300px;"><h2>Aún no te has inscrito a ningún curso.</h2></div>';
    }

    ob_start();
    echo '<div class="cursos-finalizados-grid">';

    foreach ($courses as $course_id) {
        if (get_post_status($course_id) !== 'publish') continue;

        echo '<div class="curso-finalizado-box">';
        echo '<h2><a href="' . esc_url(get_permalink($course_id)) . '">' . esc_html(get_the_title($course_id)) . '</a></h2>';
        echo '<p id="progreso-porentaje">Completado: ' . intval($percentage) . '%</p>';

        if (has_post_thumbnail($course_id)) {
            $image_url = get_the_post_thumbnail_url($course_id, 'full');
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title($course_id)) . '" style="width: 100%; height: 180px; object-fit: cover; border-radius: 6px; margin-bottom: 10px;">';
        }

        // Progreso
        $progress = learndash_course_progress([
            'user_id'   => $user_id,
            'course_id' => $course_id,
            'array'     => true
        ]);
        $percentage = $progress['percentage'] ?? 0;

        // Obtener quizzes
        $first_quiz_id = get_post_meta($course_id, '_first_quiz_id', true);
        $quiz_steps = learndash_course_get_steps_by_type($course_id, 'sfwd-quiz');
        $final_quiz_id = !empty($quiz_steps) ? end($quiz_steps) : 0;

        // Fecha de rendición helper
        function villegas_get_quiz_date($quiz_id, $user_id) {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare(
                "SELECT FROM_UNIXTIME(activity_completed, '%%d %%M %%Y') FROM {$wpdb->prefix}learndash_user_activity
                WHERE user_id = %d AND post_id = %d AND activity_type = 'quiz' AND activity_completed IS NOT NULL
                ORDER BY activity_completed DESC LIMIT 1",
                $user_id, $quiz_id
            ));
        }
        

        // === PRUEBA INICIAL ===
        $first_completed = villegas_is_quiz_completed($first_quiz_id, $user_id);
        $first_date = $first_completed ? villegas_get_quiz_date($first_quiz_id, $user_id) : '-';

        echo '<div class="quiz-row-inicial">';
        echo '<div><div class="quiz-label">Prueba Inicial</div>';
        echo '<div class="quiz-date">' . esc_html($first_date) . '</div></div>';

        if ($first_completed) {
            echo villegas_render_quiz_result($first_quiz_id, $user_id);
        } else {
            echo '<a class="quiz-status-link" href="' . esc_url(get_permalink($first_quiz_id)) . '">NO RENDIDA</a>';
            echo '<span class="quiz-percentage">–</span>';
        }
        echo '</div>';
        echo '<hr>';

        // === PRUEBA FINAL ===
        if ($final_quiz_id) {
            $final_completed = villegas_is_quiz_completed($final_quiz_id, $user_id);
            $final_date = $final_completed ? villegas_get_quiz_date($final_quiz_id, $user_id) : '-';

            // Evaluar si todas las lecciones (sin quizzes) están completadas
            $steps = learndash_get_course_steps($course_id);
            $lesson_ids = array_filter($steps, function($step_id) {
                return get_post_type($step_id) === 'sfwd-lessons';
            });
            $completed_lessons = 0;
            foreach ($lesson_ids as $lesson_id) {
                if (learndash_is_item_complete($user_id, $lesson_id)) {
                    $completed_lessons++;
                }
            }
            $all_lessons_completed = (count($lesson_ids) > 0 && $completed_lessons === count($lesson_ids));

            echo '<div class="quiz-row-final">';
            echo '<div><div class="quiz-label">Prueba Final</div>';
            echo '<div class="quiz-date">' . esc_html($final_date) . '</div></div>';

            if ($final_completed) {
                echo villegas_render_quiz_result($final_quiz_id, $user_id);
            } elseif ($all_lessons_completed) {
                echo '<a class="quiz-status-link" href="' . esc_url(get_permalink($final_quiz_id)) . '">NO RENDIDA</a>';
                echo '<span class="quiz-percentage">–</span>';
            } else {
                echo '<div style="font-size:12px;">NO RENDIDA</div>';
                echo '<span class="quiz-percentage">–</span>';
            }
            echo '</div>';
        }
        echo '<hr>';

        // === ESTADÍSTICAS ADICIONALES ===
        if ($first_completed && $final_completed) {
            $first_data = villegas_get_quiz_data($first_quiz_id, $user_id);
            $final_data = villegas_get_quiz_data($final_quiz_id, $user_id);
            $diff = $final_data['score'] - $first_data['score'];
            $diff_sign = ($diff >= 0) ? '+' : '';
            $color = ($diff >= 0) ? 'green' : 'red';
            $days = max(1, floor(($final_data['timestamp'] - $first_data['timestamp']) / DAY_IN_SECONDS));

            echo '<hr>';
            echo '<div class="quiz-row-stats">';
            echo '<div class="extra-quiz-stats">';
            echo '<div class="quiz-variation" style="color:' . esc_attr($color) . ';">Variación: ' . $diff_sign . $diff . '%</div>';
            echo '<div class="quiz-days">Lo completaste en: ' . $days . ' ' . ($days === 1 ? 'día' : 'días') . '</div>';
            echo '</div></div>';
        }

        echo '</div>'; // .curso-finalizado-box
    }

    echo '</div>'; // .cursos-finalizados-grid
    return ob_get_clean();
}
add_shortcode('cursos_finalizados', 'villegas_shortcode_cursos_finalizados');

function villegas_is_quiz_completed($quiz_id, $user_id) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare(
        "SELECT activity_id FROM {$wpdb->prefix}learndash_user_activity
         WHERE user_id = %d AND post_id = %d AND activity_type = 'quiz'
         AND activity_completed IS NOT NULL LIMIT 1",
        $user_id, $quiz_id
    ));
}

function villegas_get_quiz_data($quiz_id, $user_id) {
    global $wpdb;

    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT activity_id, activity_completed FROM {$wpdb->prefix}learndash_user_activity 
         WHERE user_id = %d AND post_id = %d AND activity_type = 'quiz' 
         ORDER BY activity_completed DESC LIMIT 1",
        $user_id, $quiz_id
    ));

    if (!$attempt) return null;

    $pct = $wpdb->get_var($wpdb->prepare(
        "SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity_meta
         WHERE activity_id = %d AND activity_meta_key = 'percentage'",
        $attempt->activity_id
    ));

    return [
        'score'     => round(floatval($pct)),
        'timestamp' => (int) $attempt->activity_completed,
    ];
}

function villegas_render_quiz_result($quiz_id, $user_id) {
    $data = villegas_get_quiz_data($quiz_id, $user_id);
    if (!$data) return '<span class="quiz-percentage">-</span>';

    ob_start(); ?>
    <div class="progress-bar-container">
        <div class="progress-bar" style="width: <?php echo esc_attr($data['score']); ?>%;"></div>
    </div>
    <div class="quiz-percentage"><?php echo esc_html($data['score']); ?>%</div>
    <?php return ob_get_clean();
}