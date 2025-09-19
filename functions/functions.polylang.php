<?php

defined('ABSPATH') or die();


/* Traducción de plantillas
**
** Si Polylang está activo, traduce las plantillas de Elementor
*/

add_filter('elementor/theme/get_location_templates/template_id', function ($post_id) {

	if (!function_exists('pll_get_post')) {
		return $post_id;
	}

	$translated = pll_get_post($post_id);

	return (is_int($translated) && $translated > 0) ? $translated : $post_id;

});