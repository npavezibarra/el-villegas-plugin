<?php
// blocks/course-link-block.php

function el_villegas_register_course_link_block() {
    // Register the block script inline
    wp_register_script(
        'el-villegas-course-link-block-inline',
        '', // No src, as we're inlining the script
        ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor'],
        null,
        true
    );

    // Inline the JavaScript for the block
    wp_add_inline_script(
        'el-villegas-course-link-block-inline',
        'wp.blocks.registerBlockType("el-villegas/course-link", {
            title: "Course Link Block",
            icon: "admin-links",
            category: "common",
            edit: function() {
                return wp.element.createElement("div", {className: "course-link-block"}, "This is your course");
            },
            save: function() {
                return wp.element.createElement("div", {className: "course-link-block"}, "This is your course");
            }
        });'
    );

    // Register the block with the inline script
    register_block_type('el-villegas/course-link', [
        'editor_script' => 'el-villegas-course-link-block-inline'
    ]);
}
