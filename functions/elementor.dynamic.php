<?php

defined('ABSPATH') or die();


/* Elementor : Etiquetas dinámicas para categorías de tratamientos */

add_action('elementor/dynamic_tags/register_tags', function($dynamic_tags) {

	class Custom_Term_Image_Tag extends Elementor\Core\DynamicTags\Data_Tag {

		public function get_name() {
			return 'category-image';
		}

		public function get_categories() {
			return ['image'];
		}

		public function get_group() {
			return ['site'];
		}

		public function get_title() {
			return __('Taxonomy image', 'orenes');
		}

		protected function get_value(array $options = array()) {

			$terms = wp_get_post_terms(get_the_ID(), $this->get_settings('taxonomy'));

			if (!is_array($terms)) {
				return;
			}

			$term = $terms[0]->term_id;

			if (!function_exists('get_field')) {
				return;
			}

			$image = get_field($this->get_settings('field'), $this->get_settings('taxonomy').'_'.$term);

			if (!$image) {
				return;
			}

			return $image;
		}


		protected function _register_controls() {

			$options = array();

			foreach (get_taxonomies(array('public' => true), 'objects') as $taxonomy) {
				$options[$taxonomy->name] = $taxonomy->label; 
			}
		
			$this->add_control(
				'taxonomy', array(
					'label' => __('Taxonomy', 'elementor-pro'),
					'type' => Elementor\Controls_Manager::SELECT,
					'options' => $options
				)
			);
		
			$this->add_control(
				'field', array(
					'label' => __('Field', 'elementor-pro'),
					'type' => Elementor\Controls_Manager::TEXT,
				)
			);
		
		}


	}

	$dynamic_tags->register_tag('Custom_Term_Image_Tag');

});

add_action('elementor/dynamic_tags/register_tags', function($dynamic_tags) {

	class Custom_Term_Text_Tag extends Elementor\Core\DynamicTags\Data_Tag {

		public function get_name() {
			return 'category-text';
		}

		public function get_categories() {
			return ['text', 'heading'];
		}

		public function get_group() {
			return ['site'];
		}

		public function get_title() {
			return __('Taxonomy text', 'orenes');
		}

		protected function get_value(array $options = array()) {

			$terms = wp_get_post_terms(get_the_ID(), $this->get_settings('taxonomy'));

			if (!is_array($terms)) {
				return;
			}

			$term = $terms[0]->term_id;

			if (!function_exists('get_field')) {
				return;
			}

			$image = get_field($this->get_settings('field'), $this->get_settings('taxonomy').'_'.$term);

			if (!$image) {
				return;
			}

			return $image;
		}


		protected function _register_controls() {

			$options = array();

			foreach (get_taxonomies(array('public' => true), 'objects') as $taxonomy) {
				$options[$taxonomy->name] = $taxonomy->label; 
			}
		
			$this->add_control(
				'taxonomy', array(
					'label' => __( 'Taxonomy', 'elementor-pro' ),
					'type' => Elementor\Controls_Manager::SELECT,
					'options' => $options
				)
			);
		
			$this->add_control(
				'field', array(
					'label' => __( 'Field', 'elementor-pro' ),
					'type' => Elementor\Controls_Manager::TEXT,
				)
			);
		
		}


	}

	$dynamic_tags->register_tag('Custom_Term_Text_Tag');

});