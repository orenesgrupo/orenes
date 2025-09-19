<?php

defined('ABSPATH') || exit;


/* Iconos
**
** Carga automáticamente librerías de iconos en Elementor
*/

add_action('init', function () {

	$sources = [
		get_stylesheet_directory().'/icons',
		get_template_directory()  .'/icons',
	];

	$packs = [];

	foreach ($sources as $source) {

		if (!is_dir($source)) continue;

		foreach (glob($source.'/*.zip') ?: [] as $zip) {
			$pack = sanitize_title(pathinfo($zip, PATHINFO_FILENAME)); // nombre del ZIP => slug
			$dest_dir = icons_upload("orenes/icons/{$pack}");
			if (!is_dir($dest_dir) || icons_empty($dest_dir)) {
				icons_extract($zip, $dest_dir);
			}
			if ($tab = icons_build($pack, $dest_dir)) {
				$packs[$pack] = $tab;
			}
		}

		foreach (glob($source.'/*', GLOB_ONLYDIR) ?: [] as $dir) {
			$pack = sanitize_title(basename($dir));
			$dest_dir = icons_upload("orenes/icons/{$pack}");
			if (!is_dir($dest_dir) || icons_empty($dest_dir)) {
				icons_publish($dir, $dest_dir);
			}
			if ($tab = icons_build($pack, $dest_dir)) {
				$packs[$pack] = $tab;
			}
		}
	}

	if (!$packs) return;

	add_filter('elementor/icons_manager/additional_tabs', function ($tabs) use ($packs) {
		foreach ($packs as $slug => $tab) {
			$key = $slug;
			$i = 2;
			while (isset($tabs[$key])) { $key = $slug.'-'.$i++; }
			$tabs[$key] = $tab;
		}
		return $tabs;
	});

}, 9);

// Helpers

function icons_upload(string $dir): string {
	$u = wp_upload_dir();
	$path = trailingslashit($u['basedir']).ltrim($dir, '/');
	wp_mkdir_p($path);
	return trailingslashit($path);
}

function icons_url(string $dir): string {
	$u = wp_upload_dir();
	return trailingslashit($u['baseurl']).ltrim($dir, '/').'/';
}

function icons_empty(string $dir): bool {
	$list = @scandir($dir);
	return !$list || count(array_diff($list, ['.','..'])) === 0;
}

function icons_extract(string $zip, string $dest): void {
	if (!class_exists('ZipArchive')) return;
	$z = new ZipArchive();
	if ($z->open($zip) === true) { $z->extractTo($dest); $z->close(); }
}

function icons_publish(string $source_dir, string $dest): void {
	wp_mkdir_p($dest);
	$css = icons_css($source_dir);
	if ($css) {
		copy($css, $dest.'/icons.css');
		$base = dirname($css);
		foreach (['woff2','woff','ttf','otf','svg'] as $ext) {
			foreach (glob($base.'/*.'.$ext) ?: [] as $f) {
				@copy($f, $dest.'/'.basename($f));
			}
		}
	}
}

function icons_css(string $dir): ?string {
	$candidates = array_merge(
		glob($dir.'/{icons,icon,style,styles,lucide,bootstrap,material}*.css', GLOB_BRACE) ?: [],
		glob($dir.'/**/*.css', GLOB_BRACE) ?: []
	);
	return $candidates ? $candidates[0] : null;
}

function icons_build(string $pack_slug, string $dest_dir): ?array {
	$css = icons_css($dest_dir);
	if (!$css || !is_file($css)) return null;

	$css_url = icons_url("orenes/icons/{$pack_slug}").basename($css);
	$icons_info = icons_parse($css);
	if (!$icons_info['icons']) return null;

	$label = ucwords(str_replace(['-','_'], ' ', $pack_slug));
	$ver   = (string) filemtime($css);

	return [
		'name'           => $pack_slug,
		'label'          => $label,
		'labelIcon'      => 'eicon-star',
		'prefix'         => $icons_info['prefix'].'-',
		'displayPrefix'  => $icons_info['prefix'],
		'url'            => $css_url,
		'enqueue'        => [$css_url],
		'ver'            => $ver,
		'native'         => false,
		'icons'          => $icons_info['icons'],
	];

}
function icons_parse(string $css_file): array {
	$css = @file_get_contents($css_file) ?: '';
	preg_match_all('~\.([a-z0-9_]+)-([a-z0-9_-]+)\s*[:{]~i', $css, $m, PREG_SET_ORDER);
	if (!$m) return ['prefix' => 'icon', 'icons' => []];

	$counts = [];
	$icons = [];
	foreach ($m as $mt) {
		$p = strtolower($mt[1]);
		$n = strtolower($mt[2]);
		$counts[$p] = ($counts[$p] ?? 0) + 1;
		$icons[] = $p.'-'.$n;
	}
	arsort($counts);
	$prefix = array_key_first($counts);

	$icons = array_values(array_unique(array_filter($icons, function ($c) use ($prefix) {
		return str_starts_with($c, $prefix.'-');
	})));
	natcasesort($icons);
	return ['prefix' => $prefix, 'icons' => array_values($icons)];
}
