<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resultados del Final Quiz</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px;">
        <h2 style="color: #333;">¡Hola <?php echo esc_html($user_name); ?>!</h2>
        <p style="color: #555;">Has completado el <strong>Final Quiz</strong> del curso <strong><?php echo esc_html($quiz_title); ?></strong> el día <strong><?php echo esc_html($completion_date); ?></strong>.</p>

        <!-- Final Quiz -->
        <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
            <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;"><?php echo esc_html($quiz_title); ?></div>
            <div style="color: #888; font-size: 14px; margin-bottom: 10px;"><?php echo esc_html($completion_date); ?></div>
            <div style="background: #e9ecef; border-radius: 4px; height: 24px; overflow: hidden;">
                <div style="width: <?php echo esc_attr($quiz_percentage); ?>%; height: 100%; background: #ffc0cb;"></div>
            </div>
            <div style="text-align: right; font-size: 20px; font-weight: bold; margin-top: 10px;"><?php echo esc_html($quiz_percentage); ?>%</div>
        </div>

        <!-- First Quiz -->
        <div style="margin-top: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
            <div style="font-weight: bold; font-size: 16px; margin-bottom: 5px;"><?php echo esc_html($first_quiz_title); ?></div>
            <div style="color: #888; font-size: 14px; margin-bottom: 10px;"><?php echo esc_html($first_quiz_date); ?></div>
            <div style="background: #e9ecef; border-radius: 4px; height: 24px; overflow: hidden;">
                <div style="width: <?php echo esc_attr($first_quiz_percentage); ?>%; height: 100%; background: #ffc0cb;"></div>
            </div>
            <div style="text-align: right; font-size: 20px; font-weight: bold; margin-top: 10px;"><?php echo esc_html($first_quiz_percentage); ?>%</div>
        </div>

        <!-- Resumen -->
        <div style="margin-top: 30px; padding: 20px; background-color: #ffffff; border: 1px solid #d5d5d5; border-radius: 8px; display: flex; justify-content: space-between; text-align: center;">
            <div style="flex: 1;">
                <div style="font-size: 16px; color: #666;">Variación conocimientos</div>
                <div style="font-size: 28px; font-weight: bold; color: <?php echo $knowledge_variation >= 0 ? 'green' : 'red'; ?>;">
                    <?php echo abs($knowledge_variation); ?>% <?php echo $variation_arrow; ?>
                </div>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 16px; color: #666;">Completaste el curso en</div>
                <div style="font-size: 28px; font-weight: bold;">
                    <?php echo intval($days_to_complete); ?> días
                </div>
            </div>
        </div>

        <p style="margin-top: 30px; color: #555;">¡Felicitaciones por tu progreso!</p>
    </div>

</body>
</html>
