<?php
/**
 * Plugin Name: Villegas Courses Plugin
 * Description: Plugin de customización experiencia cursos en sitio El Villegas.
 * Version: 1.0
 * Author: Nicolás Pavez
 */

// Reemplazar la plantilla del curso de LearnDash
function my_custom_ld_course_template( $template ) {
    if ( is_singular( 'sfwd-courses' ) ) {
        $custom_template = plugin_dir_path( __FILE__ ) . 'templates/single-sfwd-course.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'my_custom_ld_course_template' );

// Reemplaza Archive COurses
function villegas_override_learndash_templates( $template ) {
    if ( is_post_type_archive( 'sfwd-courses' ) ) {
        $custom_template = plugin_dir_path( __FILE__ ) . 'templates/archive-sfwd-courses.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'villegas_override_learndash_templates', 99 );



// Encolar estilo de Course Page
function my_custom_ld_course_styles() {
    // Encolar estilos específicos de la página del curso
    wp_enqueue_style('my-course-page-style', plugin_dir_url(__FILE__) . 'assets/course-page.css', [], '1.0', 'all');
    wp_enqueue_style('ingresa-roma-css', plugin_dir_url(__FILE__) . 'assets/ingresa-roma.css', [], '1.0', 'all');
    wp_enqueue_style('comprar-stats-css', plugin_dir_url(__FILE__) . 'assets/comprar-stats.css', [], '1.0', 'all');
    wp_enqueue_style('my-account-css', plugin_dir_url(__FILE__) . 'assets/my-account.css', [], '1.0', 'all');
    wp_enqueue_style('single-lessons-css', plugin_dir_url(__FILE__) . 'assets/single-lessons.css', [], '1.0', 'all');
    wp_enqueue_style('login-form-styles', plugin_dir_url(__FILE__) . 'login/login-form-styles.css', [], '1.0', 'all');
    wp_enqueue_style('quiz-styles', plugin_dir_url(__FILE__) . 'assets/quiz-styles.css', [], '1.0', 'all');

    // Encolar estilo solo en la página de cursos
    if (is_post_type_archive('sfwd-courses')) {
        wp_enqueue_style('archive-courses-style', plugin_dir_url(__FILE__) . 'assets/archive-courses.css', [], '1.0', 'all');
    }

    // Verificar si el shortcode [login_register_form] está presente en la página
    global $post;
    if (isset($post->post_content) && has_shortcode($post->post_content, 'login_register_form')) {
        // Encolar estilos específicos para el formulario de login/registro
        wp_enqueue_style('login-register-style', plugin_dir_url(__FILE__) . 'assets/login-register.css', [], '1.0', 'all');
    }
}
add_action('wp_enqueue_scripts', 'my_custom_ld_course_styles');


// Incluir metabox personalizado y otros archivos necesarios
include_once 'learndash-course-metabox.php';
include_once 'functions.php';
include plugin_dir_path(__FILE__) . 'parts/comprar-stats.php';
include_once plugin_dir_path(__FILE__) . 'metabox-course-first-quiz.php';
include_once plugin_dir_path(__FILE__) . 'woo-tabs.php';
// Include the leaderboard-villegas.php file
require_once plugin_dir_path(__FILE__) . 'leaderboard-villegas/leaderboard-villegas.php';

/* LOGIN MECHANISM */
require_once plugin_dir_path(__FILE__) . 'login/shortcode-login-register.php';
require_once plugin_dir_path(__FILE__) . 'login/email-confirmation.php';
require_once plugin_dir_path(__FILE__) . 'login/process-registration.php';
/* CLASSES */
require_once plugin_dir_path(__FILE__) . 'classes/class-quiz-analytics.php';
require_once plugin_dir_path(__FILE__) . 'shortcodes/quiz-class-shortcodes.php';
/* AJAX HANDLER*/
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';



// Customize LearnDash quiz result template by replacing the original with a custom one.

add_filter('learndash_template', 'custom_quiz_result_template', 10, 5);
function custom_quiz_result_template($filepath, $name, $args, $echo, $return_file_path) {
    if ($name == 'quiz/partials/show_quiz_result_box.php') {
        $custom_template_path = plugin_dir_path(__FILE__) . 'templates/show_quiz_result_box.php';
        if (file_exists($custom_template_path)) {
            return $custom_template_path;
        }
    }
    return $filepath;
}

function enqueue_login_scripts() {
    wp_enqueue_script('form-toggle', plugin_dir_url(__FILE__) . 'login/form-toggle.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_login_scripts');


// Enqueue custom CSS and JavaScript for quiz and lesson pages.

function enqueue_quiz_resources() {
    $plugin_url = plugin_dir_url(__FILE__);

    // Common CSS files
    wp_enqueue_style('quiz-result-style', $plugin_url . 'assets/quiz-result.css', [], '1.0', 'all');
    wp_enqueue_style('custom-left-div-style', $plugin_url . 'assets/custom-left-div.css', [], '1.0', 'all');
    wp_enqueue_style('woo-tabs-style', $plugin_url . 'assets/woo-tabs.css', [], '1.0', 'all');

    // Check if it's a quiz page
    if (is_singular('sfwd-quiz')) {
        wp_enqueue_script('custom-quiz-message', $plugin_url . 'assets/custom-quiz-message.js', [], '1.0', true);
        $course_id = learndash_get_course_id();
        $course_title = get_the_title($course_id);
        wp_localize_script('custom-quiz-message', 'quizData', ['courseName' => $course_title]);
    }

    // Check if it's a lesson page
    if (is_singular('sfwd-lessons')) {
        wp_enqueue_script('custom-lesson-script', $plugin_url . 'assets/custom-lesson-script.js', [], '1.0', true);
        wp_localize_script('custom-lesson-script', 'lessonData', [
            'lessonList' => 'Here is where your lesson list would go',
            'arrowImageUrl' => $plugin_url . 'assets/arrow.svg'
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_quiz_resources');

/**
 * Require the course outline functionality from an external file.
 */
require_once plugin_dir_path(__FILE__) . 'course-outline.php';


/**
 * Hook into 'wp_footer' to dynamically add the new div before entry content.
 */
add_action('wp_footer', 'insert_div_before_entry_content');

/**
 * Register the shortcode for the registration/login functionality
 */
add_shortcode('login_register_form', 'login_register_form_shortcode');

/* OVERRIDE LESSON PAGE */

add_filter('template_include', 'el_villegas_override_single_sfwd_lessons', 99);

function el_villegas_override_single_sfwd_lessons($template) {
    if (is_singular('sfwd-lessons')) { // Check if it's a lesson page
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-sfwd-lessons.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template; // Fallback to the default template
}

/* OVERRIDE INFOBAR PHP */

add_filter('learndash_template', 'el_villegas_override_infobar_template', 10, 5);

function el_villegas_override_infobar_template($filepath, $name, $args, $type, $template_key) {
    // Check if the requested template is the infobar.php file
    if ($name === 'modules/infobar.php') {
        // Return the path to your custom infobar.php
        $custom_template = plugin_dir_path(__FILE__) . 'templates/infobar.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $filepath; // Return the original template if no override
}


/* OVERRIDE QUIZ PAGE */

add_filter('template_include', 'el_villegas_override_single_sfwd_quiz', 99);

function el_villegas_override_single_sfwd_quiz($template) {
    if (is_singular('sfwd-quiz')) { // Check if it's a quiz page
        $custom_template = plugin_dir_path(__FILE__) . 'templates/single-sfwd-quiz.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template; // Fallback to the default template
}

/* EMAIL FIRST QUIZ */

// Registrar el endpoint AJAX para usuarios logueados y no logueados
add_action('wp_ajax_enviar_correo_first_quiz', 'enviar_correo_first_quiz_handler');
add_action('wp_ajax_nopriv_enviar_correo_first_quiz', 'enviar_correo_first_quiz_handler');

function enviar_correo_first_quiz_handler() {
    // Recibir y sanitizar los datos enviados vía AJAX
    $quiz_percentage = isset($_POST['quiz_percentage']) ? intval($_POST['quiz_percentage']) : 0;
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ( !$user_id || !$quiz_id ) {
        wp_send_json_error('Datos faltantes');
        wp_die();
    }

    // Obtener datos del usuario
    $user = get_userdata($user_id);
    if ( !$user ) {
        wp_send_json_error('Usuario no encontrado');
        wp_die();
    }
    $user_email = $user->user_email;
    $user_name  = $user->display_name;
    $quiz_title = get_the_title($quiz_id);

    // Cargar el contenido del correo desde el archivo de plantilla
    // Ajusta la ruta según la estructura de tu plugin.
    $email_file = plugin_dir_path(__FILE__) . 'emails/first-quiz-email.php';
    if ( file_exists($email_file) ) {
        $email_content = file_get_contents($email_file);
    } else {
        $email_content = '<p>Has finalizado el First Quiz.</p>';
    }

    // Reemplazar los marcadores con los valores reales
    $email_content = str_replace('{{user_name}}', $user_name, $email_content);
    $email_content = str_replace('{{quiz_name}}', $quiz_title, $email_content);
    $email_content = str_replace('{{quiz_percentage}}', $quiz_percentage, $email_content);

    $subject = 'Has finalizado el First Quiz';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Enviar el correo
    $sent = wp_mail($user_email, $subject, $email_content, $headers);
    if ( $sent ) {
        wp_send_json_success('Correo enviado');
    } else {
        wp_send_json_error('Error al enviar el correo');
    }
    wp_die();
}

/* FINAL QUIZ EMAIL */

add_action('wp_ajax_enviar_correo_final_quiz', 'handle_enviar_correo_final_quiz');
add_action('wp_ajax_nopriv_enviar_correo_final_quiz', 'handle_enviar_correo_final_quiz');

function handle_enviar_correo_final_quiz() {
    $quiz_id         = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $user_id         = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();
    $quiz_percentage = isset($_POST['quiz_percentage']) ? intval($_POST['quiz_percentage']) : 0;

    if ( $quiz_id === 0 || $user_id === 0 ) {
        wp_send_json_error('Datos incompletos');
    }

    // Obtener info del usuario y del quiz
    $user        = get_userdata($user_id);
    $quiz_title  = get_the_title($quiz_id);
    $user_email  = $user->user_email;
    $user_name   = $user->display_name;

    // Obtener fecha de finalización del quiz
    global $wpdb;
    $completed_ts = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT activity_completed
             FROM {$wpdb->prefix}learndash_user_activity
             WHERE user_id = %d
               AND post_id = %d
               AND activity_type = 'quiz'
             ORDER BY activity_completed DESC
             LIMIT 1",
            $user_id,
            $quiz_id
        )
    );
    $completion_date = $completed_ts ? date_i18n('j \d\e F \d\e Y', (int) $completed_ts) : '';

    // Obtener datos del First Quiz usando QuizAnalytics
    if ( ! class_exists( 'QuizAnalytics' ) ) {
        require_once plugin_dir_path(__FILE__) . 'classes/class-quiz-analytics.php';
    }

    $quiz_checker = new QuizAnalytics($quiz_id);
    $first_quiz_id = $quiz_checker->getFirstQuiz();
    $first_quiz_title = get_the_title($first_quiz_id);
    $perf = $quiz_checker->getFirstQuizPerformance();

    $first_quiz_percentage = isset($perf['percentage']) ? (int) round($perf['percentage']) : 0;
    $first_quiz_date = isset($perf['date']) && strtotime($perf['date']) !== false
        ? date_i18n('F j', strtotime($perf['date']))
        : 'N/A';

    // Calcular variación y días para completar
    $knowledge_variation = $quiz_percentage - $first_quiz_percentage;
    $variation_arrow = $knowledge_variation >= 0 ? '▲' : '▼';
    $days_to_complete = 0;

    if (isset($perf['date']) && strtotime($perf['date']) !== false && $completed_ts) {
        $first_ts = strtotime($perf['date']);
        $diff = $completed_ts - $first_ts;
        $days_to_complete = max(1, floor($diff / (60 * 60 * 24)));
    }

    // Cargar contenido del correo con variables disponibles
    ob_start();
    include plugin_dir_path(__FILE__) . 'emails/final-quiz-email.php';
    $message = ob_get_clean();

    $to      = $user_email;
    $subject = '¡Has completado el Final Quiz!';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    $sent = wp_mail($to, $subject, $message, $headers);

    if ($sent) {
        wp_send_json_success('Correo enviado');
    } else {
        wp_send_json_error('Error al enviar el correo');
    }
}



