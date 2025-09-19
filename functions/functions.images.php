<?php

defined('ABSPATH') || exit;


/* Conversión de imágenes
**
** Convierte imágenes a formato WEBP y normaliza nombres al subir a la biblioteca
*/

add_filter('wp_handle_upload', function ($upload) {

	// Solo procesar JPEG, PNG o GIF
	if (!in_array($upload['type'], ['image/jpeg','image/png','image/gif'], true)) {
		return $upload;
	}

	$path = $upload['file'];

	// Requiere Imagick o GD
	if (!extension_loaded('imagick') && !extension_loaded('gd')) {
		return $upload;
	}

	$editor = wp_get_image_editor($path);
	if (is_wp_error($editor)) {
		return $upload;
	}

	$editor->set_quality(80);

	// Limpiar nombre
	$info     = pathinfo($path);
	$dir      = $info['dirname'];
	$filename = strtolower(remove_accents($info['filename']));
	$filename = trim(preg_replace('/[^a-z0-9]+/', '-', $filename), '-');

	// Nombre único con extensión .webp
	$filename = wp_unique_filename($dir, $filename . '.webp');
	$new      = $dir . '/' . $filename;

	// Guardar en WEBP
	$image = $editor->save($new, 'image/webp');
	if (!is_wp_error($image) && is_file($image['path'])) {
		$upload['file'] = $image['path'];
		$upload['url']  = str_replace(basename($upload['url']), basename($image['path']), $upload['url']);
		$upload['type'] = 'image/webp';
		@unlink($path);
	}

	return $upload;

});
