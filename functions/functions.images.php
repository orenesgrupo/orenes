<?php

defined('ABSPATH') or die();

/* Imágenes : Conversión a WEBP y limpieza del nombre al subirlas */

add_filter('wp_handle_upload', function($upload) {

	if (in_array($upload['type'], ['image/jpeg', 'image/png', 'image/gif'])) {

		$path = $upload['file'];

		if (extension_loaded('imagick') || extension_loaded('gd')) {

			$editor = wp_get_image_editor($path);

			if (!is_wp_error($editor)) {


				// Calidad de la imagen WEBP

				$quality = 80; 
				
				$editor->set_quality($quality);
				

				// Limpiamos el nombre del archivo
				
				$info = pathinfo($path);
				
				$dir = $info['dirname'];
				
				$filename = strtolower($info['filename']);
				$filename = preg_replace('/[^a-z0-9]+/', '-', remove_accents($filename));
				$filename = trim($filename, '-');
				

				// Nos aseguramos que no existe otro archivo con el mismo nombre
				
				$filename = wp_unique_filename($dir, $filename.'.webp');
				
				$new = $dir.'/'.$filename;
				

				// Guardamos en WEBP

				$image = $editor->save($new, 'image/webp');

				if (!is_wp_error($image) && file_exists($image['path'])) {


					// Actualizamos los datos de la imagen

					$upload['file'] = $image['path'];
					$upload['url'] = str_replace(basename($upload['url']), basename($image['path']), $upload['url']);
					$upload['type'] = 'image/webp';

					
					// Eliminamos el archivo original

					@unlink($path);

				}

			}

		}

	}

	return $upload;

});

