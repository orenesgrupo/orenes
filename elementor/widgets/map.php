<?php
namespace Orenes\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) { exit; }

class Mapbox_Map extends Widget_Base {

	public function get_name() { return 'mapbox_map'; }
	public function get_title() { return 'Mapbox'; }
	public function get_icon() { return 'eicon-google-maps'; }
	public function get_categories() { return ['orenes']; }
	public function get_script_depends() { return ['jquery']; }

	protected function register_controls() {
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
			'label' => __('Center [lng,lat]', 'orenes'),
			'type' => Controls_Manager::TEXT,
			'label_block' => true,
			'condition' => ['autocenter!' => 'yes'],
			'placeholder' => '-0.3763,39.4699',
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
			'range' => ['px' => ['min' => 0, 'max' => 85]],
		]);

		$this->add_control('bearing', [
			'label' => __('Bearing', 'orenes'),
			'type' => Controls_Manager::SLIDER,
			'default' => ['size' => 0],
			'range' => ['px' => ['min' => 0, 'max' => 360]],
		]);

		$this->add_responsive_control('height', [
			'label' => __('Height', 'orenes'),
			'type' => Controls_Manager::SLIDER,
			'default' => ['size' => 300, 'unit' => 'px'],
			'size_units' => ['px', 'vh', 'vw'],
			'range' => [
				'px' => ['min' => 10, 'max' => 1000, 'step' => 10],
				'vh' => ['min' => 10, 'max' => 100, 'step' => 1],
				'vw' => ['min' => 10, 'max' => 100, 'step' => 1],
			],
			'selectors' => ['{{WRAPPER}} .map-wrapper' => 'height: {{SIZE}}{{UNIT}};'],
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
			'placeholder' => __('mapbox://styles/mapbox/streets-v12', 'orenes'),
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
			'range' => ['px' => ['min' => 10, 'max' => 200, 'step' => 1]],
		]);

		$repeater = new Repeater();
		$repeater->add_control('address', [
			'label' => __('Coordinates [lng,lat]', 'orenes'),
			'type' => Controls_Manager::TEXT,
			'placeholder' => '-0.3763,39.4699',
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
				'top' => 'Top','bottom' => 'Bottom','left' => 'Left','right' => 'Right','center' => 'Center',
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
		$s = $this->get_settings_for_display();
		$map_id = 'map-' . $this->get_id();

		// Center [lng, lat]
		$center = null;
		if (!empty($s['center']) && is_string($s['center']) && strpos($s['center'], ',') !== false) {
			$coords = array_map('trim', explode(',', $s['center']));
			if (count($coords) === 2) { $center = ['lng' => (float)$coords[0], 'lat' => (float)$coords[1]]; }
		}

		// Encolar Mapbox GL
		wp_enqueue_script('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js', [], '2.14.1', true);
		wp_enqueue_style('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css', [], '2.14.1');

		$features = [];
		$bounds = [];

		if (!empty($s['markers']) && is_array($s['markers'])) {
			foreach ($s['markers'] as $i => $m) {
				if (empty($m['address']) || strpos($m['address'], ',') === false) { continue; }
				[$lngS, $latS] = array_map('trim', explode(',', $m['address']));
				$lng = is_numeric($lngS) ? (float)$lngS : null;
				$lat = is_numeric($latS) ? (float)$latS : null;
				if ($lng === null || $lat === null) { continue; }

				$icon_url = !empty($m['icon']['url']) ? esc_url($m['icon']['url']) : '';
				$features[] = [
					'type' => 'Feature',
					'properties' => [
						'id' => $i,
						'html' => '<div class="marker-wrapper" data-id="' . (int)$i . '">' . wp_kses_post($m['info'] ?? '') . '</div>',
						'anchor' => $m['position'] ?? 'bottom',
						'icon' => 'marker_icon_' . $i,
						'icon_url' => $icon_url,
					],
					'geometry' => [
						'type' => 'Point',
						'coordinates' => [$lng, $lat], // [lng, lat] correcto
					],
				];
				$bounds[] = [$lng, $lat];
			}
		}

		$geojson = ['type' => 'FeatureCollection', 'features' => $features];

		$zoom    = (float)($s['zoom']['size']   ?? 10);
		$pitch   = (float)($s['pitch']['size']  ?? 0);
		$bearing = (float)($s['bearing']['size']?? 0);
		$icon_px = (int)  ($s['icon_size']['size'] ?? 40);

		?>
		<div class="map-wrapper" id="<?=
			esc_attr($map_id) ?>"></div>
		<script>
		jQuery(function () {
			mapboxgl.accessToken = <?= wp_json_encode($s['token'] ?? '') ?>;
			const map = new mapboxgl.Map({
				container: <?= wp_json_encode($map_id) ?>,
				style: <?= wp_json_encode($s['style'] ?: 'mapbox://styles/mapbox/streets-v12') ?>,
				zoom: <?= $zoom ?>,
				pitch: <?= $pitch ?>,
				bearing: <?= $bearing ?>,
				scrollZoom: <?= ($s['scroll'] === 'yes') ? 'true' : 'false' ?>,
				doubleClickZoom: <?= ($s['zoom_click'] === 'yes') ? 'true' : 'false' ?>,
				dragPan: <?= ($s['dragging'] === 'yes') ? 'true' : 'false' ?>,
				<?php if ($center): ?>center: [<?= $center['lng'] ?>, <?= $center['lat'] ?>],<?php endif; ?>
			});

			if (<?= ($s['zoom_control'] === 'yes') ? 'true' : 'false' ?>) {
				map.addControl(new mapboxgl.NavigationControl(), 'top-right');
			}

			const geojson = <?= wp_json_encode($geojson) ?>;
			const iconSizePx = <?= (int) $icon_px ?>;

			// Cargar iconos y calcular escala
			const loads = geojson.features.map(f => new Promise(res => {
				if (!f.properties.icon_url) return res();
				map.loadImage(f.properties.icon_url, (err, image) => {
					if (!err && image && !map.hasImage(f.properties.icon)) {
						map.addImage(f.properties.icon, image, { pixelRatio: 1 });
						f.properties.icon_size = iconSizePx / image.width; // escala relativa
					}
					res();
				});
			}));

			Promise.all(loads).then(() => {
				map.addSource('markers', {
					type: 'geojson',
					data: geojson,
					cluster: <?= ($s['cluster'] === 'yes') ? 'true' : 'false' ?>,
					clusterMaxZoom: 18,
					clusterRadius: 50
				});

				map.addLayer({
					id: 'unclustered-points',
					type: 'symbol',
					source: 'markers',
					filter: ['!', ['has', 'point_count']],
					layout: {
						'icon-image': ['get', 'icon'],
						'icon-size': ['get', 'icon_size']
					}
				});

				map.addLayer({
					id: 'clusters',
					type: 'circle',
					source: 'markers',
					filter: ['has', 'point_count'],
					paint: {
						'circle-color': '#51bbd6',
						'circle-radius': 20
					}
				});

				// Popup
				map.on('click', 'unclustered-points', (e) => {
					const f = e.features[0];
					new mapboxgl.Popup({ anchor: f.properties.anchor || 'bottom' })
						.setLngLat(f.geometry.coordinates.slice())
						.setHTML(f.properties.html)
						.addTo(map);
				});

				<?php if (!$center): ?>
				// Ajuste de vista si no hay centro manual
				const bounds = <?= wp_json_encode($bounds) ?>;
				if (bounds.length) {
					const bbox = new mapboxgl.LngLatBounds();
					bounds.forEach(c => bbox.extend(c)); // c = [lng,lat]
					map.fitBounds(bbox, { padding: 50, animate: false });
				}
				<?php endif; ?>
			});
		});
		</script>
		<?php
	}
}
