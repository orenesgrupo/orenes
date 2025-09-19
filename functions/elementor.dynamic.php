<?php

defined('ABSPATH') || exit;


/* Etiquetas dinámicas
**
** Añade etiquetas dinámicas a Elementor
*/

add_action('elementor/dynamic_tags/register_tags', function ($dynamic_tags) {
	// Requisitos mínimos
	if (!class_exists('\Elementor\Core\DynamicTags\Data_Tag')) return;

	abstract class Orenes_Term_Field_Base_Tag extends \Elementor\Core\DynamicTags\Data_Tag {
		public function get_group() { return ['site']; }

		protected function get_value(array $options = []) {
			// ACF requerido
			if (!function_exists('get_field')) return null;

			$taxonomy = (string) $this->get_settings('taxonomy');
			if ($taxonomy === '') return null;

			$terms = wp_get_post_terms(get_the_ID(), $taxonomy);
			if (empty($terms) || is_wp_error($terms)) return null;

			$term_id = (int) $terms[0]->term_id;
			if ($term_id <= 0) return null;

			$field = (string) $this->get_settings('field');
			if ($field === '') return null;

			// ACF admite "taxonomy_term" en formato "{$tax}_{$term_id}"
			return get_field($field, "{$taxonomy}_{$term_id}") ?: null;
		}

		protected function _register_controls() {
			// Taxonomías públicas
			$options = [];
			foreach (get_taxonomies(['public' => true], 'objects') as $tax) {
				$options[$tax->name] = $tax->label;
			}

			$this->add_control('taxonomy', [
				'label'   => __('Taxonomy', 'elementor-pro'),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $options,
			]);
			$this->add_control('field', [
				'label' => __('Field', 'elementor-pro'),
				'type'  => \Elementor\Controls_Manager::TEXT,
			]);
		}
	}

	if (!class_exists('Orenes_Term_Image_Tag')) {
		class Orenes_Term_Image_Tag extends Orenes_Term_Field_Base_Tag {
			public function get_name() { return 'category-image'; }
			public function get_title() { return __('Taxonomy image', 'orenes'); }
			public function get_categories() { return ['image']; }
		}
	}

	if (!class_exists('Orenes_Term_Text_Tag')) {
		class Orenes_Term_Text_Tag extends Orenes_Term_Field_Base_Tag {
			public function get_name() { return 'category-text'; }
			public function get_title() { return __('Taxonomy text', 'orenes'); }
			public function get_categories() { return ['text','heading']; }
		}
	}

	$dynamic_tags->register_tag(new \Orenes_Term_Image_Tag());
	$dynamic_tags->register_tag(new \Orenes_Term_Text_Tag());
});
