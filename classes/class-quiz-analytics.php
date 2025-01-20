<?php

/**
 * Clase para obtener datos de quiz (primer quiz y quiz final) de un curso LearnDash.
 */
class LearnDashCourseAnalytics {
    protected $user_id;
    protected $course_id;

    public function __construct($user_id, $course_id) {
        $this->user_id   = (int) $user_id;
        $this->course_id = (int) $course_id;
    }

    /**
     * Devuelve el título del curso.
     */
    public function get_course_title() {
        return get_the_title($this->course_id);
    }

    /**
     * Obtiene información del "primer quiz" definido en la meta _first_quiz_id.
     * Retorna un array con 'score' y 'percentage' del último intento.
     */
    public function get_first_quiz() {
        // 1) Verificar si el curso existe
        $course_title = $this->get_course_title();
        if (!$course_title) {
            // Si no existe el curso, devolvemos algo por defecto
            return array(
                'score'      => 0,
                'percentage' => 'N/A',
                'attempts'   => 0,
            );
        }

        // 2) Obtener el ID del primer quiz
        $first_quiz_id = get_post_meta($this->course_id, '_first_quiz_id', true);
        if (empty($first_quiz_id)) {
            // Si no hay primer quiz definido
            return array(
                'score'      => 0,
                'percentage' => 'N/A',
                'attempts'   => 0,
            );
        }

        // 3) Mirar en el user meta '_sfwd-quizzes' todos los intentos
        $attempts_data = get_user_meta($this->user_id, '_sfwd-quizzes', true);
        if (empty($attempts_data) || !is_array($attempts_data)) {
            // Si el usuario no ha intentado nada
            return array(
                'score'      => 0,
                'percentage' => 'N/A',
                'attempts'   => 0,
            );
        }

        $last_attempt_percentage = null;
        $last_attempt_score      = null;
        $attempt_count           = 0;

        foreach ($attempts_data as $attempt) {
            if (!is_array($attempt)) {
                continue;
            }
            // ¿Este intento corresponde al primer quiz?
            if (isset($attempt['quiz']) && (int) $attempt['quiz'] === (int) $first_quiz_id) {
                $attempt_count++;

                // Intentamos obtener el activity_id o statistic_ref_id
                $activity_id = 0;
                if (!empty($attempt['activity_id'])) {
                    $activity_id = (int) $attempt['activity_id'];
                } elseif (!empty($attempt['statistic_ref_id'])) {
                    $activity_id = (int) $attempt['statistic_ref_id'];
                }

                // Buscamos el 'percentage' desde la tabla user_activity_meta
                $percentage = null;
                $score      = null;

                // Si tenemos activity_id, consultamos
                if ($activity_id) {
                    global $wpdb;
                    // Sacamos el percentage
                    $percentage = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT activity_meta_value
                               FROM {$wpdb->prefix}learndash_user_activity_meta
                              WHERE activity_id = %d
                                AND activity_meta_key = 'percentage'",
                            $activity_id
                        )
                    );
                    // También podríamos sacar score si está guardado:
                    $score = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT activity_meta_value
                               FROM {$wpdb->prefix}learndash_user_activity_meta
                              WHERE activity_id = %d
                                AND activity_meta_key = 'score'",
                            $activity_id
                        )
                    );
                }

                // Si no se encuentra nada en DB, probamos fallback del array
                if (($percentage === null || $percentage === '') && isset($attempt['percentage'])) {
                    $percentage = $attempt['percentage'];
                }
                if (($score === null || $score === '') && isset($attempt['count'])) {
                    // Ojo: a veces 'count' es el número de preguntas, no la puntuación.
                    // Ajusta según necesites.
                    $score = $attempt['count'];
                }

                // Guardamos este intento como "último" (si quieres el primero, no sobrescribas)
                $last_attempt_percentage = ($percentage !== null && $percentage !== '') 
                    ? $percentage 
                    : $last_attempt_percentage;
                $last_attempt_score      = ($score !== null && $score !== '') 
                    ? $score 
                    : $last_attempt_score;
            }
        }

        // Si no hubo intentos, retornamos algo por defecto
        if ($attempt_count === 0) {
            return array(
                'score'      => 0,
                'percentage' => 'N/A',
                'attempts'   => 0,
            );
        }

        // Retornamos la info del último intento encontrado
        return array(
            'score'      => $last_attempt_score  ?: 0,
            'percentage' => ($last_attempt_percentage !== null) ? $last_attempt_percentage : 'N/A',
            'attempts'   => $attempt_count,
        );
    }

    /**
     * Obtiene información del "quiz final" (primer quiz encontrado en los pasos del curso).
     * Retorna un array con 'score' y 'percentage' del último intento.
     */
    public function get_final_quiz() {
        // 1) Verificar si el curso existe
        $course_title = $this->get_course_title();
        if (!$course_title) {
            return array(
                'score'      => 0,
                'percentage' => 'N/A',
                'attempts'   => 0,
            );
        }

        // 2) Obtener steps del curso
        $course_steps = get_post_meta($this->course_id, 'ld_course_steps', true);
        if (!empty($course_steps) && !is_array($course_steps)) {
            $course_steps = @unserialize($course_steps);
        }

        $final_quiz_id = null;
        if (!empty($course_steps['steps']) && is_array($course_steps['steps'])) {
            foreach ($course_steps['steps'] as $step) {
                if (!empty($step['sfwd-quiz']) && is_array($step['sfwd-quiz'])) {
                    foreach ($step['sfwd-quiz'] as $quiz_id => $quiz_data) {
                        $final_quiz_id = $quiz_id;
                        break 2;
                    }
                }
            }
        }

        // Si no hay quiz final
        if (!$final_quiz_id) {
            return array(
                'score'      => 0,
                'percentage' => 'N/A',
                'attempts'   => 0,
            );
        }

        global $wpdb;
        // 3) Conseguir todos los activity_ids para este quiz
        $activity_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT activity_id 
                 FROM {$wpdb->prefix}learndash_user_activity
                 WHERE user_id = %d
                   AND course_id = %d
                   AND post_id = %d
                   AND activity_type = 'quiz'
                 ORDER BY activity_id ASC",
                $this->user_id,
                $this->course_id,
                $final_quiz_id
            )
        );

        $attempt_count = count($activity_ids);
        if ($attempt_count === 0) {
            return array(
                'score'      => 0,
                'percentage' => 'N/A',
                'attempts'   => 0,
            );
        }

        // 4) Tomar el ULTIMO activity_id (el de mayor índice en $activity_ids) como último intento
        $last_activity_id = end($activity_ids);

        // Sacamos percentage
        $last_percentage = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT activity_meta_value
                 FROM {$wpdb->prefix}learndash_user_activity_meta
                 WHERE activity_id = %d
                   AND activity_meta_key = 'percentage'",
                $last_activity_id
            )
        );
        // Sacamos score también
        $last_score = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT activity_meta_value
                 FROM {$wpdb->prefix}learndash_user_activity_meta
                 WHERE activity_id = %d
                   AND activity_meta_key = 'score'",
                $last_activity_id
            )
        );

        // Devolvemos datos
        return array(
            'score'      => (!empty($last_score) || $last_score === '0') ? $last_score : 0,
            'percentage' => (!empty($last_percentage) || $last_percentage === '0') ? $last_percentage : 'N/A',
            'attempts'   => $attempt_count,
        );
    }
}
