<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$widget_id = $this->get_id();
$countItem = 1;
$data      = $settings['twae_list'];
$this->add_render_attribute(
	'twae-wrapper',
	array(
		'id'    => 'twae-wrapper-' . esc_attr( $widget_id ),
		'class' => array( 'twae-vertical', 'twae-wrapper ', esc_attr( $timeline_layout_wrapper ) ),
	)
);

$this->add_render_attribute(
	'twae-timeline',
	array(
		'id'    => 'twea-timeline-' . esc_attr( $widget_id ),
		'class' => array( 'twae-timeline' ),
	)
);

$html = '<!-- ========= Timeline Widget Addon For Elementor ' . TWAE_VERSION . ' ========= -->
<div ' . $this->get_render_attribute_string( 'twae-wrapper' ) . '>   
    <div class="twae-start"></div>    
    <div ' . $this->get_render_attribute_string( 'twae-timeline' ) . ' >';

$story_loop_obj = new Twae_Story_Loop( $settings );
if ( is_array( $data ) ) {
	foreach ( $data as $index => $content ) {
		$story_alignment = 'twae-story-right';
		if ( $layout == 'centered' ) {
			if ( $countItem % 2 == 0 ) {
				$story_alignment = 'twae-story-left';
			}
		}

		$story_id = $content['_id'];

		$icon_type = isset( $content['twae_icon_type'] ) ? $content['twae_icon_type'] : 'icon';

		$title_key = $this->get_repeater_setting_key( 'twae_story_title', 'twae_list', $index );

		$date_label_key  = $this->get_repeater_setting_key( 'twae_date_label', 'twae_list', $index );
		$sub_label_key   = $this->get_repeater_setting_key( 'twae_extra_label', 'twae_list', $index );
		$description_key = $this->get_repeater_setting_key( 'twae_description', 'twae_list', $index );

		$this->add_inline_editing_attributes( $title_key, 'none' );
		$this->add_inline_editing_attributes( $date_label_key, 'none' );
		$this->add_inline_editing_attributes( $sub_label_key, 'none' );
		$this->add_inline_editing_attributes( $description_key, 'advanced' );

		$this->add_render_attribute( $title_key, array( 'class' => esc_html( 'twae-title' ) ) );
		$this->add_render_attribute( $date_label_key, array( 'class' => esc_html( 'twae-label-big' ) ) );
		$this->add_render_attribute( $sub_label_key, array( 'class' => esc_html( 'twae-label-small' ) ) );
		$this->add_render_attribute( $description_key, array( 'class' => esc_html( 'twae-description' ) ) );

		$article_key = 'twae-article-' . esc_attr( $story_id );

		$this->add_render_attribute(
			$article_key,
			array(
				'id'    => 'story-' . esc_attr( $story_id ),
				'class' => array(
					esc_html( 'twae-story' ),
					esc_html( 'twae-repeater-item' ),
					esc_attr( $story_alignment ),
					'dot' === $icon_type ? esc_html( 'twae-story-no-icon' ) : esc_html( 'twae-story-no-icon' ),
				),
			)
		);

		$twae_repeater_attributes = array(
			$article_key     => $this->get_render_attribute_string( $article_key ),
			$title_key       => $this->get_render_attribute_string( $title_key ),
			$date_label_key  => $this->get_render_attribute_string( $date_label_key ),
			$sub_label_key   => $this->get_render_attribute_string( $sub_label_key ),
			$description_key => $this->get_render_attribute_string( $description_key ),
		);

		$repeater_key = array(
			'article_key'    => $article_key,
			'title_key'      => $title_key,
			'date_label_key' => $date_label_key,
			'sublabel_key'   => $sub_label_key,
			'desc_key'       => $description_key,
		);

		$html .= $story_loop_obj->twae_story_loop( $content, $repeater_key, $twae_repeater_attributes );

		$countItem = $countItem + 1;
	}
}

$story_styles .= $story_loop_obj->twae_story_style();

$html .= '</div>
    <div class="twae-end"></div>
    </div>';

echo $html;
