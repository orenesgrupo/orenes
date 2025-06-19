<?php

defined('ABSPATH') or die();


/* Github : Variables de entorno */

if (!defined('GITHUB_OWNER')) {
	define('GITHUB_OWNER', 'orenesgrupo');
}

if (!defined('GITHUB_REPO')) {
	define('GITHUB_REPO', 'orenes');
}

if (!defined('GITHUB_BRANCH')) {
	define('GITHUB_BRANCH', 'main');
}

if (!defined('GITHUB_API_CACHE_EXPIRATION')) {
	define('GITHUB_API_CACHE_EXPIRATION', HOUR_IN_SECONDS);
}


/* Github : Comprobación de versión */

add_filter('pre_set_site_transient_update_themes', function($transient) {

	if (empty($transient->checked)) {
		return $transient;
	}

	// Datos del tema actual

	$slug = get_option('template');

	$theme = wp_get_theme($slug);

	$version  = $theme->get('Version');

	if (!$version) {
		return $transient;
	}

	// Intentamos obtener la versión más reciente en GitHub

	$key = "gh_updater_latest_release_{$slug}";

	$data = get_transient($key);

	if (false === $data) {

		// Llamada a la API de GitHub

		$url = "https://api.github.com/repos/".GITHUB_OWNER."/".GITHUB_REPO."/releases/latest";

		$response = wp_remote_get($url, array(
			'timeout'   => 15,
			'user-agent'=> 'WordPress/'.get_bloginfo('version').'; '.home_url(),
		));

		if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
			return $transient;
		}

		$body = wp_remote_retrieve_body($response);

		$data = json_decode($body);

		if (empty($data) || empty($data->tag_name) || empty($data->zipball_url)) {
			return $transient;
		}

		$data = (object) array(
			'tag_name'    => $data->tag_name,
			'zipball_url' => $data->zipball_url,
	   );

		set_transient($key, $data, GITHUB_API_CACHE_EXPIRATION);
	
	}

	// La versión en GitHub suele venir con prefijo "v" o similar. Normalizamos (quitamos prefijo "v").
	
	$remote = ltrim($data->tag_name, 'vV');

	// Si la versión remota es mayor ofrecemos actualización
	
	if (version_compare($remote, $version, '>')) {

		$update = new stdClass();
	
		$update->slug = $slug;
		$update->new_version = $remote;
		$update->url         = "https://github.com/".GITHUB_OWNER."/".GITHUB_REPO;
		$update->package     = $data->zipball_url; // la URL al zipball de GitHub

		$transient->response[$slug] = $update;
	
	}

	return $transient;

});

add_action('after_theme_row_'.get_option('template'), function($key, $theme) {

	if ($key !== get_option('template')) {
		return;
	}

	$slug = $key;

	$version   = $theme->get('Version');

	$key = "gh_updater_latest_release_{$slug}";

	$data = get_transient($key);

	if (false === $data) {
		return;
	}

	$remote = ltrim($data->tag_name, 'vV');

	if (version_compare($remote, $version, '>')) {
		?>
		<tr class="theme-update animation-fade">
			<td colspan="3" class="update-message notice inline notice-warning notice-alt">
				<p>
					<strong><?php echo esc_html($theme->get('Name')); ?></strong>
					– versión <?php echo esc_html($version); ?> instalada.
					¡Hay una nueva versión <?php echo esc_html($remote); ?> disponible!
					<a href="<?php echo esc_url(wp_nonce_url(self_admin_url('update.php?action=upgrade-theme&theme='.$slug), 'upgrade-theme_'.$slug)); ?>">
						Actualízala ahora.
					</a>
				</p>
			</td>
		</tr>
		<?php
	}
}, 10, 2);

add_filter('upgrader_package_options', function($options, $remote, $upgrader) {


	if (!empty($remote['type']) && 'theme' === $remote['type'] &&!empty($remote['theme'])) {
		
		$slug = get_option('template');
		
		if ($remote['theme'] === $slug) {
		
			$options['clear_destination'] = false;

		}

	}

	return $options;
	
}, 10, 3);
