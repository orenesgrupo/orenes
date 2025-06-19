<?php

namespace Orenes;

if (! defined('ABSPATH')) {
	exit;
}

use Orenes\Widgets\Mapbox_Map;

class Plugin {

	public function __construct() {
		$this->add_actions();
	}

	private function add_actions() {
		add_action('elementor/widgets/widgets_registered', [$this, 'on_widgets_registered']);
		add_action('elementor/init', [$this, 'elementor_init']);
	}

	public function on_widgets_registered() {
		$this->includes();
		$this->register_widget();
	}

	private function includes() {
		directory_include(get_template_directory().'/elementor/widgets');
	}

	private function register_widget() {
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Mapbox_Map());

	}

	public function elementor_init() {
		$elementor = \Elementor\Plugin::$instance;
		$elementor->elements_manager->add_category(
			'orenes',
			[
				'title' => 'Orenes',
				'icon' => 'font',
			],
			1
		);
	}

}

new Plugin();