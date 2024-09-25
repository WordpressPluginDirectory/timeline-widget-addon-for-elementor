<?php
if ( ! class_exists( 'Twae_Story_Loop' ) ) {
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}
	/**
	 * Class Twae_Story_Loop
	 *
	 * This class handles the story loop functionality.
	 */
	class Twae_Story_Loop {
		/**
		 * The story content.
		 *
		 * @var array
		 */
		private $story_data;

		/**
		 * The render attributes for the story loop repeater.
		 *
		 * @var array
		 */
		private $render_repeater_attr = array();

		/**
		 * The repeater key for the story loop.
		 *
		 * @var array
		 */
		private $repeater_key = array();

		/**
		 * The settings for the story loop.
		 *
		 * @var array
		 */
		private $settings = array();

		/**
		 * Constructor for Twae_Story_Loop class.
		 *
		 * @param array $settings The settings for the story loop.
		 */
		public function __construct( $settings ) {
			// Set the settings for the story loop.
			$this->settings = $settings;
		}

		/**
		 * Handles the story loop functionality.
		 *
		 * @param array $content               The content for the story loop.
		 * @param array $repeater_key          The repeater key for the story loop.
		 * @param array $render_repeater_attr  The render attributes for the story loop.
		 */
		public function twae_story_loop( $content, $repeater_key, $render_repeater_attr ) {
			// Sanitize inputs
			$content              = $content;
			$repeater_key         = array_map( 'sanitize_text_field', $repeater_key );
			$render_repeater_attr = array_map( 'sanitize_text_field', $render_repeater_attr );

			$html = '';
			// Story content array.
			$this->story_data = $content;
			// Story Layout.
			$layout = $this->settings['twae_layout'];
			// Stroy repeater key array,
			$this->repeater_key = $repeater_key;
			// Story Repeater attributes array.
			$this->render_repeater_attr = $render_repeater_attr;
			// Story article key.
			$article_key = $this->repeater_key['article_key'];
			// Story article attributes.
			$article_attr = isset( $this->render_repeater_attr[ $article_key ] ) ? $this->render_repeater_attr[ $article_key ] : '';
			// Story Year label setting.
			$show_year_label = isset( $content['twae_show_year_label'] ) ? $content['twae_show_year_label'] : '';
			// Story Connector Html.
			$connector_html = '<div class="twae-arrow"></div>';

			$html = '';

			// Display Timeline Year Label if set to 'yes'.
			if ( 'yes' === $show_year_label && 'horizontal' !== $layout ) {
				$html .= $this->twae_story_year_label();
			}

			// Display Timeline Story Loop Content start.
			$html .= '<!-- Start of Story Repeater Content -->';

			// Story article wrapper start.
			$html .= '<div ' . $article_attr . '>';

			// Display Timeline Year Label in horizontal layout if set to 'yes'.
			if ( 'yes' === $show_year_label && 'horizontal' === $layout ) {
				$html .= $this->twae_story_year_label();
			}

			$html .= $this->twae_story_label(); // Display Story date label.

			$html .= $this->twae_story_icon(); // Display Story Icon.

			// Display Story Arrow.
			$html .= '<!-- Story Arrow -->';
			$html .= $connector_html;

			// Start of Story content wrapper.
			$html .= '<!-- Start of Story Content -->';
			$html .= '<div class="twae-content">';
			if ( 'horizontal' === $layout ) {
				$html .= $this->twae_story_image(); // Display Story media image.
				$html .= $this->twae_story_title(); // Display Story title.
			} else {
				$html .= $this->twae_story_title(); // Display Story title.
				$html .= $this->twae_story_image(); // Display Story media image.
			}
			$html .= $this->twae_story_desc(); // Display Story description.

			// Story content wrapper end.
			$html .= '</div>';
			// Story article wrapper end.
			$html .= '</div>';
			// Timeline Story Loop Content end.

			return $html;
		}

		/**
		 * Returns the HTML for the story title.
		 *
		 * @return string The HTML for the story title.
		 */
		public function twae_story_title() {
			$html = '';
			// Story title repeater key.
			$title_key = $this->repeater_key['title_key'];
			// Story title repeater attributes.
			$title_attr = isset( $this->render_repeater_attr[ $title_key ] ) ? $this->render_repeater_attr[ $title_key ] : '';
			// Story title content.
			$title_content = $this->story_data['twae_story_title'];

			if ( ! empty( $title_content ) ) {
				$html .= '<!-- Story Title -->';
				$html .= '<div ' . $title_attr . '>' . wp_kses_post( $title_content ) . '</div>';
			}

			return $html;
		}

		/**
		 * Returns the HTML for the story description.
		 *
		 * @return string The HTML for the story description.
		 */
		public function twae_story_desc() {
			$html = '';
			// Story description key.
			$description_key = $this->repeater_key['desc_key'];
			// Story description repeater attributes.
			$description_attr = isset( $this->render_repeater_attr[ $description_key ] ) ? $this->render_repeater_attr[ $description_key ] : '';
			// Story description content.
			$desc_content = $this->story_data['twae_description'];
			if ( ! empty( $desc_content ) ) {
				$html .= '<!-- Story Description -->';
				$html .= '<div ' . $description_attr . '>' . wp_kses_post( $desc_content ) . '</div>';
			}
			return $html;
		}

		/**
		 * Return the HTML for the story image.
		 *
		 * @return string The HHTL for the story image.
		 */
		public function twae_story_image() {
			$html       = '';
			$story_data = $this->story_data;

			// Story image size.
			$image_size = $story_data['twae_thumbnail_size'];
			// Story title for image alt text.
			$timeline_story_title = $story_data['twae_story_title'];
			$image                = '';

			if ( isset( $story_data['twae_image'] ) && is_array( $story_data['twae_image'] ) ) {
				if ( $story_data['twae_image']['id'] != '' ) {
					if ( $image_size == 'custom' ) {
						// Image custom dimension.
						$thumbnail_custom_dimension = $story_data['twae_thumbnail_custom_dimension'];
						// Image custom size.
						$custom_size = array( $thumbnail_custom_dimension['width'], $thumbnail_custom_dimension['height'] );
						// Story media image.
						$image .= wp_get_attachment_image( esc_attr( $story_data['twae_image']['id'] ), esc_attr( $custom_size ) );
					} else {
						// Story media image.
						$image = wp_get_attachment_image( esc_attr( $story_data['twae_image']['id'] ), esc_attr( $image_size ) );
					}
				} elseif ( $story_data['twae_image']['url'] != '' ) {
					// Story media image.
					$image .= '<img src="' . esc_url( $story_data['twae_image']['url'] ) . '" alt="' . esc_attr( $timeline_story_title ) . '">';
				}

				if ( $story_data['twae_image']['url'] != '' && $story_data['twae_media'] == 'image' ) {
					$html .= '<!-- Story Image -->';
					$html .= '<div class="twae-media ' . esc_attr( $image_size ) . '">' . $image . '</div>';
				}
			}

			return $html;
		}

		/**
		 * Returns the HTML for the story icon.
		 *
		 * @return string The HTML for the story icon.
		 */
		public function twae_story_icon() {
			$html = '';
			// story icon type.
			$icon_type = isset( $this->story_data['twae_icon_type'] ) ? $this->story_data['twae_icon_type'] : 'icon';
			if ( $icon_type == 'dot' ) {
				$html .= '<!-- Story Icon Dot -->';
				$html .= '<div class="twae-icondot"></div>';
			} else {
				$html .= '<!-- Story Icon -->';
				$html .= '<div class="twae-icon">';
				if ( isset( $this->story_data['twae_story_icon'] ) ) {
					$html .= $this->twae_get_icons( $this->story_data['twae_story_icon'] );
				} else {
					$html .= '<i aria-hidden="true" class="far fa-clock"></i>';
				}
				$html .= '</div>';
			}

			return $html;
		}

		/**
		 * Returns the render icon data reterive from elementor icons manager.
		 *
		 * @return string The HTML for the story icon.
		 */
		public function twae_get_icons( $icon_setting ) {
			$html = '';
			if ( isset( $icon_setting ) ) {
				ob_start();
				// Reterive icon html from \Elementor\Icons_Manager class.
				\Elementor\Icons_Manager::render_icon( $icon_setting, array( 'aria-hidden' => 'true' ) );
				$render_icon = ob_get_contents();
				ob_end_clean();
				$html .= $render_icon;
			} else {
				$html .= '<i class="far fa-clock"></i>';
			}

			return $html;
		}

		/**
		 * Returns the HTML for the story label.
		 *
		 * @return string The HTML for the story label.
		 */
		public function twae_story_label() {
			$html       = '';
			$story_data = $this->story_data;
			// Story primary label key.
			$date_label_key = $this->repeater_key['date_label_key'];
			// Story primary label repeater attribute.
			$date_label_attr = isset( $this->render_repeater_attr[ $date_label_key ] ) ? $this->render_repeater_attr[ $date_label_key ] : '';

			if ( ! empty( $story_data['twae_date_label'] ) || ! empty( $story_data['twae_extra_label'] ) ) {
				$html .= '<!-- Story Label -->';
				$html .= '<div class="twae-labels">';
				if ( ! empty( $story_data['twae_date_label'] ) ) {
					$html .= '<div ' . $date_label_attr . '>' . wp_kses_post( $story_data['twae_date_label'] ) . '</div>';
				}

				// Sub Label.
				if ( ! empty( $story_data['twae_extra_label'] ) ) {
					// Story secondary label key.
					$sub_label_key = $this->repeater_key['sublabel_key'];
					// Story secondary label repeater attribute.
					$sub_label_attr = isset( $this->render_repeater_attr[ $sub_label_key ] ) ? $this->render_repeater_attr[ $sub_label_key ] : '';
					$html          .= '<div ' . $sub_label_attr . '>' . wp_kses_post( $story_data['twae_extra_label'] ) . '</div>';
				}
				$html .= '</div>';
			}

			return $html;
		}

		/**
		 * Returns the HTML for the story year label.
		 *
		 * @return string The HTML for the story year label.
		 */
		public function twae_story_year_label() {
			$html = '';
			// Story Year label.
			$timeline_year = $this->story_data['twae_year'];
			// Story id.
			$story_id = $this->story_data['_id'];
			if ( ! empty( $timeline_year ) ) {
				$html .= '<!-- Story Year Label -->';
				$html .= '<div class="twae-year twae-year-container story-year-' . esc_attr( $story_id ) . '">
					<div class="twae-year-label twae-year-text">' . wp_kses_post( $timeline_year ) . '</div>
				</div>';
			};
			return $html;
		}

		/**
		 * Return the custom story style
		 *
		 * @return string story style css variable
		 */
		public function twae_story_style() {
			$layout               = isset( $this->settings['twae_layout'] ) ? $this->settings['twae_layout'] : 'centered';
			$tablet_css_wrapper   = '@media (max-width: 1024px){ .twae-wrapper{';
			$mobile_css_wrapper   = '@media (max-width: 767px){ .twae-wrapper{';
			$tablet_css           = '';
			$mobile_css           = '';
			$style                = '';
			$defalt_year_box_size = 'horizontal' === $layout ? '75px' : '80px';
			if ( ! isset( $this->settings['twae_year_size_tablet'] ) ) {
				$tablet_css .= '--tw-ybx-size: ' . $defalt_year_box_size . ' !important;';
			};
			if ( ! isset( $this->settings['twae_year_size_mobile'] ) ) {
				$mobile_css .= '--tw-ybx-size: ' . $defalt_year_box_size . ' !important';
			}
			$tablet_css_wrapper .= $tablet_css . '}}';
			$mobile_css_wrapper .= $mobile_css . '}}';

			if ( ! empty( $tablet_css ) ) {
				$style .= $tablet_css_wrapper;
			}
			if ( ! empty( $mobile_css ) ) {
				$style .= $mobile_css_wrapper;
			}

			return $style;
		}
	}
}


