<?php

namespace Orenes\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
	exit;
}

class Mapbox_Map extends Widget_Base {

	public function get_name() {
		return 'mapbox_map';
	}

	public function get_title() {
		return 'Mapbox';
	}

	public function get_icon() {
		return 'eicon-google-maps';
	}

	public function get_categories() {
		return ['orenes'];
	}

	public function get_script_depends() {
		return ['jquery'];
	}

	protected function _register_controls() {
		$this->start_controls_section('section_config', [
			'label' => __('Configuration', 'orenes'),
		]);

		$this->add_control('autocenter', [
			'label' => __('Auto center', 'orenes'),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		]);

		$this->add_control('center', [
			'label' => __('Center', 'elementor'),
			'type' => Controls_Manager::TEXT,
			'label_block' => true,
			'condition' => [
				'autocenter!' => 'yes',
			],
		]);

		$this->add_control('zoom', [
			'label' => __('Zoom Level', 'orenes'),
			'type' => Controls_Manager::SLIDER,
			'default' => ['size' => 10],
			'range' => ['px' => ['min' => 1, 'max' => 20]],
		]);

		$this->add_control('pitch', [
			'label' => __('Pitch', 'orenes'),
			'type' => Controls_Manager::SLIDER,
			'default' => ['size' => 0],
			'range' => ['px' => ['min' => 0, 'max' => 360]],
		]);

		$this->add_control('bearing', [
			'label' => __('Bearing', 'orenes'),
			'type' => Controls_Manager::SLIDER,
			'default' => ['size' => 0],
			'range' => ['px' => ['min' => 0, 'max' => 90]],
		]);

		$this->add_responsive_control('height', [
			'label' => __('Height', 'orenes'),
			'type' => Controls_Manager::SLIDER,
			'default' => ['size' => 300],
			'size_units' => ['px', 'vh', 'vw'],
			'range' => [
				'px' => ['min' => 10, 'max' => 1000, 'step' => 10],
				'vh' => ['min' => 10, 'max' => 100, 'step' => 1],
				'vw' => ['min' => 10, 'max' => 100, 'step' => 1],
			],
			'selectors' => [
				'{{WRAPPER}} .map-wrapper' => 'height: {{SIZE}}{{UNIT}};',
			],
		]);

		$this->add_control('scroll', [
			'label' => __('Prevent Scroll', 'orenes'),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		]);

		$this->add_control('poi', [
			'label' => __('Show POIs', 'orenes'),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		]);

		$this->add_control('zoom_control', [
			'label' => __('Show zoom control', 'orenes'),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		]);

		$this->add_control('zoom_click', [
			'label' => __('Zoom on double click', 'orenes'),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		]);

		$this->add_control('dragging', [
			'label' => __('Enable dragging', 'orenes'),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		]);

		$this->add_control('token', [
			'label' => __('Token', 'orenes'),
			'type' => Controls_Manager::TEXT,
		]);

		$this->add_control('style', [
			'label' => __('Style', 'orenes'),
			'type' => Controls_Manager::TEXT,
			'placeholder' => __('mapbox://styles/mapbox/streets-v9', 'orenes'),
		]);

		$this->end_controls_section();

		$this->start_controls_section('section_markers', [
			'label' => __('Markers', 'orenes'),
		]);

		$this->add_control('cluster', [
			'label' => __('Group markers', 'orenes'),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
			'return_value' => 'yes',
		]);

		$this->add_responsive_control('icon_size', [
			'label' => __('Icon size (px)', 'orenes'),
			'type' => Controls_Manager::SLIDER,
			'size_units' => ['px'],
			'default' => ['size' => 40],
			'range' => [
				'px' => ['min' => 10, 'max' => 200, 'step' => 1],
			],
			'selectors' => [], // No aplicamos CSS directo, lo usamos en JS
		]);

		$repeater = new Repeater();

		$repeater->add_control('address', [
			'label' => __('Coordinates (lng,lat)', 'orenes'),
			'type' => Controls_Manager::TEXT,
		]);

		$repeater->add_control('icon', [
			'label' => __('Icon', 'orenes'),
			'type' => Controls_Manager::MEDIA,
		]);

		$repeater->add_control('info', [
			'label' => __('Popup HTML content', 'orenes'),
			'type' => Controls_Manager::TEXTAREA,
		]);

		$repeater->add_control('position', [
			'label' => __('Popup anchor', 'orenes'),
			'type' => Controls_Manager::SELECT,
			'default' => 'bottom',
			'options' => [
				'top' => 'Top',
				'bottom' => 'Bottom',
				'left' => 'Left',
				'right' => 'Right',
				'center' => 'Center',
			],
		]);

		$this->add_control('markers', [
			'label' => __('Markers', 'orenes'),
			'type' => Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(),
			'default' => [],
		]);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$map_id = 'map-' . $this->get_id();
		$center = null;

		if (!empty($settings['center']) && is_string($settings['center']) && strpos($settings['center'], ',') !== false) {
			$coords = array_map('trim', explode(',', $settings['center']));
			if (count($coords) === 2) {
				$center = ['lng' => (float)$coords[0], 'lat' => (float)$coords[1]];
			}
		}

		wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js', [], null, true);
		wp_enqueue_style('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css');

		$features = [];
		$bounds = [];

		foreach ($settings['markers'] as $index => $marker) {
			$coords = explode(',', $marker['address']);
			$lat = (float) trim($coords[1]);
			$lng = (float) trim($coords[0]);
			$icon_id = 'marker_icon_' . $index;

			$features[] = [
				'type' => 'Feature',
				'properties' => [
					'id' => $index,
					'html' => '<div class="marker-wrapper" data-id="' . $index . '">' . $marker['info'] . '</div>',
					'anchor' => $marker['position'],
					'icon' => $icon_id,
					'icon_url' => esc_url($marker['icon']['url']),
				],
				'geometry' => [
					'type' => 'Point',
					'coordinates' => [$lat, $lng]
				]
			];

			$bounds[] = [$lat, $lng];
		}

		$geojson = [
			'type' => 'FeatureCollection',
			'features' => $features
		];

		$json_geojson = json_encode($geojson);
		$json_bounds = json_encode($bounds);

		?>
		<div class="map-wrapper" id="<?= esc_attr($map_id) ?>"></div>
		<script>
		jQuery(window).on('load', function () {
			mapboxgl.accessToken = <?= json_encode($settings['token']) ?>;
			const map_<?= $this->get_id() ?> = new mapboxgl.Map({
				container: <?= json_encode($map_id) ?>,
				style: <?= json_encode($settings['style'] ?: 'mapbox://styles/mapbox/streets-v9') ?>,
				zoom: <?= (float)$settings['zoom']['size'] ?>,
				pitch: <?= (float)$settings['pitch']['size'] ?>,
				bearing: <?= (float)$settings['bearing']['size'] ?>,
				scrollZoom: <?= $settings['scroll'] === 'yes' ? 'true' : 'false' ?>,
				doubleClickZoom: <?= $settings['zoom_click'] === 'yes' ? 'true' : 'false' ?>,
				dragPan: <?= $settings['dragging'] === 'yes' ? 'true' : 'false' ?>,
				<?php if ($center): ?>
				center: [<?= $center['lat'] ?>, <?= $center['lng'] ?>],
				<?php endif; ?>
			});

			if (<?= $settings['zoom_control'] === 'yes' ? 'true' : 'false' ?>) {
				_<?= $this->get_id() ?>.addControl(new mapboxgl.NavigationControl(), 'top-right');
			}

			const geojson = <?= $json_geojson ?>;

			const iconSize = {
				desktop: <?= json_encode($settings['icon_size']['size'] ?? 40) ?>,
				tablet: <?= json_encode($settings['icon_size']['size'] ?? $settings['icon_size']['size']) ?>,
				mobile: <?= json_encode($settings['icon_size']['size'] ?? $settings['icon_size']['size']) ?>,
			};

			function getResponsiveSize() {
				const width = window.innerWidth;
				if (width <= 767) return iconSize.mobile;
				if (width <= 1024) return iconSize.tablet;
				return iconSize.desktop;
			}

			const loadImages = geojson.features.map((feature) => {
				return new Promise((resolve, reject) => {
					map_<?= $this->get_id() ?>.loadImage(feature.properties.icon_url, (error, image) => {
						if (error) {
							console.warn('Error loading image', feature.properties.icon_url);
							return resolve();
						}
						if (!map_<?= $this->get_id() ?>.hasImage(feature.properties.icon)) {
							const scale = getResponsiveSize() / image.width;
							map_<?= $this->get_id() ?>.addImage(feature.properties.icon, image, { pixelRatio: 1 });
							feature.properties.icon_size = scale;
						}
						resolve();
					});
				});
			});


			Promise.all(loadImages).then(() => {
				map_<?= $this->get_id() ?>.addSource('markers', {
					type: 'geojson',
					data: geojson,
					cluster: <?= ($settings['cluster'] == 'yes' ? 'true' : 'false') ?>,
					clusterMaxZoom: 18,
					clusterRadius: 50
				});

				// Layer para los puntos individuales
				map_<?= $this->get_id() ?>.addLayer({
					id: 'unclustered-points',
					type: 'symbol',
					source: 'markers',
					filter: ['!', ['has', 'point_count']],
					layout: {
						'icon-image': ['get', 'icon'],
						'icon-size': ['get', 'icon_size']
					}
				});

				// Opcional: capa para los clusters
				map_<?= $this->get_id() ?>.addLayer({
					id: 'clusters',
					type: 'circle',
					source: 'markers',
					filter: ['has', 'point_count'],
					paint: {
						'circle-color': '#51bbd6',
						'circle-radius': 20
					}
				});

				// Evento click para mostrar popup
				map_<?= $this->get_id() ?>.on('click', 'unclustered-points', (e) => {
					const props = e.features[0].properties;
					const coordinates = e.features[0].geometry.coordinates.slice();
					new mapboxgl.Popup({
						anchor: props.anchor || 'bottom'
					})
						.setLngLat(coordinates)
						.setHTML(props.html)
						.addTo(map_<?= $this->get_id() ?>);
				});

				<?php if(is_null($center)): ?>
				// Fit bounds
				const bounds = <?= $json_bounds ?>;
				if (bounds.length > 0) {
					const bbox = new mapboxgl.LngLatBounds();
					bounds.forEach(coord => bbox.extend(coord));
					map_<?= $this->get_id() ?>.fitBounds(bbox, {
						padding: 50,
						animate: false
					});
				}
				<?php endif; ?>
			})

		});
		</script>
		<?php
	}
}
