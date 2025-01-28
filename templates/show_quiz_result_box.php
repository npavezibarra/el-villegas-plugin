<?php


// Debug section - add this near the top of show_quiz_result_box.php
if (!function_exists('learndash_get_course_id')) {
    require_once WP_PLUGIN_DIR . '/sfwd-lms/includes/course/ld-course-functions.php';
}

// Debug output
error_log('Debug Quiz Results:');
error_log('Current Quiz ID: ' . $quiz->getID());
$course_id = learndash_get_course_id();
error_log('Course ID: ' . $course_id);
error_log('User ID: ' . get_current_user_id());
$first_quiz_id = get_post_meta($course_id, '_first_quiz_id', true);
error_log('First Quiz ID: ' . $first_quiz_id);

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
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_have_reached_points_message',
						// translators: placeholder: points earned, points total.
						'message'      => sprintf( esc_html_x( 'You have reached %1$s of %2$s point(s), (%3$s)', 'placeholder: points earned, points total', 'learndash' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' ),
						'placeholders' => array( '0', '0', '0' ),
					)
				)
			);
		?>
		</p>
		<p class="wpProQuiz_graded_points" style="display: none;">
		<?php
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_earned_points_message',
						// translators: placeholder: points earned, points total, points percentage.
						'message'      => sprintf( esc_html_x( 'Earned Point(s): %1$s of %2$s, (%3$s)', 'placeholder: points earned, points total, points percentage', 'learndash' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' ),
						'placeholders' => array( '0', '0', '0' ),
					)
				)
			);
		?>
		<br />
		<?php
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_essay_possible_points_message',
						// translators: placeholder: number of essays, possible points.
						'message'      => sprintf( esc_html_x( '%1$s Essay(s) Pending (Possible Point(s): %2$s)', 'placeholder: number of essays, possible points', 'learndash' ), '<span>0</span>', '<span>0</span>' ),
						'placeholders' => array( '0', '0' ),
					)
				)
			);
		?>
		<br />
		</p>
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
		<table>
    <tbody>
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
}

.progress-container {
    position: relative;
    width: 100%;
    height: 25px;
    background-color: #E0E0E0;
    padding: 0;
}

.progress-bar {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    background-color: #FFB6C1;
    transition: width 0.5s ease-out;
}

.percentage {
    width: 80px;
    text-align: right;
    font-weight: bold;
    font-size: 16px;
    padding-left: 20px;
}
</style>

<table class="quiz-results-table">
    <tbody>
        <!-- Primera Evaluación -->
        <?php
        global $wpdb;
        $current_quiz_id = $quiz->getID();
        $course_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_first_quiz_id' AND meta_value = %d",
                7346
            )
        );
        $user_id = get_current_user_id();

        if (class_exists('LearnDashCourseAnalytics') && !empty($course_id)) {
            $analytics = new LearnDashCourseAnalytics($user_id, $course_id);
            $first_quiz_data = $analytics->get_first_quiz();

            if (isset($first_quiz_data['percentage']) && $first_quiz_data['percentage'] !== 'N/A') {
                ?>
                <tr>
                    <td class="quiz-name">Primera Evaluación</td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo esc_attr($first_quiz_data['percentage']); ?>%;"></div>
                        </div>
                    </td>
                    <td class="percentage"><?php echo esc_html($first_quiz_data['percentage']); ?>%</td>
                </tr>
                <?php
            }
        }
        ?>

        <!-- Evaluación Final -->
        <tr>
            <td class="quiz-name">Evaluación Final</td>
            <td>
                <div class="progress-container">
                    <div class="progress-bar final-progress" style="width: 0%;"></div>
                </div>
            </td>
            <td class="percentage final-percentage">0%</td>
        </tr>
    </tbody>
</table>

<script>
jQuery(document).ready(function($) {
    function updateFinalScore() {
        var pointsText = $('.wpProQuiz_points').text();
        var matches = pointsText.match(/Has alcanzado (\d+) de (\d+) puntos,\s*\((\d+(?:\.\d+)?)%\)/);
        
        if (matches) {
            var percentage = parseFloat(matches[3]);
            $('.final-progress').css('width', percentage + '%');
            $('.final-percentage').text(percentage + '%');
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
</div>
	<?php
	}
	?>

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