<?php

function villegas_registration_login_shortcode() {
    // Enqueue CSS and JavaScript files
    wp_enqueue_style('ingresa-roma-css', plugins_url('assets/ingresa-roma.css', __FILE__));
    wp_enqueue_script('form-toggle-js', plugins_url('login-register/form-toggle.js', __FILE__), array('jquery'), null, true);
    
    ob_start();

    // Check if the user is already logged in
    if (is_user_logged_in()) {
        // Redirect to the course if already logged in
        wp_redirect(home_url('/la-republica-romana')); // Adjust this to your course URL
        exit;
    }

    // Variable to track if registration was successful
    $registration_successful = false;

    // Handle registration
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $password = $_POST['password'];
        $email = sanitize_email($_POST['email']);
        
        // Generate a unique username based on email or first and last names
        $username = sanitize_user($first_name . $last_name);
        if (username_exists($username)) {
            $username = sanitize_user($first_name . '.' . $last_name . rand(1000, 9999)); // Add random number if username exists
        }

        // Register user
        $userdata = array(
            'user_login' => $username,
            'user_pass' => $password,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'subscriber' // Set role to 'subscriber' for new users
        );
        $user_id = wp_insert_user($userdata);
        
        if (!is_wp_error($user_id)) {
            // Generate confirmation code
            $confirmation_code = wp_generate_password(20, false);
            update_user_meta($user_id, 'confirmation_code', $confirmation_code);

            // Send confirmation email
$to = $email;
$subject = 'Confirma tu cuenta';

// Define the confirmation link
$confirmation_link = home_url('/?confirm=' . $confirmation_code . '&user=' . $user_id);

// Set the HTML content for the email
$message = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirma tu cuenta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
        }
        h2 {
            color: #4c8bf5;
            text-align: center;
        }
        p {
            text-align: center;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin-top: 30px;
        }
        .button {
            padding: 12px 24px;
            background-color: #4c8bf5;
            color: #ffffff;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #357ae8;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h2>¡Te has registrado en Tienda El Villegas!</h2>
        <p>Para verificar que realmente quisiste registrarte, haz click en el botón de abajo:</p>
        <div class="button-container">
            <a href="' . esc_url($confirmation_link) . '" class="button">CONFIRMAR</a>
        </div>
        <p>Si no te registraste, puedes ignorar este mensaje.</p>
    </div>
</body>
</html>';

// Set the Content-Type header to send HTML email
$headers = array('Content-Type: text/html; charset=UTF-8');

// Send the email
wp_mail($to, $subject, $message, $headers);


            // Automatically log in the user
            $creds = array(
                'user_login' => $username,
                'user_password' => $password,
                'remember' => true,
            );

            $user = wp_signon($creds, false);
            if (!is_wp_error($user)) {
                $registration_successful = true; // Set flag to indicate successful registration
            }
        }
    }

    // Handle login
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        
        // Prepare credentials for login
        $creds = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true,
        );

        $user = wp_signon($creds, false);
        if (!is_wp_error($user)) {
            // Successful login, redirect to the course page
            wp_redirect(home_url('/la-republica-romana')); // Adjust this to your course URL
            exit;
        } else {
            // Display error if login fails
            echo '<p style="color:red; text-align: center;">Error: Nombre de usuario o contraseña incorrectos.</p>';
        }
    }

    // Display success message if registration is successful
    if ($registration_successful) {
        echo '<div id="mensaje-registro" style="text-align: center; padding: 80px 0px;">';
        echo '<h3>Registro exitoso.</h3>';
        echo '<p>Por favor revisa tu correo para confirmar tu cuenta.</p>';
        
        // Adjusted to display as a button
        echo '<button onclick="window.location.href=\'' . esc_url(home_url('/la-republica-romana')) . '\'" class="button" style="background-color: #4c8bf5; color: white; padding: 10px 20px; border-radius: 5px; cursor: pointer;">';
        echo 'Ir al curso';
        echo '</button>';
        
        echo '</div>';
    } else {
        // Display the login and registration forms if registration was not successful
        ?>
        <div id="ingresa-roma">
            <div id="form-container">
                <form method="POST" id="auth-form">
                    <h2 id="form-title">Iniciar Sesión</h2>
                    <p id="form-subtitle">Si no tienes cuenta, <a href="#" id="toggle-form">regístrate aquí</a></p>
                    
                    <input type="text" name="username" placeholder="Nombre de usuario" required id="username">
                    <input type="password" name="password" placeholder="Contraseña" required id="password">
                    <input type="hidden" name="action" value="login" id="form-action">
                    
                    <input type="submit" value="Iniciar Sesión">
                    <p id="forgot-pass"><a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Olvidé la contraseña</a></p>
                </form>

                <form method="POST" id="registration-form" style="display:none;">
                    <h2 id="form-title-register">Registro</h2>
                    <p id="form-subtitle-register">Si ya tienes cuenta, <a href="#" id="toggle-form-login">inicia sesión aquí</a></p>
                    <input type="text" name="first_name" placeholder="Nombre" required id="register-first-name">
                    <input type="text" name="last_name" placeholder="Apellido" required id="register-last-name">
                    <input type="email" name="email" placeholder="Correo electrónico" required id="register-email">
                    <input type="password" name="password" placeholder="Contraseña" required id="register-password">
                    <input type="hidden" name="action" value="register">
                    
                    <input type="submit" value="Registrarse">
                </form>
            </div>
        </div>
        <?php
    }

    return ob_get_clean();
}
add_shortcode('villegas_registration_login', 'villegas_registration_login_shortcode');
