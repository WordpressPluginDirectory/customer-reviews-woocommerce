<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CR_Utils' ) ) :

	class CR_Utils {

		public static function cr_locate_template( $template_name, $template_path, $default_path ) {
			$template = locate_template(
				array(
					trailingslashit( $template_path ) . $template_name,
					$template_name,
				)
			);
			if ( ! $template ) {
				$template = $default_path . $template_name;
			}
			return apply_filters( 'cr_locate_template', $template, $template_name, $template_path, $default_path );
		}

	}

endif;
