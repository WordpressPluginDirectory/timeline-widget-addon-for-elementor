<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$widget_id   = $this->get_id();
$isRTL       = is_rtl();
$dir         = $isRTL ? 'rtl' : '';
$data        = isset( $settings['twae_list'] ) ? $settings['twae_list'] : array();
$sidesToShow = isset( $settings['twae_slides_to_show'] ) && ! empty( $settings['twae_slides_to_show'] ) ? $settings['twae_slides_to_show'] : 2;
$sidesHeight = isset( $settings['twae_slides_height'] ) ? $settings['twae_slides_height'] : 'no-height';
$autoplay    = isset( $settings['twae_autoplay'] ) ? $settings['twae_autoplay'] : 'false';

$this->add_render_attribute(
	'twae-wrapper',
	array(
		'id'    => 'twae-wrapper-' . esc_attr( $widget_id ),
		'class' => array( 'twae-wrapper', esc_attr( $timeline_layout_wrapper ) ),
	)
);

$this->add_render_attribute(
	'twae-slider-container',
	array(
		'id'                => 'twae-slider-container-' . esc_attr( $widget_id ),
		'data-dir'          => esc_attr( $dir ),
		'data-slidestoshow' => esc_attr( $sidesToShow ),
		'data-autoplay'     => esc_attr( $autoplay ),
		'data-auto-height'  => $sidesHeight === 'no-height' ? 'true' : 'false',
		'class'             => array( 'twae-slider-container', 'swiper-container' ),
	)
);


	$story_loop_obj = new Twae_Story_Loop( $settings );
		// Default Style
	$html = '<!-- ========= Timeline Widget  Addon For Elementor ' . TWAE_VERSION . ' ========= -->
<div ' . $this->get_render_attribute_string( 'twae-wrapper' ) . '>
<div class="twae-wrapper-inside">
 <div ' . $this->get_render_attribute_string( 'twae-slider-container' ) . '>
 <div  class="twae-slider-wrapper swiper-wrapper ' . esc_attr( $sidesHeight ) . '">';

if ( is_array( $data ) ) {
	foreach ( $data as $index => $content ) {

		$story_id             = $content['_id'];
		$icon_type            = isset( $content['twae_icon_type'] ) ? $content['twae_icon_type'] : 'icon';

		$this->add_render_attribute( 'twae_story_title', array( 'class' => esc_html( 'twae-title' ) ) );
		$this->add_render_attribute( 'twae_date_label', array( 'class' => esc_html( 'twae-label-big' ) ) );
		$this->add_render_attribute( 'twae_extra_label', array( 'class' => esc_html( 'twae-label-small' ) ) );
		$this->add_render_attribute( 'twae_description', array( 'class' => esc_html( 'twae-description' ) ) );

		$article_key = 'twae-article-' . esc_attr( $story_id );

		$this->add_render_attribute(
			$article_key,
			array(
				'id'    => esc_attr( $article_key ),
				'class' => array(
					esc_html( 'twae-repeater-item' ),
					esc_html( 'twae-story' ),
					esc_html( 'swiper-slide' ),
					'dot' === $icon_type ? esc_html( 'twae-story-no-icon' ) : esc_html( 'twae-story-no-icon' ),
				),
			)
		);

		$twae_repeater_attributes = array(
			$article_key       => $this->get_render_attribute_string( $article_key ),
			'twae_story_title' => $this->get_render_attribute_string( 'twae_story_title' ),
			'twae_date_label'  => $this->get_render_attribute_string( 'twae_date_label' ),
			'twae_extra_label' => $this->get_render_attribute_string( 'twae_extra_label' ),
			'twae_description' => $this->get_render_attribute_string( 'twae_description' ),
		);

		$repeater_key = array(
			'article_key'    => $article_key,
			'title_key'      => 'twae_story_title',
			'date_label_key' => 'twae_date_label',
			'sublabel_key'   => 'twae_extra_label',
			'desc_key'       => 'twae_description',
		);

		$html .= $story_loop_obj->twae_story_loop( $content, $repeater_key, $twae_repeater_attributes );
	}
}

$story_styles .= $story_loop_obj->twae_story_style();

$html .= '</div></div></div>';
$html .= ' <!-- Add Arrows -->
       <div class="twae-button-prev"><i class="fas fa-chevron-left"></i></div>
       <div class="twae-button-next"><i class="fas fa-chevron-right"></i></div>
       <div class="twae-h-line"></div>
       <div class="twae-line-fill"></div>
    </div>';

echo $html;




