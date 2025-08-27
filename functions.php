<?php

require_once get_template_directory().'/inc/scssphp/scss.inc.php';

use ScssPhp\ScssPhp\Compiler;


// Declaramos la versión actual del tema

define('THEME_VERSION', 2.0);


// Añadimos soporte para miniaturas

add_theme_support('post-thumbnails');


// Declaramos los idiomas

add_action('after_setup_theme', function() {

	load_theme_textdomain('orenes', get_stylesheet_directory().'/languages');

});


// Eliminamos la redimensión automática para imágenes muy grandes

add_filter('big_image_size_threshold', '__return_false');


// Creamos un menú principal por defecto

register_nav_menus(
	array(
		'primary' => 'Menú principal',
	)
);


// Directivas CSP de seguridad en el archivo .htaccess

// Directivas CSP de seguridad en el archivo .htaccess

add_action('admin_init', function() {

	if (!is_multisite()) {

		require_once(ABSPATH.'wp-admin/includes/file.php');

		$scp = array(
			'default-src' => array(
				'none'
			),
			'script-src' => array(
				'self',
				'unsafe-inline',
				'unsafe-eval',
				trim(site_url(), '/'),
				'https://*.covermanager.com',
				'https://www.googletagmanager.com',
				'https://www.google.com',
				'https://www.gstatic.com',
				'https://cdnjs.cloudflare.com',
				'https://*.youtube.com',
				'https://pagead2.googlesyndication.com',
				'https://googleads.g.doubleclick.net',
				'https://*.googleadservices.com',
				'https://adservice.google.com',
				'https://*.doubleclick.net',
				'https://*.facebook.net',
				'https://www.recaptcha.net',
				'https://www.gstatic.com/recaptcha/',
				'https://*.mapbox.com',
				'https://*.intercom.io',
				'https://*.intercomcdn.com'
			),
			'connect-src' => array(
				'self',
				trim(site_url(), '/'),
				'https://*.covermanager.com',
				'https://www.googletagmanager.com',
				'https://google.com',
				'https://www.google.com',
				'https://www.gstatic.com',
				'https://cdnjs.cloudflare.com',
				'https://*.youtube.com',
				'https://pagead2.googlesyndication.com',
				'https://googleads.g.doubleclick.net',
				'https://adservice.google.com',
				'https://*.doubleclick.net',
				'https://*.facebook.net',
				'https://*.facebook.com',
				'https://*.google-analytics.com',
				'https://*.google.es',
				'https://*.wpo365.com',
				'https://www.recaptcha.net',
				'https://*.mapbox.com',
				'https://*.intercom.io',
				'wss://*.intercom.io'
			),
			'img-src' => array(
				'self',
				'data:',
				'blob:',
				'https://secure.gravatar.com',
				'https://*.w.org',
				'https://library.elementor.com',
				'https://pagead2.googlesyndication.com',
				'https://googleads.g.doubleclick.net',
				'https://*.googleadservices.com',
				'https://*.gtranslate.net',
				'https://*.doubleclick.net',
				'https://*.wpo365.com',
				'https://*.facebook.com',
				'https://*.google.com',
				'https://*.google.es',
				'https://*.ytimg.com'
			),
			'style-src' => array(
				'self',
				'unsafe-inline',
				'https://*.mapbox.com'
			),
			'worker-src' => array(
				'self',
				'blob:'
			),
			'font-src' => array(
				'self',
				'data:',
				'https://*.sharepointonline.com',
				'https://*.akamaihd.net',
				'https://*.office.net',
				'https://*.gstatic.com',
				'https://*.intercomcdn.com'
			),
			'media-src' => array(
				'self',
				'blob:',
				'data:',
				trim(site_url(), '/'),
				'https://*.archive.org'
			),
			'frame-src' => array(
				'self',
				'blob:',
				trim(site_url(), '/'),
				'https://www.google.com',
				'https://*.youtube.com',
				'https://*.youtube-nocookie.com',
				'https://*.covermanager.com',
				'https://tpc.googlesyndication.com',
				'https://*.doubleclick.net',
				'https://*.googletagmanager.com',
				'https://www.google.com/recaptcha/',
				'https://www.recaptcha.net',
				'https://archive.org'
			),
			'style-src-elem' => array(
				'self',
				'unsafe-inline',
				trim(site_url(), '/'),
				'https://*.mapbox.com',
				'https://*.googleapis.com'
			)
		);

		$directives = array();

		foreach ($scp as $directive => $sources) {

			$mapped = array_map(function($src) {

				switch ($src) {
				
					case 'self':
					case 'unsafe-inline':
					case 'unsafe-eval':
					case 'none':
					
						return '\''.$src.'\'';

						break;
				
					default:

						return $src;

						break;


				}

			}, $sources);

			$mapped = array_unique($mapped);

			$directives[] = $directive.' '.implode(' ',$mapped);

		}

		$content = array(
			'<IfModule mod_rewrite.c>',
			'RewriteEngine On',
			'RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]',
			'RewriteBase /',
			'RewriteRule ^index\.php$ - [L]',
			'RewriteCond %{REQUEST_FILENAME} !-f',
			'RewriteCond %{REQUEST_FILENAME} !-d',
			'RewriteRule . /index.php [L]',
			'</IfModule>',
			'# CSP Security headers rules',
			'<IfModule mod_headers.c>',
			'Header set Content-Security-Policy "'.implode('; ', $directives).'"',
			'</IfModule>',
			'# End of CSP Security headers rules'
		);

		insert_with_markers(get_home_path().'.htaccess', 'WordPress', $content);

	}

});


// Función para cargar scripts PHP recursivamente

function directory_include($dir, $recursively = true) {

	if (is_dir($dir)) {

		$relative = str_replace(dirname(__FILE__).'/', '', $dir);

		$scan = scandir($dir);

		unset($scan[0], $scan[1]);

		$scan = multi_sort_array($scan, 'first');

		foreach($scan as $file) {

			if (is_dir($dir.'/'.$file) && $recursively == true) {

				directory_include($dir.'/'.$file);

			} else if (!is_dir($dir.'/'.$file)) {

				require($relative.'/'.$file);

			}

		}

	}

}


// Función para ordenar arrays multidimensionales en función de los valores

function multi_sort_array(&$array, $value) {

	if (count($array) > 0 && strpos(implode('', $array), $value) !== false) {

		$key = array_keys(preg_grep('/('.$value.')/i', $array))[0];
		
		$value = $array[$key];

		if($key) {
			unset($array[$key]);
		}

		array_unshift($array, $value);

	}

	return $array;

}


// Añadimos soporte para SVG

add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {

	global $wp_version;

	if ($wp_version !== '4.7.1') {
		return $data;
	}

	$filetype = wp_check_filetype($filename, $mimes);

	return array(
		'ext' => $filetype['ext'],
		'type' => $filetype['type'],
		'proper_filename' => $data['proper_filename']
	);

}, 10, 4);

add_filter('upload_mimes', function($mimes) {

	$mimes['svg'] = 'image/svg+xml';

	return $mimes;

});


// Cargamos los scripts y hojas de estilo
	
add_action('wp_enqueue_scripts', function() {

	wp_enqueue_style('style', get_template_directory_uri().'/style.css', array(), uniqid());

	wp_enqueue_style('fonts', get_stylesheet_directory_uri().'/fonts/css.php', array(), uniqid());

	foreach (array_diff(scandir(get_stylesheet_directory().'/scss'), array('.', '..')) as $file) {

		$ext = end(explode('.', $file));

		if ($ext != 'scss') {
			continue;
		}

		$name = pathinfo($file, PATHINFO_FILENAME);

		$scss = get_template_directory().'/scss/'.$name.'.scss';
		$css = get_template_directory().'/css/'.$name.'.css';

		if (!file_exists($css) || filemtime($css) < filemtime($scss)) {

			$compiler = new Compiler();

			unlink($css);

			$out = fopen($css, 'w') or die('Error compilando SCSS');

			fwrite($out, $compiler->compileString(file_get_contents($scss))->getCss());

			fclose($out);

		}

	}

	foreach (array_diff(scandir(get_template_directory().'/scss'), array('.', '..')) as $file) {

		$ext = end(explode('.', $file));

		if ($ext != 'scss') {
			continue;
		}

		$name = pathinfo($file, PATHINFO_FILENAME);

		wp_enqueue_style($name, get_template_directory_uri().'/css/'.$name.'.css', array(), uniqid());

	}

	wp_enqueue_script('anime', get_template_directory_uri().'/js/anime.js', array(), uniqid(), true);

	wp_enqueue_script('theme', get_template_directory_uri().'/js/theme.js', array(), uniqid(), true);

	wp_enqueue_script('wow', get_template_directory_uri().'/js/wow.js', array(), uniqid(), true);

	wp_register_script('main', get_template_directory_uri().'/js/main.js', array('jquery', 'anime', 'wow'), uniqid(), true);

	wp_localize_script('main', 'main', array(
		'messages' => array(
			'errors' => __('There are errors in the form. Please correct them before continuing.', 'orenes'),
			'fill' => __('Fill in the marked fields.', 'orenes'),
			'legal' => __('You must accept the legal notice.', 'orenes'),
			'email' => __('Invalid email address', 'orenes')
		)
	));

	wp_enqueue_script('main');

});


// Cargamos los scripts y estilos del área de administración

add_action('admin_enqueue_scripts', function() {

	$scss = get_template_directory().'/scss/admin.scss';

	$css = get_template_directory().'/css/admin.css';

	if (!file_exists($css) || filemtime($css) < filemtime($scss)) {

		$compiler = new Compiler();

		unlink($css);

		$out = fopen($css, 'w') or die('Error compilando SCSS');

		fwrite($out, $compiler->compileString(file_get_contents($scss))->getCss());

		fclose($out);

	}

	wp_enqueue_style('admin', get_stylesheet_directory_uri().'/css/admin.css', array(), uniqid());

	wp_enqueue_script('admin', get_stylesheet_directory_uri().'/js/admin.js', array('jquery'), uniqid(), true);

});


// Eliminamos archivos inncesarios

add_action('init', function() {

	$files = ['readme.html', 'wp-config-sample.php', 'licencia.txt', 'license.txt'];

	foreach ($files as $file) {

		if (file_exists(ABSPATH.$file)) {

			unlink(ABSPATH.$file);

		}

	}

	$dir = get_stylesheet_directory().'/.git';

	if (is_dir($dir)) {

		remove_dir_recursively($git_dir);

	}

	remove_files(get_stylesheet_directory(), '.DS_Store');

});

function remove_dir_recursively($dir) {

	$dir = rtrim($dir, '/');

	if (! is_dir($dir)) {
		return;
	}

	foreach (scandir($dir) as $item) {

		if ($item === '.' || $item === '..') {
			continue;
		}

		$path = $dir . '/' . $item;

		if (is_dir($path)) {
			remove_dir_recursively($path);
		} else {
			unlink($path);
		}
	}

	rmdir($dir);

}

function remove_files($dir, $filename) {

	$dir = rtrim($dir, '/');

	if (!is_dir($dir)) {
		return;
	}

	$items = scandir($dir);

	foreach ($items as $item) {

		if ($item === '.' || $item === '..') {
			continue;
		}

		$path = $dir . '/' . $item;

		if (is_dir($path)) {

			remove_files($path, $filename);

		} elseif ($item === $filename) {

			unlink($path);

		}

	}

}


// Creamos el archivo principal SCSS si no existe

add_action('after_switch_theme', function() {

	$dir = get_stylesheet_directory().'/scss';

	$file = $dir.'/main.scss';

	if (!file_exists($file)) {

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		file_put_contents($file, '');

	}

	$dir = get_stylesheet_directory().'/js';

	$file = $dir.'/main.js';

	if (!file_exists($file)) {

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		file_put_contents($file, '');

	}

});


// Cargamos las tipografías personalizadas

add_action('elementor/controls/controls_registered', function($controls_registry) {

	$fonts = $controls_registry->get_control('font')->get_settings('options');

	foreach (scandir(get_template_directory().'/fonts') as $file) {

		if (!in_array($file, array('.', '..', 'css.php'))) {

			$fonts = array_merge(array($file => 'system'), $fonts);

		}

	}

	$controls_registry->get_control('font')->set_settings('options', $fonts);

}, 10, 1);


// Cargamos los widgets personalizados de Elementor

add_action('after_setup_theme', function() {

	if (!did_action('elementor/loaded')) {
		return;
	}

	include('elementor/init.php');

}, 50);


// Deshabilitamos los pingbacks

add_filter('pings_open', function() {

	return false;

});


// Deshabiltiamos Xmlrpc

add_filter('xmlrpc_enabled', function() {

	return false;

});


// Deshabilitamos los endpoints de la REST API

add_filter('rest_endpoints', function($endpoints) {

	if (is_user_logged_in()) {

		return $endpoints;

	}

	foreach ($endpoints as $route => $endpoint) {

		if (stripos($route, '/wp/') === 0) {

			unset($endpoints[ $route ]);

		}

	}

	return $endpoints;

});


// Deshabilitamos avisos de actualizaciones automáticas

add_filter('auto_plugin_update_send_email', function() {

	return false;

});

add_filter('auto_theme_update_send_email', function() {

	return false;

});


// Eliminamos etiquetas innecesarias de la cabecera

add_action('init', function() {

	remove_action('wp_head', 'feed_links', 2);
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'start_post_rel_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'parent_post_rel_link', 10, 0);
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');
	remove_action('wp_head', 'rest_output_link_wp_head');
	remove_action('template_redirect', 'rest_output_link_header', 11, 0);
	remove_action('template_redirect', 'wp_shortlink_header', 11, 0);

});


// Reemplazamos las urls dinámicas basadas en la ID

function dynamic_urls($arg) {

	if ($arg[1] == 'id') {

		$post_id = $arg[2];

		$post_type = get_post_type($post_id);

		if ($post_type === 'attachment') {

			$link = wp_get_attachment_url($post_id);

		} else {

			if (function_exists('pll_get_post')) {
				$link = get_permalink(pll_get_post($post_id));
			} else {
				$link = get_permalink($post_id);
			}
		}

	} else {

		if (!term_exists((int)$arg[2], $arg[1])) {
			return;
		}

		if (function_exists('pll_get_term')) {
			$link = get_term_link(pll_get_term((int)$arg[2]), $arg[1]);
		} else {
			$link = get_term_link((int)$arg[2], $arg[1]);
		}

	}

	if (!is_wp_error($link) && is_string($link)) {
		return $link;
	}

	return;
}

add_action('elementor/frontend/the_content', function($content) {

	$content = preg_replace_callback("/{{([a-z]+)-([0-9]+)}}/", "dynamic_urls", $content);
	$content = preg_replace_callback("/http:\/\/{{([a-z]+)-([0-9]+)}}/", "dynamic_urls", $content);
	$content = preg_replace_callback("/http[s]?:\/\/([a-z]+)-([0-9]+)/", "dynamic_urls", $content);

	return $content;

});


// Reemplazamos variables en el contenido

add_action('elementor/frontend/the_content', function($content) {

	$replacements = array(
		'{{year}}' => date('Y'),
		'{{cookies}}' => '<span class="cookies-link">'.__('Withdrawal', 'orenes').'</span>'
	);

	$content = strtr($content, $replacements);

	return $content;

});


// Cargamos las funciones extras del tema

directory_include(get_template_directory().'/functions');
directory_include(get_stylesheet_directory().'/functions');

?>
