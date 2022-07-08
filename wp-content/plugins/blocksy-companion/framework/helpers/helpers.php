<?php

function blc_empty_site_host_and_domain_for($url) {
	$parsed_url = parse_url(get_site_url());

	$to_replace = $parsed_url['scheme'] . '://' . $parsed_url['host'];

	if (isset($parsed_url['port'])) {
		$to_replace .= ':' . $parsed_url['port'];
	}

	return str_replace(
		$to_replace,
		'',
		$url
	);
}

function blc_get_ext($id) {
	return \Blocksy\Plugin::instance()->extensions->get($id);
}

if (! function_exists('blc_load_xml_file')) {
	function blc_load_xml_file($url, $useragent = '') {
		set_time_limit(300);

		if (ini_get('allow_url_fopen') && ini_get('allow_url_fopen') !== 'Off') {
			$context_options = [
				"ssl" => [
					"verify_peer"=>false,
					"verify_peer_name"=>false,
				]
			];

			if (! empty($useragent)) {
				$context_options['http'] = [
					'user_agent' => $useragent
				];
			}

			return file_get_contents(
				$url, false,
				stream_context_create($context_options)
			);
		} else if (function_exists('curl_init')) {
			$curl = curl_init($url);

			if (! empty($useragent)) {
				curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
			}

			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

			$result = curl_exec($curl);
			curl_close($curl);

			return $result;
		} else {
			throw new Exception("Can't load data.");
		}
	}
}

function blc_get_contacts_output($args = []) {
	$args = wp_parse_args($args, [
		'data' => [],
		'link_target' => 'no',
		'type' => 'rounded',
		'fill' => 'outline',
		'size' => '',
		'direction' => 'horizontal'
	]);

	$has_enabled_layer = false;

	foreach ($args['data'] as $single_layer) {
		if ($single_layer['enabled']) {
			$has_enabled_layer = true;
			break;
		}
	}

	if (! $has_enabled_layer) {
		return '';
	}

	$data_target = '';

	if ($args['link_target'] !== 'no') {
		$data_target = 'target="_blank"';
	}

	$custom_icon_defaults = [
		'address' => 'blc blc-map-pin',
		'phone' => 'blc blc-phone',
		'mobile' => 'blc blc-mobile-phone',
		'hours' => 'blc blc-clock',
		'fax' => 'blc blc-fax',
		'email' => 'blc blc-email',
		'website' => 'blc blc-globe',
	];

	$attr = [];

	// if ($args['type'] !== 'simple') {
		$attr['data-icons-type'] = $args['type'];
	// }

	if ($args['type'] !== 'simple' && ! empty($args['fill'])) {
		$attr['data-icons-type'] .= ':' . $args['fill'];
	}

	if (! empty($args['size'])) {
		$attr['data-icon-size'] = $args['size'];
	}

	$attr['data-items-direction'] = $args['direction'];

	ob_start(); ?>

	<ul <?php echo blocksy_attr_to_html($attr) ?>>
		<?php foreach ($args['data'] as $single_layer) { ?>
			<?php if (! $single_layer['enabled']) { continue; }?>
			<li>
				<?php 
					if (function_exists('blc_get_icon')) { 
						echo blc_get_icon([
							'icon_descriptor' => blocksy_akg(
								'icon',
								$single_layer,
								['icon' => $custom_icon_defaults[$single_layer['id']]]
							),
						]);
					}
				?>

				<div class="contact-info">
					<?php if (! empty(blocksy_akg('title', $single_layer, ''))) { ?>
						<span class="contact-title">
							<?php echo do_shortcode(blocksy_akg('title', $single_layer, '')) ?>
						</span>
					<?php } ?>

					<?php if (! empty(blocksy_akg('content', $single_layer, ''))) { ?>
						<span class="contact-text">
							<?php if (! empty(blocksy_akg('link', $single_layer, ''))) { ?>
								<a href="<?php echo blocksy_akg('link', $single_layer, '') ?>" <?php echo $data_target ?>>
							<?php } ?>

							<?php echo do_shortcode(blocksy_akg('content', $single_layer, '')) ?>

							<?php if (! empty(blocksy_akg('link', $single_layer, ''))) { ?>
								</a>
							<?php } ?>
						</span>
					<?php } ?>
				</div>
			</li>
		<?php } ?>
	</ul>

	<?php
	return ob_get_clean();
}
