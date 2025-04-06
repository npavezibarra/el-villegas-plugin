<?php
// Mostrar el campo de imagen en la sección "Editar cuenta"
add_action('woocommerce_edit_account_form_start', 'villegas_profile_picture_field', 5);

function villegas_profile_picture_field() {
    $user_id = get_current_user_id();
    $profile_picture = get_user_meta($user_id, 'profile_picture', true);
    ?>
    <p class="form-row form-row-wide">
        <label for="profile_picture">Foto usuario</label><br>
        <?php if ($profile_picture): ?>
            <img src="<?php echo esc_url($profile_picture); ?>" style="width: 100px; height: 100px; border-radius: 50%; margin-top: 10px;">
        <?php endif; ?>
        <br><input type="file" name="profile_picture" id="profile_picture" accept="image/jpeg,image/png,image/webp">
    </p>
    <?php
}

// Guardar la imagen cuando el usuario edita su cuenta
add_action('woocommerce_save_account_details', 'villegas_save_profile_picture');
function villegas_save_profile_picture($user_id) {
    error_log('🧪 CONTENIDO $_FILES: ' . print_r($_FILES, true));
    error_log('FILES: ' . print_r($_FILES, true));
    if (!empty($_FILES)) {
        wc_add_notice('🧪 Archivos recibidos por el servidor', 'notice');
    } else {
        wc_add_notice('❌ El navegador NO envió ningún archivo', 'error');
    }
    

    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    
    if (
        !isset($_FILES['profile_picture']) ||
        $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK ||
        empty($_FILES['profile_picture']['tmp_name'])
    ) {
        wc_add_notice('⚠️ No se detectó archivo válido para subir.', 'notice');
        return;
    }

    $file = $_FILES['profile_picture'];

    // Validar tipo y tamaño
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed_types) || $file['size'] > 2 * 1024 * 1024) {
        wc_add_notice('Formato no válido o imagen demasiado grande (máx 2MB).', 'error');
        return;
    }

    // Subida al servidor
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $upload = wp_handle_upload($file, ['test_form' => false]);

    if (is_wp_error($upload)) {
        error_log('❌ wp_handle_upload() devolvió error WP_Error: ' . $upload->get_error_message());
        wc_add_notice('❌ wp_handle_upload() error: ' . $upload->get_error_message(), 'error');
        return;
    }

    error_log('✅ Resultado de wp_handle_upload(): ' . print_r($upload, true));
    wc_add_notice('✅ Resultado de wp_handle_upload(): ' . print_r($upload, true), 'notice');


    // Mostrar info útil
    error_log(print_r($upload, true));
    wc_add_notice('DEBUG Upload: ' . print_r($upload, true), 'notice');

    if (!$upload || isset($upload['error'])) {
        error_log('Fallo en wp_handle_upload: ' . print_r($upload, true));
        wc_add_notice('Error en wp_handle_upload: ' . (isset($upload['error']) ? $upload['error'] : 'Desconocido'), 'error');
        return;
    }

    wc_add_notice('Archivo subido: ' . $upload['url'], 'success');

    // Redimensionar la imagen
    $editor = wp_get_image_editor($upload['file']);
    if (!is_wp_error($editor)) {
        $editor->resize(200, 200, true);
        $editor->set_quality(90);
        $editor->save($upload['file']);
    }

    // Registrar como attachment
    $filename = basename($upload['file']);
    $wp_filetype = wp_check_filetype($filename, null);

    $attachment = [
        'guid'           => $upload['url'],
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $upload['file']);

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    update_user_meta($user_id, 'profile_picture', esc_url(wp_get_attachment_url($attach_id)));
}
