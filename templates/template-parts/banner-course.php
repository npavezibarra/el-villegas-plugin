<?php
/**
 * Custom Banner for Courses / Products
 * File: banner-course.php
 */

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

// Asegurarnos de tener un $post disponible (en el Loop o global)
global $post;
if ( ! $post ) {
    return; // Si no hay $post, no hacemos nada
}

$post_id = $post->ID;

// Obtener URL de imagen destacada o un placeholder
$thumbnail_url = has_post_thumbnail( $post_id ) 
    ? get_the_post_thumbnail_url( $post_id, 'full' ) 
    : 'https://via.placeholder.com/1920x1080';

// Obtener título y autor
$title = get_the_title( $post_id );

$author_id   = get_post_field( 'post_author', $post_id );
$first_name  = get_the_author_meta( 'first_name', $author_id );
$last_name   = get_the_author_meta( 'last_name', $author_id );
$author_name = trim( esc_html( $first_name . ' ' . $last_name ) );

// Generar el banner
?>
<div id="body-content" style="background-image: url('<?php echo esc_url( $thumbnail_url ); ?>'); 
                             background-size: cover; 
                             background-position: center; 
                             background-repeat: no-repeat; 
                             padding: 40px 20px; 
                             margin-bottom: 20px;
                             z-index: -9999999;">
    <div id="datos-generales-curso">
        <h1><?php echo esc_html( $title ); ?></h1>
        <?php if ( ! empty( $author_name ) ) : ?>
            <h4>Profesor <?php echo $author_name; ?></h4>
        <?php endif; ?>
    </div>
</div>
