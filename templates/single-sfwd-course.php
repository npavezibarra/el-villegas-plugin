<?php
// Template for displaying a page with the default Twenty Twenty-Four theme header and footer.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php
// Load the default Twenty Twenty-Four header template part
echo do_blocks('<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->');

// Start the main content area
?>

<div id="mensaje-logeado">
    <?php 
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $first_name = $current_user->user_firstname;
        $display_name = $current_user->display_name;
        echo 'Hola ' . esc_html(!empty($first_name) ? $first_name : $display_name) . " ";
        echo '<a class="logout-cuenta" href="' . esc_url(wp_logout_url(get_permalink())) . '">Cerrar sesión</a>';
    } else {
        $register_page_check = get_page_by_path('ingresa-roma');
        $register_page_url = $register_page_check ? get_permalink($register_page_check) : wp_login_url();
        echo 'No estás logeado <a class="logout-cuenta" href="' . esc_url($register_page_url) . '">log in</a>';
    }
    ?>
</div>
<div id="body-content" style="background-image: url(<?php 
    if (has_post_thumbnail()) {
        echo get_the_post_thumbnail_url(null, 'full');
    } else {
        echo 'https://via.placeholder.com/1920x1080';
    }
?>); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div id="datos-generales-curso">
        <h1><?php the_title(); ?></h1>
        <h4>Profesor <?php 
            $author_id = get_post_field('post_author', get_the_ID());
            $first_name = get_the_author_meta('first_name', $author_id);
            $last_name = get_the_author_meta('last_name', $author_id);
            echo esc_html($first_name . ' ' . $last_name);
        ?></h4>
    </div>
</div>

<div id="buy-button-stats">
    <?php
    if (function_exists('mostrar_comprar_stats')) {
        mostrar_comprar_stats();
    }
    ?>
</div>

<div id="about-course">
    <div id="course-content">
        <h4 style="color: black;">Contenido del curso</h4>
        <ul style="list-style-type: none; padding-left: 0;">
            <?php
            $course_id = get_the_ID();
            if ($course_id) {
                $lessons_query = new WP_Query(array(
                    'post_type' => 'sfwd-lessons',
                    'meta_key' => 'course_id',
                    'meta_value' => $course_id,
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                    'posts_per_page' => -1,
                ));

                $user_id = get_current_user_id();
                $course_builder_meta = get_post_meta($course_id, 'course_sections', true);
                $section_headers = json_decode($course_builder_meta, true) ?? []; // Default to empty array if null

                $lessons = $lessons_query->posts;
                $lesson_index = 0;

                for ($step_index = 0; $step_index < count($lessons) + count($section_headers); $step_index++) {
                    $current_section = array_filter($section_headers, function($header) use ($step_index) {
                        return isset($header['order']) && $header['order'] == $step_index;
                    });

                    if (!empty($current_section)) {
                        $current_section = reset($current_section);
                        echo '<li class="course-section-header" style="margin-bottom: 10px; padding: 20px;">';
                        echo '<h4>' . esc_html($current_section['post_title']) . '</h4>';
                        echo '</li>';
                        continue;
                    }

                    if ($lesson_index < count($lessons)) {
                        $lesson_post = $lessons[$lesson_index];
                        $lesson_id = $lesson_post->ID;

                        $is_completed = learndash_is_lesson_complete($user_id, $lesson_id);
                        $circle_color_class = $is_completed ? 'completed' : 'not-completed';

                        echo '<li class="lesson-item ' . esc_attr($circle_color_class) . '" style="display: flex; align-items: center; margin-bottom: 10px;">';
                        echo '<span class="lesson-circle" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 10px; background-color: ' . ($is_completed ? '#4c8bf5' : '#ccc') . ';"></span>';
                        echo '<a href="' . esc_url(get_permalink($lesson_id)) . '">' . esc_html(get_the_title($lesson_id)) . '</a>';
                        echo '</li>';
                        $lesson_index++;
                    }
                }

                wp_reset_postdata();

                $quizzes = learndash_get_course_quiz_list($course_id);
                if (!empty($quizzes)) {
                    echo '<hr>';
                    foreach ($quizzes as $quiz) {
                        echo '<li style="display: flex; align-items: center;">';
                        echo '<img src="' . esc_url(plugins_url('assets/svg/exam-icon.svg', __DIR__)) . '" alt="Exam Icon" style="width: 20px; height: 20px; margin-right: 10px;">';
                        echo '<a href="' . esc_url(get_permalink($quiz['post']->ID)) . '">' . esc_html($quiz['post']->post_title) . '</a>';
                        echo '</li>';
                    }
                } else {
                    echo '<p>No hay quizzes asociados a este curso.</p>';
                }
            }
            ?>
        </ul>
    </div>

    <div id="description-course">
        <h4>Sobre este curso</h4>
        <hr>
        <?php the_content(); ?>
    </div>
</div>

<div id="autor-box" style="padding: 40px; border-radius: 10px; background-color: #f9f9f9; display: flex; align-items: flex-start; margin-top: 20px;">
    <div style="flex: 0 0 auto; margin-right: 20px;">
        <div class="user-photo-circle" style="width: 70px; height: 70px; border-radius: 50%; display: flex; justify-content: center; align-items: center; background-color: red;">
            <?php 
            $author_id = get_post_field('post_author', get_the_ID());
            $user_photo_url = get_user_meta($author_id, 'profile_picture', true);

            if ($user_photo_url) {
                echo '<img src="' . esc_url($user_photo_url) . '" alt="Profile Photo" style="width: 100%; height: 100%; border-radius: 50%;">';
            } else {
                $first_name = get_the_author_meta('first_name', $author_id);
                echo '<span style="color: white; font-size: 24px;">' . esc_html(strtoupper(substr($first_name, 0, 1))) . '</span>';
            }
            ?>
        </div>
    </div>
    <div style="flex: 1;">
        <h2 style="margin: 0; font-size: 24px; text-align: left;">
            <?php 
            echo esc_html($first_name . ' ' . $last_name);
            ?>
        </h2>
        <p style="margin: 5px 0;"><?php echo esc_html(get_the_author_meta('description', $author_id)); ?></p>
    </div>
</div>

<?php 
// Load the default Twenty Twenty-Four footer template part
echo do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->');

wp_footer(); 
?>
</body>
</html>
