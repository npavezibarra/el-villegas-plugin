<!-- HEADER -->
<?php
include plugin_dir_path(__FILE__) . 'template-parts/header.php';
echo do_blocks('<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->');

global $post;
$quiz_id = $post->ID;

// Obtener imagen personalizada
$image_id = get_post_meta($quiz_id, '_quiz_style_image', true);
$image_url = $image_id ? wp_get_attachment_url($image_id) : '';

$body_class = 'quiz-style-' . $quiz_id;
?>

<?php if ($image_url): ?>
<style>
    body.<?php echo esc_attr($body_class); ?> {
        background-image: url('<?php echo esc_url($image_url); ?>');
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: center center;
    }
</style>
<?php endif; ?>

<body class="<?php echo esc_attr($body_class); ?>">

<div class="custom-quiz-layout">
    <div id="quiz-card">
    <?php
    the_title('<h1>', '</h1>');

    setlocale(LC_TIME, 'es_ES.UTF-8');
    echo strftime('%e de %B de %Y');

    $quiz_type = 'final';
    $terms = wp_get_post_terms($quiz_id, 'ld_quiz_category');
    foreach ($terms as $term) {
        if (strtolower($term->name) === 'primera') {
            $quiz_type = 'first';
            break;
        }
    }

    $course_id = learndash_get_course_id($quiz_id);
    $course_title = get_the_title($course_id);

    // Mensaje personalizado
    $mensaje = '';
    if ($quiz_type === 'first') {
        $mensaje = '
            <div id="quiz-start-message" class="custom-quiz-message" style="margin-bottom: 30px;">
                <a href="' . esc_url(wp_get_referer()) . '" class="back-to-course-link" style="display:block;margin-bottom:10px;">Volver al curso</a>
                <p>
                    Estás a punto de realizar la <strong>Prueba Inicial</strong> del curso <strong>' . esc_html($course_title) . '</strong>. 
                    Esta evaluación tiene como objetivo medir tus conocimientos antes de comenzar. Consta de 30 preguntas contrarreloj, 
                    con 45 segundos para cada una. Recuerda que solo puedes rendirla 3 veces. 
                    <br><br>
                    Una vez finalices todas las lecciones del curso, podrás acceder a la Prueba Final para comparar tu progreso. 
                    ¡Te deseamos lo mejor!
                </p>
            </div>';
    } else {
        $mensaje = '
            <div id="quiz-start-message" class="custom-quiz-message" style="margin-bottom: 30px;">
                <a href="' . esc_url(wp_get_referer()) . '" class="back-to-course-link" style="display:block;margin-bottom:10px;">Volver al curso</a>
                <p>
                    Estás a punto de rendir la <strong>Prueba Final</strong> del curso <strong>' . esc_html($course_title) . '</strong>. 
                    Esta evaluación final te permitirá conocer cuánto has avanzado desde que comenzaste. 
                    Al completarla, recibirás una tabla comparativa entre esta prueba y la inicial, para que puedas visualizar tu progreso.
                    <br><br>
                    Consta de 30 preguntas contrarreloj, con un límite de 45 segundos por pregunta. 
                    Tienes un máximo de 3 intentos. ¡Mucho éxito!
                </p>
            </div>';
    }

    echo $mensaje;
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var startBtn = document.querySelector('.wpProQuiz_button[name="startQuiz"]');
        var msg = document.getElementById('quiz-start-message');
        if (startBtn && msg) {
            startBtn.addEventListener('click', function () {
                msg.style.display = 'none';
            });
        }
    });
    </script>

    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            the_content();
        endwhile;
    endif;
    ?>
    </div>
</div>

</body>

<!-- FOOTER -->
<?php 
echo do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->');
wp_footer(); 
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.querySelector('.wp-block-navigation__responsive-container-open');
    const closeBtn = document.querySelector('.wp-block-navigation__responsive-container-close');
    const container = document.querySelector('.wp-block-navigation__responsive-container');

    if (openBtn && container) {
        openBtn.addEventListener('click', function () {
            container.classList.add('is-menu-open');
        });
    }

    if (closeBtn && container) {
        closeBtn.addEventListener('click', function () {
            container.classList.remove('is-menu-open');
        });
    }
});
</script>
