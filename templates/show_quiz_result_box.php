<?php


// Debug section - add this near the top of show_quiz_result_box.php
if (!function_exists('learndash_get_course_id')) {
    require_once WP_PLUGIN_DIR . '/sfwd-lms/includes/course/ld-course-functions.php';
}


/**
 * Displays Quiz Result Box
 *
 * Available Variables:
 *
 * @var object $quiz_view      WpProQuiz_View_FrontQuiz instance.
 * @var object $quiz           WpProQuiz_Model_Quiz instance.
 * @var array  $shortcode_atts Array of shortcode attributes to create the Quiz.
 * @var int    $question_count Number of Question to display.
 * @var array  $result         Array of Quiz Result Messages.
 *
 * @since 3.2.0
 *
 * @package LearnDash\Templates\Legacy\Quiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div style="display: none;" class="wpProQuiz_sending">
	<h4 class="wpProQuiz_header">RESULTADO</h4>
	<p>
		<div>
		<?php
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'quiz_complete_message',
					// translators: placeholder: Quiz.
					'message'      => sprintf( esc_html_x( '%s complete. Results are being recorded.', 'placeholder: Quiz', 'learndash' ), LearnDash_Custom_Label::get_label( 'quiz' ) ),
				)
			)
		);
		?>
		</div>
		<div>
			<dd class="course_progress">
				<div class="course_progress_blue sending_progress_bar" style="width: 0%;">
				</div>
			</dd>
		</div>
	</p>
</div>

<div style="display: none;" class="wpProQuiz_results">
	

<?php
if ( ! $quiz->isHideResultPoints() ) {
    ?>
    <p class="wpProQuiz_points">
    <?php
        // Generar el mensaje completo y capturar la salida
        $quiz_message = SFWD_LMS::get_template(
            'learndash_quiz_messages',
            array(
                'quiz_post_id' => $quiz->getID(),
                'context'      => 'quiz_have_reached_points_message',
                'message'      => sprintf( 
                    esc_html_x( 'You have reached %1$s of %2$s point(s), (%3$s)', 'placeholder: points earned, points total', 'learndash' ), 
                    '<span class="quiz-earned-points">0</span>', 
                    '<span class="quiz-total-points">0</span>', 
                    '<span class="quiz-percentage">0</span>' 
                ),
                'placeholders' => array( '0', '0', '0' ),
            )
        );

        echo wp_kses_post($quiz_message);
    ?>
    </p>

    <!-- Agregar el contenedor de progreso -->
    <div class="progress-container" style="width: 100%; max-width: 400px; background-color: #e0e0e0; border-radius: 10px; height: 25px; margin-top: 10px; position: relative;">
        <div class="progress-bar" style="width: 0%; height: 100%; background-color: red; border-radius: 10px; text-align: center; color: white; line-height: 25px;">
            <span class="progress-text" style="position: absolute; width: 100%; text-align: center;">0%</span>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        function updateProgressBar() {
            var percentageText = $('.quiz-percentage').text().trim();
            var percentage = parseFloat(percentageText);

            if (!isNaN(percentage)) {
                $('.progress-bar').css('width', percentage + '%');
                $('.progress-text').text(percentage + '%');
            }
        }

        // Verificar cambios dinámicos en el contenido
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if ($(mutation.target).is(':visible')) {
                    updateProgressBar();
                }
            });
        });

        var resultsDiv = document.querySelector('.wpProQuiz_results');
        if (resultsDiv) {
            observer.observe(resultsDiv, { 
                attributes: true, 
                attributeFilter: ['style'] 
            });
        }

        $(document).on('learndash-quiz-finished', function() {
            setTimeout(updateProgressBar, 1000);
        });

        updateProgressBar();
    });
    </script>
<?php
}

	if ( is_user_logged_in() ) {
		?>
		<p class="wpProQuiz_certificate" style="display: none ;"><?php echo LD_QuizPro::certificate_link( '', $quiz ); ?></p>
		<?php echo LD_QuizPro::certificate_details( $quiz ); ?>
		<?php
	}

	if ( $quiz->isShowAverageResult() ) {
		?>
		<div class="wpProQuiz_resultTable">
    <style>
        .quiz-results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .quiz-results-table td {
            padding: 15px 0;
        }

        .quiz-name {
            width: 250px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }

        .progress-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            height: 25px;
            background-color: #E0E0E0;
            padding: 0;
            border-radius: 10px;
            margin: auto;
        }

        .progress-bar {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background-color: #4c8bf5;
            border-radius: 10px;
            transition: width 0.5s ease-out;
        }

        .final-progress {
            background-color: #FFB6C1;
        }

        .percentage {
            width: 80px;
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            padding-left: 20px;
        }

        .quiz-percentage-box {
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>

    <table class="quiz-results-table">
        <tbody>
            <?php
            global $wpdb;

            // Dynamically get the current quiz ID
            $current_quiz_id = get_the_ID();

            // Get the course ID associated with the current quiz
            $course_id = learndash_get_course_id($current_quiz_id);

            // If LearnDash doesn't return a course ID, check manually
            if (!$course_id || $course_id == 0) {
                $course_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_first_quiz_id' AND meta_value = %d",
                    $current_quiz_id
                ));
            }

            // Get the First Quiz ID associated with the course
            $first_quiz_id = get_post_meta($course_id, '_first_quiz_id', true);
            $user_id = get_current_user_id();

            // Check if the current quiz is the first quiz
            $is_first_quiz = ($current_quiz_id == $first_quiz_id);

            // Get latest attempt for First Quiz
            $latest_attempt = $wpdb->get_row($wpdb->prepare(
                "SELECT activity_id, activity_completed FROM {$wpdb->prefix}learndash_user_activity 
                WHERE user_id = %d 
                AND post_id = %d 
                AND activity_type = 'quiz' 
                ORDER BY activity_completed DESC 
                LIMIT 1",
                $user_id,
                $first_quiz_id
            ));

            // Fetch the percentage score for First Quiz
            $first_quiz_score = null;
            $first_quiz_date = 'No Attempts';

            if ($latest_attempt) {
                $first_quiz_score = $wpdb->get_var($wpdb->prepare(
                    "SELECT activity_meta_value FROM {$wpdb->prefix}learndash_user_activity_meta 
                    WHERE activity_id = %d 
                    AND activity_meta_key = 'percentage'",
                    $latest_attempt->activity_id
                ));

                // Convert timestamp to readable date format
                if ($latest_attempt->activity_completed) {
                    $first_quiz_date = date_i18n('d F Y', $latest_attempt->activity_completed);
                }
            }

            $first_quiz_score_text = ($first_quiz_score !== null) ? $first_quiz_score . '%' : 'No Attempts';
            $progress_first = ($first_quiz_score !== null) ? $first_quiz_score . '%' : '0%';
            

            // Get final quiz percentage dynamically
            list($has_completed_final_quiz, $final_quiz_percentage) = get_latest_quiz_percentage($user_id, $current_quiz_id);
            $final_quiz_text = ($final_quiz_percentage !== null) ? $final_quiz_percentage . '%' : 'No Attempts';
            $progress_final = ($final_quiz_percentage !== null) ? $final_quiz_percentage . '%' : '0%';

            // Get the percentage from the quiz message for First Quiz
            $quiz_message = SFWD_LMS::get_template(
                'learndash_quiz_messages',
                array(
                    'quiz_post_id' => $current_quiz_id,
                    'context'      => 'quiz_have_reached_points_message',
                    'message'      => sprintf(
                        esc_html_x('You have reached %1$s of %2$s point(s), (%3$s)', 'placeholder: points earned, points total', 'learndash'),
                        '<span>0</span>',
                        '<span>0</span>',
                        '<span class="quiz-percentage">0</span>'
                    ),
                    'placeholders' => array('0', '0', '0'),
                )
            );

            // Extract the percentage dynamically
            $current_quiz_percentage = '0%';
            if (preg_match('/\((\d+(?:\.\d+)?)%\)/', $quiz_message, $matches)) {
                $current_quiz_percentage = $matches[1] . '%';
            }

            // If the current quiz is the First Quiz, display only its result
            if ($is_first_quiz) {
                ?>
                <div>HOLA</div>
                <?php
            } else {
            // If the current quiz is NOT the First Quiz, display both First and Final Quiz results
            ?>

<tr>
    <td colspan="3" style="background-color: #e0e0e0; padding: 20px; border-radius: 10px; text-align: center;">
        <div class="quiz-name" style="text-align: center; font-weight: bold;">
            Primera Evaluación: <?php echo esc_html($first_quiz_score_text); ?>
        </div>
        
        <div style="font-size: 16px; color: #666; margin-bottom: 8px;">
            <?php echo esc_html($first_quiz_date); ?>
        </div>

        <!-- Fix: Separate class for First Quiz Progress Bar -->
        <?php $progress_width_first = str_replace('%', '', $first_quiz_score_text); ?>

        <div class="progress-container" style="position: relative; width: 100%; max-width: 400px; height: 25px; background-color: #E0E0E0; border-radius: 10px; margin: auto;">
            <div class="progress-bar-first" style="width: <?php echo esc_attr($progress_width_first); ?>%; height: 100%; background-color: #4c8bf5; border-radius: 10px; transition: width 0.5s ease-out;">
            </div>
        </div>
    </td>
</tr>



            <?php
            }
            ?>
        </tbody>
    </table>
</div>
<script>
    jQuery(document).ready(function($) {
    function updateFinalScore() {
        var pointsText = $('.wpProQuiz_points').text();
        var matches = pointsText.match(/Has alcanzado (\d+) de (\d+) puntos,\s*\((\d+(?:\.\d+)?)%\)/);

        if (matches) {
            var percentage = parseFloat(matches[3]);

            // Update Final Quiz progress bar
            $('.final-progress').css('width', percentage + '%');
            $('.final-percentage').text(percentage + '%');

            // Update the red percentage box (for first quiz)
            $('.quiz-percentage-box').text(percentage + '%');
        }
    }

    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if ($(mutation.target).is(':visible')) {
                updateFinalScore();
            }
        });
    });

    var resultsDiv = document.querySelector('.wpProQuiz_results');
    if (resultsDiv) {
        observer.observe(resultsDiv, { 
            attributes: true, 
            attributeFilter: ['style'] 
        });
    }

    $(document).on('learndash-quiz-finished', function() {
        setTimeout(updateFinalScore, 1000);
    });
});
</script>



<?php	
} ?>

	<div class="wpProQuiz_catOverview" <?php $quiz_view->isDisplayNone( $quiz->isShowCategoryScore() ); ?>>
		<h4>
		<?php
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'learndash_categories_header',
					'message'      => esc_html__( 'Categories', 'learndash' ),
				)
			)
		);
		?>
		</h4>

		<div style="margin-top: 10px;">
			<ol>
			<?php
			foreach ( $quiz_view->category as $cat ) {
				if ( ! $cat->getCategoryId() ) {
					$cat->setCategoryName(
						wp_kses_post(
							SFWD_LMS::get_template(
								'learndash_quiz_messages',
								array(
									'quiz_post_id' => $quiz->getID(),
									'context'      => 'learndash_not_categorized_messages',
									'message'      => esc_html__( 'Not categorized', 'learndash' ),
								)
							)
						)
					);
				}
				?>
				<li data-category_id="<?php echo esc_attr( $cat->getCategoryId() ); ?>">
					<span class="wpProQuiz_catName"><?php echo esc_attr( $cat->getCategoryName() ); ?></span>
					<span class="wpProQuiz_catPercent">0%</span>
				</li>
				<?php
			}
			?>
			</ol>
		</div>
	</div>
	<div>
		<ul class="wpProQuiz_resultsList">
			<?php foreach ( $result['text'] as $resultText ) { ?>
				<li style="display: none;">
					<div>
						<?php echo do_shortcode( apply_filters( 'comment_text', $resultText, null, null ) ); ?>
						<?php // echo do_shortcode( apply_filters( 'the_content', $resultText, null, null ) ); ?>
					</div>
				</li>
			<?php } ?>
		</ul>
	</div>
	<?php
	if ( $quiz->isToplistActivated() ) {
		if ( $quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_NORMAL ) {
			echo do_shortcode( '[LDAdvQuiz_toplist ' . $quiz->getId() . ' q="true"]' );
		}

		$quiz_view->showAddToplist();
	}
	?>
	
    <div class="ld-quiz-actions" style="margin: 10px 0px; display: flex; flex-direction: column; align-items: center; gap: 20px;">
    <?php
        // Get the current quiz ID dynamically
        $current_quiz_id = get_the_ID();

        // Get the course ID associated with the current quiz
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_first_quiz_id' AND meta_value = %d",
            $current_quiz_id
        );
        $course_id = $wpdb->get_var($query);

        // Get the first quiz ID for the course
        $first_quiz_id = get_post_meta($course_id, '_first_quiz_id', true);

        // Find the WooCommerce product ID associated with the course
        $product_query = $wpdb->prepare(
            "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_related_course'"
        );
        $related_courses = $wpdb->get_results($product_query);

        $product_id = null;

        // Loop through related courses to find the product associated with the course ID
        if (!empty($related_courses)) {
            foreach ($related_courses as $related_course) {
                $meta_value = maybe_unserialize($related_course->meta_value); // Unserialize meta_value

                if (is_array($meta_value) && in_array($course_id, $meta_value)) {
                    $product_id = $related_course->post_id;
                    break;
                }
            }
        }

        // Generate the WooCommerce product URL
        $product_url = !empty($product_id) ? get_permalink($product_id) : null;

        // If the current quiz is the first quiz, display "Comprar Curso" and "Ver Ranking" buttons
        if ($current_quiz_id == $first_quiz_id) {
            echo '<div class="buttons" style="display: flex; gap: 15px;">';
            if ($product_url) {
                echo '<input class="wpProQuiz_button" type="button" name="comprarCurso" value="Comprar Curso" 
                    style="background-color: #000; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;" 
                    onclick="window.location.href=\'' . esc_url($product_url) . '\'" />';
            } echo '</div>';
        } else {
            // If not the first quiz, display only the "Ver Ranking" button
            echo '<div class="buttons" style="display: flex; gap: 15px;">';
            echo '<input class="wpProQuiz_button" type="button" name="verRanking" value="Ver Ranking" 
                style="background-color: #000; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;" />';
            echo '</div>';
        }
    ?>
    
</div>

</div>