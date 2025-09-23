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
	if ($c = get_transient($key)) return $c;

	$owner = rawurlencode(GITHUB_OWNER);
	$repo  = rawurlencode(GITHUB_REPO);

	// 1) Último release
	$data = download_github("https://api.github.com/repos/{$owner}/{$repo}/releases/latest");
	if (!is_wp_error($data) && !empty($data['tag_name'])) {
		$tag_raw = (string) $data['tag_name'];   // ej. "v2.3.0" o "2.3.0"
		$tag     = ltrim($tag_raw, 'vV');        // "2.3.0" siempre
		$out = [
			'version'   => $tag,
			'changelog' => (string) ($data['body'] ?? ''),
			'package'   => "https://codeload.github.com/{$owner}/{$repo}/zip/refs/tags/" . rawurlencode($tag_raw),
			'homepage'  => "https://github.com/{$owner}/{$repo}",
			'details'   => "https://github.com/{$owner}/{$repo}/releases/tag/{$tag}", // <- sin "v"
		];
		set_transient($key, $out, 6 * HOUR_IN_SECONDS);
		return $out;
	}

	// 2) Fallback: último tag
	$tags = download_github("https://api.github.com/repos/{$owner}/{$repo}/tags?per_page=1");
	if (!is_wp_error($tags) && !empty($tags[0]['name'])) {
		$tag_raw = (string) $tags[0]['name'];
		$tag     = ltrim($tag_raw, 'vV');
		$out = [
			'version'   => $tag,
			'changelog' => '',
			'package'   => "https://codeload.github.com/{$owner}/{$repo}/zip/refs/tags/" . rawurlencode($tag_raw),
			'homepage'  => "https://github.com/{$owner}/{$repo}",
			'details'   => "https://github.com/{$owner}/{$repo}/releases/tag/{$tag}",
		];
		set_transient($key, $out, 6 * HOUR_IN_SECONDS);
		return $out;
	}

	// 3) Fallback: rama
	$branch = rawurlencode(GITHUB_BRANCH);
	$info   = download_github("https://api.github.com/repos/{$owner}/{$repo}/branches/{$branch}");
	if (!is_wp_error($info) && !empty($info['commit']['sha'])) {
		$sha = substr((string)$info['commit']['sha'], 0, 7);
		$out = [
			'version'   => $sha,
			'changelog' => '',
			'package'   => "https://codeload.github.com/{$owner}/{$repo}/zip/refs/heads/{$branch}",
			'homepage'  => "https://github.com/{$owner}/{$repo}",
			'details'   => "https://github.com/{$owner}/{$repo}/tree/{$branch}",
		];
		set_transient($key, $out, 3 * HOUR_IN_SECONDS);
		return $out;
	}

	return null;
}

// Información del tema padre
function theme_info(): array {
	$parent_slug = get_template();
	$theme       = wp_get_theme($parent_slug);
	return [
		'stylesheet' => $parent_slug,
		'version'    => $theme->get('Version') ?: (defined('THEME_VERSION') ? THEME_VERSION : '0.0.0'),
		'name'       => $theme->get('Name') ?: $parent_slug,
	];
}

// Inyectar actualización al padre
add_filter('pre_set_site_transient_update_themes', function ($transient) {
	$cur = theme_info();         // tu helper existente: usa get_template()
	$rel = fetch_release();
	if (!$rel) return $transient;

	$semver  = preg_match('~^\d+\.\d+\.\d+$~', $rel['version']) && preg_match('~^\d+\.\d+\.\d+$~', $cur['version']);
	$is_newer= $semver ? version_compare($rel['version'], $cur['version'], '>') : ($rel['version'] !== $cur['version']);
	if (!$is_newer) return $transient;

	$item = [
		'theme'       => $cur['stylesheet'],
		'new_version' => $rel['version'],
		'url'         => $rel['details'],   // ← abre /releases/tag/2.3.0 directamente
		'package'     => $rel['package'],
	];

	$transient = is_object($transient) ? $transient : (object)[];
	$transient->response = is_array($transient->response ?? null) ? $transient->response : [];
	$transient->response[$cur['stylesheet']] = $item;

	return $transient;
});

// Modal de información del tema padre
add_filter('themes_api', function ($res, $action, $args) {
	if ($action !== 'theme_information') { return $res; }
	$theme = theme_info();
	if (empty($args->slug) || $args->slug !== $theme['stylesheet']) { return $res; }

	$rel = fetch_release();
	if (!$rel) { return $res; }

	$info                 = new stdClass();
	$info->name           = $theme['name'];
	$info->slug           = $theme['stylesheet'];
	$info->version        = $rel['version'];
	$info->homepage       = $rel['homepage'];
	$info->download_link  = $rel['package'];
	$info->sections       = [
		'description' => sprintf('%s — actualización desde GitHub.', esc_html($theme['name'])),
		'changelog'   => wp_kses_post(nl2br($rel['changelog'] ?? '')),
	];
	return $info;
}, 10, 3);


// Limpiar caché al actualizar temas
add_action('upgrader_process_complete', function ($upgrader, $hook_extra) {
	if (!empty($hook_extra['type']) && $hook_extra['type'] === 'theme') {
		delete_transient('orenes_theme_release');
	}
}, 10, 2);

// Renombrar la carpeta del tema
add_filter('upgrader_post_install', function ($response, $hook_extra, $result) {

	if (empty($hook_extra['type']) || $hook_extra['type'] !== 'theme') {
		return $response;
	}

	$target_slug = 'orenes';
	$themes_dir  = trailingslashit(WP_CONTENT_DIR) . 'themes/';
	$target_dir  = $themes_dir . $target_slug;

	$installed_dir = isset($result['destination']) ? $result['destination'] : '';

	if (! $installed_dir || ! is_dir($installed_dir)) {
		return $response;
	}

	if (basename($installed_dir) === $target_slug) {
		$response['destination'] = $installed_dir;
		return $response;
	}

	if (is_dir($target_dir)) {
		$backup = $target_dir . '-backup-' . time();
		@rename($target_dir, $backup);
		if (is_dir($backup)) {
			$it = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($backup, RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::CHILD_FIRST
			);
			foreach ($it as $node) {
				$node->isDir() ? @rmdir($node->getRealPath()) : @unlink($node->getRealPath());
			}
			@rmdir($backup);
		}
	}

	if (@rename($installed_dir, $target_dir)) {
		$response['destination'] = $target_dir;
		return $response;
	}

	$copy_dir = function ($src, $dst) use (&$copy_dir) {
		if (! is_dir($src)) { return false; }
		@mkdir($dst, 0755, true);
		foreach (scandir($src) as $item) {
			if ($item === '.' || $item === '..') { continue; }
			$from = $src . '/' . $item;
			$to   = $dst . '/' . $item;
			if (is_dir($from)) {
				$copy_dir($from, $to);
			} else {
				@copy($from, $to);
			}
		}
		return true;
	};

	$remove_dir = function ($dir) use (&$remove_dir) {
		if (! is_dir($dir)) { return; }
		foreach (scandir($dir) as $item) {
			if ($item === '.' || $item === '..') { continue; }
			$path = $dir . '/' . $item;
			if (is_dir($path)) {
				$remove_dir($path);
			} else {
				@unlink($path);
			}
		}
		@rmdir($dir);
	};

	if ($copy_dir($installed_dir, $target_dir)) {
		$remove_dir($installed_dir);
		$response['destination'] = $target_dir;
	}

	return $response;
}, 10, 3);
