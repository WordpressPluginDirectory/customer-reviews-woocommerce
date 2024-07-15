<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CR_Reviews_Rating' ) ) {

	/**
	* Class for reviews rating shortcode
	*/
 class CR_Reviews_Rating {

		public function __construct() {
			$this->register_shortcode();
		}

		public function register_shortcode() {
			add_shortcode( 'cusrev_reviews_rating', array( $this, 'render_reviews_rating_shortcode' ) );
		}

		public function render_reviews_rating( $attributes ) {
			global $product;
			if( isset( $product ) ) {
				$cr_stars_style = "color:" . $attributes['color_stars'] . ";";
				$template = wc_locate_template(
					'cr-shortcode-rating.php',
					'customer-reviews-woocommerce',
					__DIR__ . '/../../templates/'
				);
				ob_start();
				include( $template );
				return ob_get_clean();
			} else {
				return self::not_a_product_page();
			}
		}

		public function render_reviews_rating_shortcode( $attributes ) {
			$shortcode_enabled = get_option( 'ivole_reviews_shortcode', 'no' );
			if ( 'yes' !== $shortcode_enabled ) {
				return;
			} else {
				// Convert shortcode attributes to block attributes
				$attributes = shortcode_atts( array(
					'color_stars' => '#FFBC00'
				), $attributes, 'cusrev_reviews_rating' );
				return $this->render_reviews_rating( $attributes );
			}
		}

		public static function not_a_product_page() {
			$output = '<div class="cr-reviews-rating-not-product">' .
				esc_html__( 'Error: [cusrev_reviews_rating] shortcode works only on WooCommerce single product pages', 'customer-reviews-woocommerce' ) .
				'</div>';
			return $output;
		}

	}

}
