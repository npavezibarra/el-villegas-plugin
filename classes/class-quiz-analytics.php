<?php
class QuizAnalytics {
    private $quiz_id;
    private $course_id;
    private $first_quiz_id;
    private $is_final_quiz;
    private $user_id;

    public function __construct($quiz_id, $user_id = null) {
        global $wpdb;
        $this->quiz_id = intval($quiz_id);
        $this->user_id = $user_id ? intval($user_id) : get_current_user_id();

        // Step 1: Find the Course ID this quiz is attached to
        $this->course_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = 'ld_course_steps' 
             AND meta_value LIKE %s",
            '%' . $this->quiz_id . '%'
        ));

        // Step 2: Find the First Quiz (if exists)
        $this->first_quiz_id = $this->course_id ? $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->postmeta} 
             WHERE post_id = %d AND meta_key = '_first_quiz_id'",
            $this->course_id
        )) : null;

        // Ensure first_quiz_id is an integer if valid, otherwise handle as not set
        $this->first_quiz_id = $this->first_quiz_id ? intval($this->first_quiz_id) : 0;

        // Step 3: Check if this quiz is the Final Quiz
        $this->is_final_quiz = ($this->course_id !== null);
    }

    /**
     * Returns the Course ID for this quiz
     */
    public function getCourse() {
        return $this->course_id ? $this->course_id : "Doesn't have";
    }

    /**
     * Returns the First Quiz ID for the course
     */
    public function getFirstQuiz() {
        return $this->first_quiz_id ? $this->first_quiz_id : "Doesn't have";
    }

    /**
     * Returns the Final Quiz ID (if this quiz is final)
     */
    public function getFinalQuiz() {
        return $this->is_final_quiz ? $this->quiz_id : "Doesn't have";
    }

    /**
     * Returns User Performance Data for the First Quiz
     */
    public function getFirstQuizPerformance() {
        if (!$this->first_quiz_id || $this->first_quiz_id === 0) {
            return array('score' => 0, 'percentage' => 'N/A', 'attempts' => 0, 'date' => "No Attempts");
        }
        return $this->getUserQuizPerformance($this->first_quiz_id);
    }

    /**
     * Returns User Performance Data for the Final Quiz
     */
    public function getFinalQuizPerformance() {
        if (!$this->is_final_quiz) {
            return array('score' => 0, 'percentage' => 'N/A', 'attempts' => 0, 'date' => "No Attempts");
        }
        return $this->getUserQuizPerformance($this->quiz_id);
    }

    /**
     * Generic function to retrieve User Quiz Performance
     */
    private function getUserQuizPerformance($quiz_id) {
        global $wpdb;
    
        $activity_id = $wpdb->get_var($wpdb->prepare(
            "SELECT activity_id FROM {$wpdb->prefix}learndash_user_activity
             WHERE user_id = %d AND post_id = %d AND activity_type = 'quiz'
             ORDER BY activity_completed DESC LIMIT 1",
            $this->user_id,
            $quiz_id
        ));
    
        if (!$activity_id) {
            return array('score' => 0, 'percentage' => 'N/A', 'attempts' => 0, 'date' => "No Attempts");
        }
    
        $score = $wpdb->get_var($wpdb->prepare(
            "SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity_meta
             WHERE activity_id = %d AND activity_meta_key = 'score'",
            $activity_id
        ));
    
        $percentage = $wpdb->get_var($wpdb->prepare(
            "SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity_meta
             WHERE activity_id = %d AND activity_meta_key = 'percentage'",
            $activity_id
        ));
    
        // Get the latest attempt date
        $latest_attempt_timestamp = $wpdb->get_var($wpdb->prepare(
            "SELECT activity_completed FROM {$wpdb->prefix}learndash_user_activity
             WHERE activity_id = %d",
            $activity_id
        ));
    
        // Convert timestamp to readable format
        $attempt_date = (!empty($latest_attempt_timestamp)) ? date('d F Y', $latest_attempt_timestamp) : "No Attempts";
    
        return array(
            'score' => !empty($score) ? $score : 0,
            'percentage' => !empty($percentage) ? $percentage : 'N/A',
            'attempts' => 1, // Since we fetched only the last attempt
            'date' => $attempt_date
        );
    }

    public function isFirstQuiz() {
        if (!$this->course_id || !$this->first_quiz_id || $this->first_quiz_id === 0) {
            return true; // Assume this is the first quiz if no first quiz is defined
        }
        return ($this->quiz_id == $this->first_quiz_id);
    }

    /**
     * Display results in HTML
     */
    public function displayResults() {
        echo "<div style='background: #f4f4f4; padding: 10px; border-radius: 5px;'>";
        echo "<p><strong>Quiz ID:</strong> " . esc_html($this->quiz_id) . "</p>";
        echo "<p><strong>Course ID:</strong> " . esc_html($this->getCourse()) . "</p>";
        echo "<p><strong>First Quiz ID:</strong> " . esc_html($this->getFirstQuiz()) . "</p>";
        echo "<p><strong>Final Quiz ID:</strong> " . esc_html($this->getFinalQuiz()) . "</p>";

        // Show First Quiz Performance
        $first_quiz_performance = $this->getFirstQuizPerformance();
        echo "<p><strong>First Quiz Score:</strong> " . esc_html($first_quiz_performance['score']) . "</p>";
        echo "<p><strong>First Quiz Percentage:</strong> " . esc_html($first_quiz_performance['percentage']) . "</p>";

        // Show Final Quiz Performance
        $final_quiz_performance = $this->getFinalQuizPerformance();
        echo "<p><strong>Final Quiz Score:</strong> " . esc_html($final_quiz_performance['score']) . "</p>";
        echo "<p><strong>Final Quiz Percentage:</strong> " . esc_html($final_quiz_performance['percentage']) . "</p>";

        echo "</div>";
    }
}