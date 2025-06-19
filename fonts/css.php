<?php

header("Content-type: text/css; charset: UTF-8");

require_once('../../../../wp-load.php');

$fonts = array();

foreach (scandir(get_template_directory().'/fonts') as $family) {

	if (!in_array($family, array('.', '..', 'css.php'))) {

		$fonts[$family] = array();

		foreach (scandir(get_template_directory().'/fonts/'.$family) as $variant) {

			if (!in_array($variant, array('.', '..'))) {

				$ext = pathinfo($variant)['extension'];

				$italic = false;

				if (strpos($variant, 'i')) {
					$italic = true;
				}

				$weight = intval(pathinfo($variant)['filename']);

				$fonts[$family][$weight.($italic ? 'i' : '')][$ext] = $variant;

			}

		}


	}

}

$out = '';

foreach ($fonts as $family => $variant) {

	foreach ($variant as $weight => $variant) {

		$italic = false;

		if (strpos($weight, 'i')) {

			$italic = true;

			$weight = intval($weight);

		}

		$out .= '@font-face {'."\n";
		$out .= '	font-family: \''.$family.'\';'."\n";
		$out .= '	font-display: swap;'."\n";

		if (isset($variant['eot'])) {
			$out .= '	src: url(\'../fonts/'.$family.'/'.$variant['eot'].'\');'."\n";
		}

		$i = 1;

		foreach ($variant as $ext => $name) {

			$out .= '	'.($i == 1 ? 'src:' : '	').' url(\'../fonts/'.$family.'/'.$name.($ext == 'eot' ? '?#iefix' : '').'\') format(\''.strtr($ext, array('eot' => 'embedded-opentype', 'ttf' => 'truetype')).'\')'.($i == count($variant) ? ';' : ',')."\n";

			$i++;

		}

		$out .= '	font-weight: '.$weight.';'."\n";
		$out .= '	font-style: '.($italic ? 'italic' : 'normal').';'."\n";
		$out .= '}'."\n";
		$out .= ''."\n";

	}

}

echo $out;

?>