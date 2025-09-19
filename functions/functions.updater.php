<?php

defined('ABSPATH') || exit;


/* Actualizador
**
** Actualiza el tema a través de repositorio de GitHub
*/

use WP_Error;

// Configuración fija
const GITHUB_OWNER  = 'orenesgrupo';
const GITHUB_REPO   = 'orenes';
const GITHUB_BRANCH = 'main';

// Helpers
function download_github(string $url, int $timeout = 10) {
	$response = wp_remote_get($url, [
		'timeout' => $timeout,
		'headers' => [
			'Accept'     => 'application/vnd.github+json',
			'User-Agent' => 'WP-GitHub-Theme-Updater',
		],
	]);
	if (is_wp_error($response)) { return $response; }
	$code = wp_remote_retrieve_response_code($response);
	if ($code < 200 || $code >= 300) {
		return new WP_Error('http_status', 'GitHub HTTP '.$code, ['url' => $url]);
	}
	$body = wp_remote_retrieve_body($response);
	return json_decode($body, true);
}

// Obtener release
function fetch_release(): ?array {
	$key = 'orenes_theme_release';
	$cached = get_transient($key);
	if ($cached) { return $cached; }

	$owner = rawurlencode(GITHUB_OWNER);
	$repo  = rawurlencode(GITHUB_REPO);

	$data = download_github("https://api.github.com/repos/{$owner}/{$repo}/releases/latest");
	if (!is_wp_error($data) && is_array($data) && !empty($data['name'])) {
		$tag = ltrim((string) $data['name'], 'vV');
		$out = [
			'version'   => $tag,
			'changelog' => (string) ($data['body'] ?? ''),
			'package'   => "https://codeload.github.com/{$owner}/{$repo}/zip/refs/tags/" . rawurlencode($data['name']),
			'homepage'  => "https://github.com/{$owner}/{$repo}",
		];
		set_transient($key, $out, HOUR_IN_SECONDS * 6);
		return $out;
	}

	$tags = download_github("https://api.github.com/repos/{$owner}/{$repo}/tags?per_page=1");
	if (!is_wp_error($tags) && is_array($tags) && !empty($tags[0]['name'])) {
		$name = (string) $tags[0]['name'];
		$tag = ltrim($name, 'vV');
		$out = [
			'version'   => $tag,
			'changelog' => '',
			'package'   => "https://codeload.github.com/{$owner}/{$repo}/zip/refs/tags/" . rawurlencode($name),
			'homepage'  => "https://github.com/{$owner}/{$repo}",
		];
		set_transient($key, $out, HOUR_IN_SECONDS * 6);
		return $out;
	}

	$branch = rawurlencode(GITHUB_BRANCH);
	$info   = download_github("https://api.github.com/repos/{$owner}/{$repo}/branches/{$branch}");
	if (!is_wp_error($info) && is_array($info) && !empty($info['commit']['sha'])) {
		$sha = substr((string) $info['commit']['sha'], 0, 7);
		$out = [
			'version'   => $sha,
			'changelog' => '',
			'package'   => "https://codeload.github.com/{$owner}/{$repo}/zip/refs/heads/{$branch}",
			'homepage'  => "https://github.com/{$owner}/{$repo}",
		];
		set_transient($key, $out, HOUR_IN_SECONDS * 3);
		return $out;
	}

	return null;
}

// Información del tema actual
function theme_info(): array {
	$stylesheet = get_template();
	$theme      = wp_get_theme($stylesheet);
	return [
		'stylesheet' => $stylesheet,
		'slug'       => $theme->get_stylesheet(),
		'version'    => $theme->get('Version') ?: (defined('THEME_VERSION') ? THEME_VERSION : '0.0.0'),
		'name'       => $theme->get('Name') ?: $stylesheet,
		'author'     => $theme->get('Author') ?: '',
	];
}

// Inyecta actualización
add_filter('pre_set_site_transient_update_themes', function ($transient) {
	$current = theme_info();
	$release = fetch_release();
	if (!$rel) { return $transient; }

	$check = preg_match('~^\d+\.\d+\.\d+~', $release['version']) && preg_match('~^\d+\.\d+\.\d+~', $current['version']);
	$newer = $check ? version_compare($release['version'], $current['version'], '>') : ($release['version'] !== $current['version']);

	if (!$newer) { return $transient; }

	$item = [
		'theme'       => $current['stylesheet'],
		'new_version' => $release['version'],
		'url'         => $release['homepage'],
		'package'     => $release['package'],
	];

	$transient = is_object($transient) ? $transient : (object) [];
	if (!isset($transient->response)) { $transient->response = []; }
	$transient->response[$cur['stylesheet']] = $item;

	return $transient;
});

// Modal
add_filter('themes_api', function ($res, $action, $args) {
	if ($action !== 'theme_information') { return $res; }

	$cur = theme_info();
	if (empty($args->slug) || $args->slug !== $cur['stylesheet']) { return $res; }

	$rel = fetch_release();
	if (!$rel) { return $res; }

	$info            = new stdClass();
	$info->name      = $cur['name'];
	$info->slug      = $cur['stylesheet'];
	$info->version   = $rel['version'];
	$info->homepage  = $rel['homepage'];
	$info->download_link = $rel['package'];
	$info->sections  = [
		'description' => sprintf('%s — actualización desde GitHub.', esc_html($cur['name'])),
		'changelog'   => wp_kses_post(nl2br($rel['changelog'] ?: '')),
	];

	return $info;
}, 10, 3);

// Limpieza de caché
add_action('upgrader_process_complete', function ($upgrader, $hook_extra) {
	if (!empty($hook_extra['type']) && $hook_extra['type'] === 'theme') {
		delete_transient('orenes_theme_release');
	}
}, 10, 2);
