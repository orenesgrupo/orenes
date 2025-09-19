<?php
/**
 * Grupo Orenes - Tema WordPress
 * Desarrollado por Samuel E. Cerezo
 * 
 * 2.2.0
 */

defined('ABSPATH') || exit;

// Librería SCSS
require_once get_template_directory().'/inc/scssphp/scss.inc.php';
use ScssPhp\ScssPhp\Compiler;

// Configuraciones iniciales
define('THEME_VERSION', '2.2.1');
define('THEME_SLUG', 'orenes');

add_action('after_setup_theme', function () {
	load_theme_textdomain(THEME_SLUG, get_template_directory().'/languages');
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
	add_theme_support('align-wide');
	add_theme_support('responsive-embeds');
});

// Soporte de menús
add_action('after_setup_theme', function () {
	register_nav_menus(['primary' => __('Menú principal', THEME_SLUG)]);
});

// No redimensionar imágenes grandes
add_filter('big_image_size_threshold', '__return_false');

// Encabezados CSP (legible, filtrable)
add_action('send_headers', function () {
	if (is_user_logged_in() && current_user_can('manage_options')) { return; }

	$self = rtrim(site_url(), '/');

	$policies = [
		'default-src' => ["'self'"],
		'script-src' => [
			"'self'", "'unsafe-inline'", "'unsafe-eval'",
			$self,
			'https://*.covermanager.com','https://www.googletagmanager.com','https://www.google.com',
			'https://www.gstatic.com','https://cdnjs.cloudflare.com','https://*.youtube.com',
			'https://pagead2.googlesyndication.com','https://googleads.g.doubleclick.net',
			'https://*.googleadservices.com','https://adservice.google.com','https://*.doubleclick.net',
			'https://*.facebook.net','https://www.recaptcha.net','https://www.gstatic.com/recaptcha/',
			'https://*.mapbox.com','https://*.intercom.io','https://*.intercomcdn.com',
		],
		'connect-src' => [
			"'self'", $self,
			'https://*.covermanager.com','https://www.googletagmanager.com','https://google.com',
			'https://www.google.com','https://www.gstatic.com','https://cdnjs.cloudflare.com','https://*.youtube.com',
			'https://pagead2.googlesyndication.com','https://googleads.g.doubleclick.net','https://adservice.google.com',
			'https://*.doubleclick.net','https://*.facebook.net','https://*.facebook.com','https://*.google-analytics.com',
			'https://*.google.es','https://*.wpo365.com','https://www.recaptcha.net','https://*.mapbox.com',
			'https://*.intercom.io','wss://*.intercom.io',
		],
		'img-src' => [
			"'self'", 'data:', 'blob:',
			'https://secure.gravatar.com','https://*.w.org','https://library.elementor.com',
			'https://pagead2.googlesyndication.com','https://googleads.g.doubleclick.net','https://*.googleadservices.com',
			'https://*.gtranslate.net','https://*.doubleclick.net','https://*.wpo365.com','https://*.facebook.com',
			'https://*.google.com','https://*.google.es','https://*.ytimg.com',
		],
		'style-src' => ["'self'", "'unsafe-inline'", 'https://*.mapbox.com'],
		'style-src-elem' => ["'self'", "'unsafe-inline'", $self, 'https://*.mapbox.com','https://*.googleapis.com'],
		'font-src' => ["'self'", 'data:','https://*.sharepointonline.com','https://*.akamaihd.net','https://*.office.net','https://*.gstatic.com','https://*.intercomcdn.com'],
		'media-src' => ["'self'", 'blob:', 'data:', $self, 'https://*.archive.org'],
		'frame-src' => [
			"'self'", 'blob:', $self,
			'https://www.google.com','https://*.youtube.com','https://*.youtube-nocookie.com','https://*.covermanager.com',
			'https://tpc.googlesyndication.com','https://*.doubleclick.net','https://*.googletagmanager.com',
			'https://www.google.com/recaptcha/','https://www.recaptcha.net','https://archive.org',
		],
		'worker-src' => ["'self'", 'blob:'],
	];

	$policies = apply_filters('csp_policies', $policies);

	$lines = [];
	foreach ($policies as $directive => $sources) {
		$sources = array_values(array_unique(array_filter(array_map('trim', (array) $sources))));
		natcasesort($sources);
		$lines[] = $directive.' '.implode(' ', $sources);
	}

	header('Content-Security-Policy: '.implode('; ', $lines));

});

// Utilidades comunes
function asset_version(string $abs_path, $fallback = THEME_VERSION) {
	return file_exists($abs_path) ? filemtime($abs_path) : $fallback;
}
function upload_paths(string $subdir): array {
	$dir = wp_upload_dir();
	return [
		'dir' => trailingslashit($dir['basedir']).ltrim($subdir, '/'),
		'url' => trailingslashit($dir['baseurl']).ltrim($subdir, '/'),
	];
}

// Compilación SCSS
function compile_scss(string $input, string $output): void {
	if (!file_exists($input)) { return; }
	if (!file_exists($output) || filemtime($output) < filemtime($input)) {
		try {
			$compiler = new Compiler();
			$compiler->setImportPaths(dirname($input));
			$compiler->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);
			$css = $compiler->compileString(file_get_contents($input))->getCss();
			wp_mkdir_p(dirname($output));
			file_put_contents($output, $css);
		} catch (\Throwable $e) { }
	}
}

// Carga automática de archivos CSS y JS en frontend
add_action('wp_enqueue_scripts', function () {
	$file = get_stylesheet_directory().'/style.css';
	wp_enqueue_style(THEME_SLUG.'-style', get_stylesheet_uri(), [], asset_version($file));

	$scss = get_stylesheet_directory().'/scss/main.scss';
	if (!file_exists($scss)) { wp_mkdir_p(dirname($scss)); file_put_contents($scss, ""); }

	$dir = get_stylesheet_directory().'/scss';
	if (is_dir($dir)) {
		$upload = upload_paths('orenes');
		foreach (glob($dir.'/*.scss') as $file) {
			$name = pathinfo($file, PATHINFO_FILENAME);
			$path = $upload['dir'].'/'.$name.'.css';
			$url  = $upload['url'].'/'.$name.'.css';
			compile_scss($file, $path);
			if (file_exists($path)) { wp_enqueue_style('scss-'.$name, $url, [], asset_version($path)); }
		}
	}

	$fonts = upload_paths('orenes/fonts.css');
	if (file_exists($fonts['dir'])) {
		$version = get_option('fonts_version') ?: filemtime($fonts['dir']);
		wp_enqueue_style(THEME_SLUG.'-fonts', $fonts['url'], [], $version);
	}

	$anime = get_template_directory().'/js/anime.js';
	$wow   = get_template_directory().'/js/wow.js';
	$theme = get_template_directory().'/js/theme.js';
	$main  = get_stylesheet_directory().'/js/main.js';

	wp_enqueue_script('anime', get_template_directory_uri().'/js/anime.js', [], asset_version($anime), true);
	wp_enqueue_script('wow',   get_template_directory_uri().'/js/wow.js',   [], asset_version($wow),   true);

	wp_register_script(THEME_SLUG, get_template_directory_uri().'/js/theme.js', ['jquery','anime','wow'], asset_version($theme), true);
	wp_localize_script(THEME_SLUG, 'main', [
		'messages' => [
			'errors' => __('There are errors in the form. Please correct them before continuing.', THEME_SLUG),
			'fill'   => __('Fill in the marked fields.', THEME_SLUG),
			'legal'  => __('You must accept the legal notice.', THEME_SLUG),
			'email'  => __('Invalid email address', THEME_SLUG),
		],
		'orenes' => [
			'theme' => get_template_directory_uri(),
			'ajax' => [
				'url' => admin_url('/admin-ajax.php'),
				'nonce' => wp_create_nonce('ajax_nonce')
			]
		]
	]);
	wp_enqueue_script(THEME_SLUG);

	$main = get_stylesheet_directory().'/js/main.js';
	if (!file_exists($main)) { wp_mkdir_p(dirname($main)); file_put_contents($main, ""); }
	wp_enqueue_script('main', get_stylesheet_directory_uri().'/js/main.js', ['orenes'], asset_version($main), true);
}, 20);

// Carga automática de archivos CSS y JS en área de administración
add_action('admin_enqueue_scripts', function () {
	$scss = get_stylesheet_directory().'/scss/admin.scss';
	$css  = get_stylesheet_directory().'/css/admin.css';
	$js   = get_stylesheet_directory().'/js/admin.js';

	if (!file_exists($scss)) { wp_mkdir_p(dirname($scss)); file_put_contents($scss, ""); }
	if (!file_exists($js))   { wp_mkdir_p(dirname($js));   file_put_contents($js,   ""); }

	if (file_exists($scss) && (!file_exists($css) || filemtime($css) < filemtime($scss))) {
		try {
			$compiler = new Compiler();
			$compiler->setImportPaths(dirname($scss));
			$compiler->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);
			wp_mkdir_p(dirname($css));
			file_put_contents($css, $compiler->compileString(file_get_contents($scss))->getCss());
		} catch (\Throwable $e) { }
	}

	wp_enqueue_style('admin', get_stylesheet_directory_uri().'/css/admin.css', [], asset_version($css));
	wp_enqueue_script('admin', get_stylesheet_directory_uri().'/js/admin.js', ['jquery'], asset_version($js), true);
}, 20);

// Limpieza de archivos núcleo
add_action('upgrader_process_complete', function ($upgrader, $hook_extra) {
	if (empty($hook_extra['type']) || $hook_extra['type'] !== 'core') { return; }
	cleanup_core();
}, 10, 2);

add_action('init', function () {
	if (!wp_next_scheduled('cleanup_weekly')) {
		wp_schedule_event(time()+300, 'weekly', 'cleanup_weekly');
	}
});
add_action('cleanup_weekly', 'cleanup_core');

function cleanup_core(): void {

	foreach (['readme.html','wp-config-sample.php','licencia.txt','license.txt'] as $root) {
		$path = ABSPATH.$root;
		if (is_file($path) && is_writable($path)) { @unlink($path); }
	}

	$theme_dir = get_template_directory();

	$dirs = [
		$theme_dir.'/.git',
	];
	$files = [
		$theme_dir.'/.gitattributes',
		$theme_dir.'/.gitignore',
		$theme_dir.'/LICENSE',
		$theme_dir.'/README.md',
		$theme_dir.'/readme.md',
	];

	foreach ($dirs as $dir) {
		rmdir_recursive($dir);
	}
	foreach ($files as $file) {
		if (is_file($file) && is_writable($file)) {
			@unlink($file);
		}
	}

}

// Helper para borrado recursivo
function rmdir_recursive(string $dir): void {
	$dir = rtrim($dir, '/');
	if (!is_dir($dir)) return;
	foreach (scandir($dir) ?: [] as $item) {
		if ($item === '.' || $item === '..') continue;
		$path = $dir . '/' . $item;
		if (is_dir($path)) { rmdir_recursive($path); }
		elseif (is_writable($path)) { @unlink($path); }
	}
	@rmdir($dir);
}

// SVG seguro
add_filter('upload_mimes', function ($mimes) { $mimes['svg'] = 'image/svg+xml'; return $mimes; });
add_filter('wp_handle_upload_prefilter', function ($file) {
	if (($file['type'] ?? '') === 'image/svg+xml') {
		$contents = @file_get_contents($file['tmp_name']);
		if ($contents && preg_match('~<(script|foreignObject)\b~i', $contents)) {
			$file['error'] = __('SVG contains disallowed elements.', THEME_SLUG);
		}
	}
	return $file;
});

// Limpieza de cabecera
add_action('init', function () {
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

// Deshabilitar pingbacks y XML-RPC
add_filter('pings_open', '__return_false');
add_filter('xmlrpc_enabled', '__return_false');

// Permitir REST solo a usuarios autenticados
add_filter('rest_endpoints', function ($endpoints) {
	if (is_user_logged_in()) { return $endpoints; }
	foreach ($endpoints as $route => $def) {
		if (stripos($route, '/wp/') === 0) { unset($endpoints[$route]); }
	}
	return $endpoints;
});

// Sustituciones de texto en Elementor (solo dos filtros)
add_action('elementor/frontend/the_content', function ($content) {
	$replacements = [
		'{{year}}'    => date('Y'),
		'{{cookies}}' => '<span class="cookies-link">'.esc_html__('Withdrawal', THEME_SLUG).'</span>',
	];
	$replacements = apply_filters('content_placeholders', $replacements, $content);
	$content      = strtr($content, $replacements);

	$links = function (string $kind, int $id) {
		$kind = strtolower($kind);
		if (in_array($kind, ['id','attachment'], true)) {
			$post_type = get_post_type($id);
			if ($post_type === 'attachment') { return wp_get_attachment_url($id); }
			return function_exists('pll_get_post') ? get_permalink(pll_get_post($id)) : get_permalink($id);
		}
		if (!term_exists($id, $kind)) { return null; }
		return function_exists('pll_get_term') ? get_term_link(pll_get_term($id)) : get_term_link($id);
	};
	$resolver = apply_filters('content_links', $links);

	$pattern  = '~(?:https?://)?\{\{([a-z]+)-(\d+)\}\}~i';
	$content = preg_replace_callback($pattern, function ($matches) use ($resolver) {
		$link = $resolver($matches[1], (int) $matches[2]);
		return (!is_wp_error($link) && is_string($link) && $link !== '') ? esc_url($link) : $matches[0];
	}, $content);

	return $content;
});

// Carga recursiva de funciones
function directory_include(string $dir, bool $recursive = true): void {
	if (!is_dir($dir)) { return; }
	$files = [];
	foreach (scandir($dir) ?: [] as $f) {
		if ($f === '.' || $f === '..') { continue; }
		$path = $dir.'/'.$f;
		if (is_dir($path) && $recursive) { directory_include($path, true); continue; }
		if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') { $files[] = $path; }
	}
	natsort($files);
	foreach ($files as $php) { require_once $php; }
}
directory_include(get_template_directory().'/functions');
directory_include(get_stylesheet_directory().'/functions');

// Carga automática de tipografías
add_action('init', 'fonts_maybe_generate', 9);
add_action('wp_enqueue_scripts', function () {
	$fonts = upload_paths('orenes/fonts.css');
	if (file_exists($fonts['dir'])) {
		$ver = get_option('fonts_version') ?: filemtime($fonts['dir']);
		wp_enqueue_style('orenes', $fonts['url'], [], $ver);
	}
}, 20);

// Añadir familias al selector de Elementor
add_action('elementor/controls/controls_registered', function ($registry) {
	$control = $registry->get_control('font');
	if (!$control) { return; }
	$options = (array) $control->get_settings('options');
	foreach (fonts_families() as $family) {
		$options = [''.$family => 'system'] + $options;
	}
	$control->set_settings('options', $options);
}, 10, 1);

// Helpers para fuentes tipográficas
function fonts_maybe_generate(): void {
	$base = get_stylesheet_directory().'/fonts';
	if (!is_dir($base)) { return; }

	fonts_cleanup($base);

	$files = fonts_files($base);
	$items = [];
	foreach ($files as $absolute) {
		$rel = ltrim(str_replace(get_stylesheet_directory(), '', $absolute), '/');
		$items[] = $rel.'|'.filesize($absolute).'|'.filemtime($absolute);
	}
	$signature = md5(implode("\n", $items));
	if ($signature === get_option('fonts_signature')) { return; }

	$css = fonts_build($files);
	if ($css === '') { return; }

	$upload = upload_paths('orenes');
	wp_mkdir_p($upload['dir']);
	file_put_contents($upload['dir'].'/fonts.css', $css);

	update_option('fonts_signature', $signature, false);
	update_option('fonts_version', (string) filemtime($upload['dir'].'/fonts.css'), false);
}

function fonts_files(string $base_dir): array {
	$glob = glob(trailingslashit($base_dir).'*/*.{woff2,woff}', GLOB_BRACE) ?: [];
	natsort($glob);
	return array_values($glob);
}

function fonts_build(array $files): string {
	$fonts = [];
	$base_uri = trailingslashit(get_stylesheet_directory_uri());

	foreach ($files as $abs) {
		$extension   = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
		$file        = basename($abs);
		$dir         = basename(dirname($abs));
		$filename    = pathinfo($file, PATHINFO_FILENAME);
		$family      = fonts_family($dir);

		if (!preg_match('~^(100|200|300|400|500|600|700|800|900)(i)?$~i', $filename, $matches)) { continue; }
		$weight = (int) $matches[1];
		$style  = !empty($matches[2]) ? 'italic' : 'normal';

		$key = strtolower($family).'|'.$weight.'|'.$style;
		$fonts[$key] ??= ['family' => $family, 'weight' => $weight, 'style' => $style, 'src' => []];

		$relative = 'fonts/'.rawurlencode($dir).'/'.rawurlencode($file);
		$url      = $base_uri.$relative;

		if ($extension === 'woff2') {
			$fonts[$key]['src']['30:woff2'] = "url('{$url}') format('woff2')";
		} elseif ($extension === 'woff') {
			$fonts[$key]['src']['20:woff']  = "url('{$url}') format('woff')";
		}
	}

	$out = [];
	foreach ($fonts as $font) {
		if (!$font['src']) { continue; }
		krsort($font['src']);
		$source = implode(",\n\t\t", array_values($font['src']));
		$family = addslashes($font['family']);
		$out[]  = "@font-face {\n\tfont-family: '{$family}';\n\tsrc: {$source};\n\tfont-weight: {$font['weight']};\n\tfont-style: {$font['style']};\n\tfont-display: swap;\n}";
	}
	return implode("\n\n", $out);
}

function fonts_family(string $slug): string {
	$name = ucwords(str_replace(['-', '_'], ' ', trim($slug)));
	return preg_replace('~\bUi\b~', 'UI', $name);
}

function fonts_cleanup(string $base): void {
	$enabled = apply_filters('fonts_cleanup_enabled', true);
	if (!$enabled) { return; }
	$extensions = apply_filters('fonts_cleanup_extensions', ['svg','ttf']);
	$pattern    = '*/*.{'.implode(',', array_map('strtolower', (array) $extensions)).'}';
	foreach (glob(trailingslashit($base).$pattern, GLOB_BRACE) ?: [] as $absolute) {
		if (is_file($absolute) && is_writable($absolute)) { @unlink($absolute); }
	}
}

// Scaffold mínimo en child
add_action('after_switch_theme', function () {
	foreach ([get_stylesheet_directory().'/scss/main.scss', get_stylesheet_directory().'/scss/admin.scss', get_stylesheet_directory().'/js/main.js', get_stylesheet_directory().'/js/admin.js'] as $file) {
		if (!file_exists($file)) { wp_mkdir_p(dirname($file)); file_put_contents($file, ''); }
	}
});
