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