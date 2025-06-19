<?php

defined('ABSPATH') or die();


/* Polylang : Traducción de plantillas */

add_filter('elementor/theme/get_location_templates/template_id', function($post_id) {

	if (function_exists('pll_get_post')) {

		$translation_post_id = pll_get_post($post_id);

		if (null === $translation_post_id) {
			return $post_id;
		} else if (false === $translation_post_id) {
			return $post_id;
		} else if ($translation_post_id > 0) {
			return $translation_post_id;
		}

	}

	return $post_id;

});