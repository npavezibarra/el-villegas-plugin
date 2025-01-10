<!-- HEADER -->
<?php
include plugin_dir_path(__FILE__) . 'template-parts/header.php';
// Load the default Twenty Twenty-Four header template part
echo do_blocks('<!-- wp:template-part {"slug":"header","area":"header","tagName":"header"} /-->');
?>
<body>
<div class="custom-quiz-layout">
    <div id="quiz-card">
    <?php
    the_title('<h1>', '</h1>');
    setlocale(LC_TIME, 'es_ES.UTF-8');  // Set the locale to Spanish
echo strftime('%e de %B de %Y');  // Outputs the date as "2 de Febrero de 2025"

    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            // The Quiz content
            the_content();
        endwhile;
    endif;
    ?>
    </div>
</div>
</body>

<!--FOOTER -->
<?php 
// Load the default Twenty Twenty-Four footer template part
echo do_blocks('<!-- wp:template-part {"slug":"footer","area":"footer","tagName":"footer"} /-->');

wp_footer(); 
?>
