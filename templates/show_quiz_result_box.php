<?php
/**
 * Displays Quiz Result Box (Legacy Template).
 *
 * @since 3.2.0
 * @version 4.17.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<style>
.table-quiz-name {
    text-align: left !important;
}
</style>

<!-- (A) LearnDash default sending container -->
<div style="display: none;" class="wpProQuiz_sending">
    <h4 class="wpProQuiz_header"><?php esc_html_e('Results', 'learndash'); ?></h4>
    <p>
        <div>
            <?php
            echo wp_kses_post(
                SFWD_LMS::get_template(
                    'learndash_quiz_messages',
                    array(
                        'quiz_post_id' => $quiz->getID(),
                        'context'      => 'quiz_complete_message',
                        'message'      => sprintf(
                            esc_html_x('%s complete. Results are being recorded.', 'placeholder: Quiz', 'learndash'),
                            LearnDash_Custom_Label::get_label('quiz')
                        ),
                    )
                )
            );
            ?>
        </div>
        <div>
            <dd class="course_progress">
                <div class="course_progress_blue sending_progress_bar" style="width: 0%;"></div>
            </dd>
        </div>
    </p>
</div>

<!-- (B) LearnDash default results container -->
<div style="display: none;" class="wpProQuiz_results">
    <h4 class="wpProQuiz_header"><?php esc_html_e('Results', 'learndash'); ?></h4>

    <?php
    if (!$quiz->isHideResultCorrectQuestion()) :

        if (!$quiz->isHideResultQuizTime()) {
            ?>
            <p class="wpProQuiz_quiz_time">
                <?php
                echo wp_kses_post(
                    SFWD_LMS::get_template(
                        'learndash_quiz_messages',
                        array(
                            'quiz_post_id' => $quiz->getID(),
                            'context'      => 'quiz_your_time_message',
                            'message'      => sprintf(
                                esc_html_x('Your time: %s', 'placeholder: quiz time.', 'learndash'),
                                '<span></span>'
                            ),
                        )
                    )
                );
                ?>
            </p>
            <?php
        }
        ?>
        <div style="display: none;">
            <span class="wpProQuiz_correct_answer">0</span>
            <span class="total-questions"><?php echo intval($question_count); ?></span>
        </div>

        <?php
        global $post;
        $quiz_id = isset($post->ID) ? $post->ID : 0;

        if (!class_exists('QuizAnalytics')) {
            require_once plugin_dir_path(__FILE__) . 'classes/class-quiz-analytics.php';
        }

        if (class_exists('QuizAnalytics')) {
            $quiz_checker = new QuizAnalytics($quiz_id);
            $is_first_quiz = $quiz_checker->isFirstQuiz();

            // (1) Always show the current quiz container
            ?>
            <div class="quiz-results-container" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="height: 50px;">
            <td style="width: 40%; padding: 10px; vertical-align: middle;" class="table-quiz-name">
                <div class="quiz-name" style="font-weight: bold; font-size: 16px;">
                    <?php echo esc_html(get_the_title()); ?>
                </div>
                <div style="color: #666; font-size: 14px;">
                    <?php echo esc_html(date('F j')); ?>
                </div>
            </td>
            <td style="width: 40%; padding: 10px; vertical-align: middle;">
                <div class="progress-bar-container" style="background: #e9ecef; border-radius: 4px; height: 24px; overflow: hidden;">
                    <div id="quiz-progress-bar" style="width: 0%; height: 100%; background: #ffc0cb; transition: width 0.5s ease;"></div>
                </div>
            </td>
            <td style="width: 20%; padding: 10px; text-align: right; vertical-align: middle;">
                <span id="quiz-percentage" style="font-size: 24px; font-weight: bold;">0%</span>
            </td>
        </tr>
    </table>
</div>

<?php
// Obtener el ID del curso asociado al quiz actual
$quiz_id = isset($post->ID) ? $post->ID : 0;
$course_id = learndash_get_course_id($quiz_id); // Obtiene el Course ID del quiz

// Obtener el Product ID asociado al curso
$product_id = get_post_meta($course_id, '_linked_woocommerce_product', true);
if (empty($product_id)) {
    $args = array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => '_related_course',
                'value' => $course_id,
                'compare' => 'LIKE',
            ),
        ),
        'posts_per_page' => 1,
    );
    $products = get_posts($args);
    if (!empty($products)) {
        $product_id = $products[0]->ID;
    }
}

// Obtener la URL del producto en WooCommerce
$product_url = get_permalink($product_id);

// Verificar si el usuario ya tiene acceso al curso (si ha comprado el curso)
$user_id = get_current_user_id();
$has_access = sfwd_lms_has_access($course_id, $user_id);

// Generar el texto y la URL del botón dependiendo de si el usuario ha comprado el curso
if ($has_access) {
    // Si el usuario tiene acceso al curso (lo ha comprado), mostramos el enlace "IR AL CURSO"
    $button_text = 'IR AL CURSO';
    $button_url = get_permalink($course_id); // URL del curso
} else {
    // Si el usuario no tiene acceso al curso, mostramos el enlace "COMPRAR"
    $button_text = 'COMPRAR';
    $button_url = $product_url; // URL del producto en WooCommerce
}
?>

<!-- Botón "COMPRAR" o "IR AL CURSO" -->
<button onclick="window.location.href='<?php echo esc_url($button_url); ?>'" 
        style="background-color: #4c8bf5; color: white; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer;">
    <?php echo esc_html($button_text); ?>
</button>

            <?php
            // (2) Show the first quiz container only if this is NOT the first quiz
            if (!$is_first_quiz) {
                $first_quiz_id = $quiz_checker->getFirstQuiz();
                $first_quiz_name = ($first_quiz_id !== "Doesn't have") 
                    ? get_the_title($first_quiz_id) 
                    : "Doesn't exist";

                $perf = $quiz_checker->getFirstQuizPerformance();
                $rawPct = $perf['percentage'];
                $pctNumeric = (is_numeric($rawPct)) ? round(floatval($rawPct)) : 0; // Round to integer
                $first_quiz_percentage = $pctNumeric . '%';

                $first_quiz_date = (!empty($perf['date']) && strtotime($perf['date']) !== false)
                    ? date('F j', strtotime($perf['date']))
                    : "No Attempts";
                ?>
                <div class="quiz-results-container" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="height: 50px;">
                            <td style="width: 20%; padding: 10px; vertical-align: middle;" class="table-quiz-name">
                                <div class="quiz-name" style="font-weight: bold; font-size: 16px;">
                                    <?php echo esc_html($first_quiz_name); ?>
                                </div>
                                <div style="color: #666; font-size: 14px;">
                                    <?php echo esc_html($first_quiz_date); ?>
                                </div>
                            </td>
                            <td style="width: 60%; padding: 10px; vertical-align: middle;">
                                <div class="progress-bar-container" style="background: #e9ecef; border-radius: 4px; height: 24px; overflow: hidden;">
                                    <div id="first-quiz-progress-bar" style="width: 0%; height: 100%; background: #ffc0cb; transition: width 0.5s ease;"></div>
                                </div>
                            </td>
                            <td style="width: 20%; padding: 10px; text-align: right; vertical-align: middle;">
                                <span id="first-quiz-percentage" style="font-size: 24px; font-weight: bold;">
                                    <?php echo esc_html($first_quiz_percentage); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var pct = <?php echo json_encode(str_replace('%', '', $first_quiz_percentage)); ?>;
                    document.getElementById("first-quiz-progress-bar").style.width = pct + "%";
                });
                </script>
            <?php
            }
        }
        ?>

        <script>
        jQuery(document).ready(function($) {
            $(document).on('learndash-quiz-finished', function() {
                var correctAnswers = parseInt($('.wpProQuiz_correct_answer').text(), 10);
                var totalQuestions = parseInt($('.total-questions').text(), 10);

                if (!isNaN(correctAnswers) && totalQuestions > 0) {
                    var percentage = Math.round((correctAnswers / totalQuestions) * 100);
                    $('#quiz-percentage').text(percentage + '%');
                    $('#quiz-progress-bar').css('width', percentage + '%');
                }
            });
        });
        </script>
    <?php endif; ?>

	
	<p class="wpProQuiz_time_limit_expired" style="display: none;">
		<?php
		echo wp_kses_post(
			SFWD_LMS::get_template(
				'learndash_quiz_messages',
				array(
					'quiz_post_id' => $quiz->getID(),
					'context'      => 'quiz_time_has_elapsed_message',
					'message'      => esc_html__( 'Time has elapsed', 'learndash' ),
				)
			)
		);
		?>
	</p>

	<?php
	// Points-based result block.
	if ( ! $quiz->isHideResultPoints() ) {
		?>
		
		<p class="wpProQuiz_graded_points" style="display: none;">
			<?php
			echo wp_kses_post(
				SFWD_LMS::get_template(
					'learndash_quiz_messages',
					array(
						'quiz_post_id' => $quiz->getID(),
						'context'      => 'quiz_earned_points_message',
						// translators: placeholder: points earned, points total, points percentage.
						'message'      => sprintf(
							esc_html_x( 'Earned Point(s): %1$s of %2$s, (%3$s)', 'placeholder: points earned, points total, points percentage', 'learndash' ),
							'<span>0</span>',
							'<span>0</span>',
							'<span>0</span>'
						),
						'placeholders' => array( '0', '0', '0' ),
					)
				)
			);
			?>
		</p>
		<?php
	}

	if ( is_user_logged_in() ) {
		?>
		<p class="wpProQuiz_certificate" style="display: none;"><?php echo LD_QuizPro::certificate_link( '', $quiz ); ?></p>
		<?php echo LD_QuizPro::certificate_details( $quiz ); ?>
		<?php
	}

	if ( $quiz->isShowAverageResult() ) {
		?>
		<div class="wpProQuiz_resultTable">
			<table>
				<tbody>
					<tr>
						<td class="wpProQuiz_resultName">
							<?php
							echo wp_kses_post(
								SFWD_LMS::get_template(
									'learndash_quiz_messages',
									array(
										'quiz_post_id' => $quiz->getID(),
										'context'      => 'quiz_average_score_message',
										'message'      => esc_html__( 'Average score', 'learndash' ),
									)
								)
							);
							?>
						</td>
						<td class="wpProQuiz_resultValue wpProQuiz_resultValue_AvgScore">
							<div class="progress-meter" style="background-color: #6CA54C;">&nbsp;</div>
							<span class="progress-number">&nbsp;</span>
						</td>
					</tr>
					<tr>
						<td class="wpProQuiz_resultName">
							<?php
							echo wp_kses_post(
								SFWD_LMS::get_template(
									'learndash_quiz_messages',
									array(
										'quiz_post_id' => $quiz->getID(),
										'context'      => 'quiz_your_score_message',
										'message'      => esc_html__( 'Your score', 'learndash' ),
									)
								)
							);
							?>
						</td>
						<td class="wpProQuiz_resultValue wpProQuiz_resultValue_YourScore">
							<div class="progress-meter">&nbsp;</div>
							<span class="progress-number">&nbsp;</span>
						</td>
					</tr>
				</tbody>
			</table>
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
			<?php
			foreach ( $result['text'] as $resultText ) {
				?>
				<li style="display: none;">
					<div>
						<?php
						if ( $quiz->is_result_message_enabled() ) {
							echo do_shortcode( apply_filters( 'comment_text', $resultText, null, null ) );
						}
						?>
					</div>
				</li>
				<?php
			}
			?>
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
	<div class="ld-quiz-actions" style="margin: 10px 0px;">
		<?php
		$show_quiz_continue_buttom_on_fail = apply_filters( 'show_quiz_continue_buttom_on_fail', false, learndash_get_quiz_id_by_pro_quiz_id( $quiz->getId() ) );
		?>
		<div class='quiz_continue_link <?php if ( true === $show_quiz_continue_buttom_on_fail ) { echo ' show_quiz_continue_buttom_on_fail'; } ?>'>
		</div>
		<?php if ( ! $quiz->isBtnRestartQuizHidden() ) { ?>
			<input class="wpProQuiz_button wpProQuiz_button_restartQuiz" type="button" name="restartQuiz"
				value="<?php echo wp_kses_post(
					SFWD_LMS::get_template(
						'learndash_quiz_messages',
						array(
							'quiz_post_id' => $quiz->getID(),
							'context'      => 'quiz_restart_button_label',
							'message'      => sprintf(
								esc_html_x( 'Restart %s', 'Restart Quiz Button Label', 'learndash' ),
								LearnDash_Custom_Label::get_label( 'quiz' )
							),
						)
					)
				); ?>"/>
		<?php } ?>
		<?php if ( ! $quiz->isBtnViewQuestionHidden() ) { ?>
			<input class="wpProQuiz_button wpProQuiz_button_reShowQuestion" type="button" name="reShowQuestion"
				value="<?php echo wp_kses_post(
					SFWD_LMS::get_template(
						'learndash_quiz_messages',
						array(
							'quiz_post_id' => $quiz->getID(),
							'context'      => 'quiz_view_questions_button_label',
							'message'      => sprintf(
								esc_html_x( 'View %s', 'View Questions Button Label', 'learndash' ),
								LearnDash_Custom_Label::get_label( 'questions' )
							),
						)
					)
				); ?>"/>
		<?php } ?>
		<?php if ( $quiz->isToplistActivated() && $quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_BUTTON ) { ?>
			<input class="wpProQuiz_button" type="button" name="showToplist"
				value="<?php echo wp_kses_post(
					SFWD_LMS::get_template(
						'learndash_quiz_messages',
						array(
							'quiz_post_id' => $quiz->getID(),
							'context'      => 'quiz_show_leaderboard_button_label',
							'message'      => esc_html__( 'Show leaderboard', 'learndash' ),
						)
					)
				); ?>"/>
		<?php } ?>
	</div>
</div>