<?php
// Asegúrate de que este archivo no sea accedido directamente
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();

// Verificar si el usuario está logueado
if ( !is_user_logged_in() || !$user_id ) {
    echo '<p>Debes estar conectado para ver tus cursos.</p>';
    return;
}

// Obtener los cursos del usuario
$user_courses = ld_get_mycourses( $user_id );

// Si no hay cursos inscritos
if ( empty( $user_courses ) ) {
    echo '<div class="no-courses">';
    echo '<p>No estás inscrito en ningún curso, visita el catálogo de cursos y una vez que te inscribas en uno podrás verlos en esta sección.</p>';
    echo '<a href="' . esc_url( home_url( '/cursos' ) ) . '" class="button ver-cursos" style="display: inline-block; font-size: 12px; padding: 10px 20px; background-color: #0073aa; color: #fff; text-decoration: none; border-radius: 5px;">VER CURSOS</a>';
    echo '</div>';
    return;
}

// Mostrar los cursos del usuario
echo '<div class="cursos-usuario">';
echo '<h3 style="color: black;">Mis Cursos</h3>';
echo '<p>Estos son los cursos que estás cursando.</p>';

foreach ( $user_courses as $course_id ) {
    $course_title = get_the_title( $course_id );
    $course_link = get_permalink( $course_id );
    $course_image = get_the_post_thumbnail( $course_id, 'medium' );

    $total_lessons = count(learndash_get_course_steps($course_id));
    $completed_lessons = learndash_course_get_completed_steps_legacy( $user_id, $course_id );

    $percentage_complete = ($total_lessons > 0) ? min(100, ($completed_lessons / $total_lessons) * 100) : 0;

    echo '<div class="curso-item" style="display: flex; align-items: center; margin-bottom: 20px;">';

    // Imagen del curso
    if ( $course_image ) {
        echo '<div class="curso-imagen" style="margin-right: 20px;">' . $course_image . '</div>';
    } else {
        echo '<div class="curso-imagen" style="width: 150px; height: 150px; background-color: #add8e6; margin-right: 20px;"></div>';
    }

    // Info del curso
    echo '<div class="curso-info" style="flex-grow: 1;">';
    echo '<a href="' . esc_url( $course_link ) . '">';
    echo '<h4 style="margin: 0 0 10px;">' . esc_html( $course_title ) . '</h4>';
    echo '</a>';

    echo '<div class="card__progress">';
    echo '<progress max="100" value="' . esc_attr( $percentage_complete ) . '"></progress>';
    echo '<p>' . esc_html( round($percentage_complete) ) . '% completado</p>';
    echo '</div>';

    echo '</div>'; // .curso-info
    echo '</div>'; // .curso-item
}

echo '</div>'; // .cursos-usuario
?>
