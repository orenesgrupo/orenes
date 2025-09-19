<?php
namespace Orenes;

defined('ABSPATH') || exit;

use Elementor\Plugin as ElementorPlugin;
use Elementor\Widgets_Manager;
use Orenes\Widgets\Mapbox_Map;

final class Plugin {

	public function __construct() {
		// Registrar widgets con el hook moderno
		add_action('elementor/widgets/register', [$this, 'register_widgets']);
		// Añadir categoría propia
		add_action('elementor/init', [$this, 'add_category']);
	}

	/** Carga archivos y registra widgets */
	public function register_widgets(Widgets_Manager $widgets_manager): void {
		$this->include_widgets();
		$widgets_manager->register(new Mapbox_Map());
	}

	/** Incluye todos los widgets del directorio */
	private function include_widgets(): void {
		if (function_exists('\directory_include')) {
			\directory_include(get_template_directory() . '/elementor/widgets');
		}
	}

	/** Crea categoría "Orenes" en el panel de widgets */
	public function add_category(): void {
		$elements = ElementorPlugin::$instance->elements_manager ?? null;
		if ($elements) {
			$elements->add_category('orenes', [
				'title' => __('Orenes', 'orenes'),
				'icon'  => 'eicon-map', // icono genérico; cambia si quieres
			]);
		}
	}
}

new Plugin();
