<?php
if (!defined('ABSPATH')) exit;

$course_id = intval($_POST['course_id'] ?? 0);
$user_id = get_current_user_id();

if (!$course_id || !$user_id) {
    echo '<p>Error: faltan datos.</p>';
    return;
}

// Obtener nombre del curso
$course_title = get_the_title($course_id);

// Obtener IDs de quizzes
$first_quiz_id = get_post_meta($course_id, '_first_quiz_id', true);
$quiz_steps = learndash_course_get_steps_by_type($course_id, 'sfwd-quiz');
$final_quiz_id = !empty($quiz_steps) ? end($quiz_steps) : 0;

global $wpdb;

// Obtener datos del First Quiz
$first_data = ['pct' => 0, 'date' => null];
if ($first_quiz_id) {
    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT activity_id, activity_completed FROM {$wpdb->prefix}learndash_user_activity 
         WHERE user_id = %d AND post_id = %d AND activity_type = 'quiz' 
         ORDER BY activity_completed DESC LIMIT 1",
        $user_id, $first_quiz_id
    ));
    if ($attempt) {
        $pct = $wpdb->get_var($wpdb->prepare(
            "SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity_meta 
             WHERE activity_id = %d AND activity_meta_key = 'percentage'",
            $attempt->activity_id
        ));
        $first_data['pct'] = round(floatval($pct));
        $first_data['date'] = $attempt->activity_completed;
    }
}

// Obtener datos del Final Quiz
$final_data = ['pct' => 0, 'date' => null];
if ($final_quiz_id) {
    $attempt = $wpdb->get_row($wpdb->prepare(
        "SELECT activity_id, activity_completed FROM {$wpdb->prefix}learndash_user_activity 
         WHERE user_id = %d AND post_id = %d AND activity_type = 'quiz' 
         ORDER BY activity_completed DESC LIMIT 1",
        $user_id, $final_quiz_id
    ));
    if ($attempt) {
        $pct = $wpdb->get_var($wpdb->prepare(
            "SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity_meta 
             WHERE activity_id = %d AND activity_meta_key = 'percentage'",
            $attempt->activity_id
        ));
        $final_data['pct'] = round(floatval($pct));
        $final_data['date'] = $attempt->activity_completed;
    }
}

// Si faltan datos, mostrar mensaje
if (!$first_data['pct'] || !$first_data['date'] || !$final_data['pct'] || !$final_data['date']) {
    echo '<p>Faltan resultados para mostrar comparativa.</p>';
    return;
}

// Calcular variación y días
$variation = $final_data['pct'] - $first_data['pct'];
$days_diff = floor(($final_data['date'] - $first_data['date']) / (60 * 60 * 24));
$days_diff = max(1, $days_diff); // nunca mostrar "0 días"
?>

<style>
.quiz-results-container,
.extra-stats-container {
    border: 1px solid #d5d5d5;
    padding: 20px;
    border-radius: 8px;
    background: white;
    margin-bottom: 20px;
}
.quiz-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.quiz-name {
    font-weight: bold;
    font-size: 16px;
}
.quiz-percentage {
    font-size: 24px;
    font-weight: bold;
    text-align: right;
}
</style>
<p style="font-size: 10px; text-align: center; margin-bottom: 10px; letter-spacing: 10px;">CURSO</p>
<h3 style="text-align: center; margin-top: 0px;"><?php echo esc_html($course_title); ?></h3>
<div class="quiz-results-container">
    

    <div class="quiz-flex">
        <div>
            <div class="quiz-name">Prueba Inicial</div>
            <div style="color: #666;"><?php echo date('F j', $first_data['date']); ?></div>
        </div>
        <div style="width: 50%; background: #e9ecef; border-radius: 15px; height: 20px; overflow: hidden;">
            <div style="width: <?php echo $first_data['pct']; ?>%; height: 100%; background: #ff9800;"></div>
        </div>
        <div class="quiz-percentage"><?php echo $first_data['pct']; ?>%</div>
    </div>
</div>

<div class="quiz-results-container">
    <div class="quiz-flex">
        <div>
            <div class="quiz-name">Prueba Final</div>
            <div style="color: #666;"><?php echo date('F j', $final_data['date']); ?></div>
        </div>
        <div style="width: 50%; background: #e9ecef; border-radius: 15px; height: 20px; overflow: hidden;">
            <div style="width: <?php echo $final_data['pct']; ?>%; height: 100%; background: #ff9800;"></div>
        </div>
        <div class="quiz-percentage"><?php echo $final_data['pct']; ?>%</div>
    </div>
</div>

<div class="extra-stats-container" style="margin-bottom: 0px;">
    <div class="quiz-flex">
        <div style="flex: 1; text-align: center;">
            <div style="font-size: 16px; color: #666;">Variación conocimientos</div>
            <div style="font-size: 36px; font-weight: bold; color: <?php echo $variation >= 0 ? '#9fd99f' : 'red'; ?>">
                <?php echo abs($variation); ?>% <span><?php echo $variation >= 0 ? '▲' : '▼'; ?></span>
            </div>
        </div>
        <div style="flex: 1; text-align: center;">
            <div style="font-size: 16px; color: #666;">Completaste el curso en</div>
            <div style="font-size: 36px; font-weight: bold;"><?php echo $days_diff . ' ' . ($days_diff === 1 ? 'día' : 'días'); ?></div>
        </div>
    </div>
</div>
