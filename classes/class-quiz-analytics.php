<?php

if (!class_exists('QuizAnalytics')) {
    class QuizAnalytics {
        private $user_id;

        public function __construct($user_id) {
            $this->user_id = $user_id;
        }

        public function get_user_quizzes() {
            global $wpdb;

            // Fetch quizzes from the database
            $results = $wpdb->get_results(
                $wpdb->prepare("
                    SELECT 
                        ua.activity_id,
                        ua.post_id AS quiz_id,
                        ua.course_id,
                        ua.activity_started,
                        ua.activity_completed,
                        am1.activity_meta_value AS score,
                        am2.activity_meta_value AS percentage,
                        am3.activity_meta_value AS points,
                        p.post_title AS quiz_title
                    FROM {$wpdb->prefix}learndash_user_activity ua
                    INNER JOIN {$wpdb->prefix}learndash_user_activity_meta am1 
                        ON ua.activity_id = am1.activity_id AND am1.activity_meta_key = 'score'
                    INNER JOIN {$wpdb->prefix}learndash_user_activity_meta am2 
                        ON ua.activity_id = am2.activity_id AND am2.activity_meta_key = 'percentage'
                    INNER JOIN {$wpdb->prefix}learndash_user_activity_meta am3 
                        ON ua.activity_id = am3.activity_id AND am3.activity_meta_key = 'points'
                    INNER JOIN {$wpdb->prefix}posts p
                        ON ua.post_id = p.ID
                    WHERE ua.user_id = %d
                    AND ua.activity_type = 'quiz'
                    ORDER BY ua.activity_completed ASC
                ", $this->user_id)
            );

            // Build array of quizzes
            $quizzes = [];
            foreach ($results as $quiz) {
                $quizzes[] = [
                    'title' => $quiz->quiz_title,
                    'date' => $quiz->activity_completed 
                        ? date('Y-m-d H:i:s', $quiz->activity_completed) 
                        : 'Not Completed',
                    'percentage' => $quiz->percentage,
                    'points' => $quiz->points,
                ];
            }

            return $quizzes;
        }

        public function calculate_variation() {
            $quizzes = $this->get_user_quizzes();

            if (count($quizzes) < 2) {
                return null; // Not enough quizzes to calculate variation
            }

            $first_quiz = $quizzes[0];
            $last_quiz = $quizzes[count($quizzes) - 1];

            $variation_percentage = $last_quiz['percentage'] - $first_quiz['percentage'];
            $variation_points = $last_quiz['points'] - $first_quiz['points'];

            return [
                'percentage_variation' => $variation_percentage,
                'points_variation' => $variation_points,
            ];
        }

        public function print_variation() {
            $variation = $this->calculate_variation();

            if (!$variation) {
                echo "No hay suficientes datos para calcular la variación.";
                return;
            }

            echo "Variación de porcentaje: {$variation['percentage_variation']}%<br>";
            echo "Variación de puntos: {$variation['points_variation']}";
        }
    }
}
