<?php
/**
 * Калькулятор предварительной стоимости: тариф ремонта + площадь.
 *
 * $args: type (ключ тарифа по умолчанию), title, text, heading_tag
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_s    = rmt_calc_settings();
$rmt_keys = wp_list_pluck( $rmt_s['types'], 'key' );
$rmt_type = $args['type'] ?? '';
$rmt_type = in_array( $rmt_type, $rmt_keys, true ) ? $rmt_type : ( $rmt_keys[1] ?? $rmt_keys[0] );
$rmt_area = (int) $rmt_s['default_area'];
$rmt_json = array(
	'types'   => array_map(
		fn( $t ) => array(
			'key'      => $t['key'],
			'label'    => $t['label'],
			'subtitle' => $t['subtitle'],
			'rate'     => (float) $t['rate'],
			'includes' => rmt_calc_includes( $t ),
		),
		$rmt_s['types']
	),
	'minArea' => (int) $rmt_s['min_area'],
	'maxArea' => (int) $rmt_s['max_area'],
	'demo'    => (bool) $rmt_s['demo'],
);
wp_enqueue_script( 'rmt-calculator' );
?>
<section class="section section--paper calc-section" id="calculator" aria-labelledby="calc-title">
	<div class="container">
		<?php
		rmt_section_head(
			array(
				'kicker' => rmt_opt( 'calc_kicker' ),
				'title'  => $args['title'] ?? rmt_opt( 'calc_title' ),
				'text'   => $args['text'] ?? rmt_opt( 'calc_text' ),
				'id'     => 'calc-title',
				'tag'    => $args['heading_tag'] ?? 'h2',
			)
		);
		?>

		<div class="calc" data-calc>
			<script type="application/json" data-calc-settings><?php echo wp_json_encode( $rmt_json, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>

			<form class="calc__form" onsubmit="return false" aria-label="<?php esc_attr_e( 'Параметры расчёта', 'remont' ); ?>">
				<fieldset class="calc__row">
					<legend class="calc__label"><span class="display"><?php esc_html_e( 'Тип ремонта', 'remont' ); ?></span></legend>
					<div class="tariffs">
						<?php foreach ( $rmt_s['types'] as $rmt_t ) : ?>
							<label class="tariff">
								<input type="radio" name="calc_type" value="<?php echo esc_attr( $rmt_t['key'] ); ?>" <?php checked( $rmt_t['key'], $rmt_type ); ?>>
								<span class="tariff__body">
									<span class="tariff__name"><?php echo esc_html( $rmt_t['label'] ); ?></span>
									<span class="tariff__price"><?php echo esc_html( sprintf( __( '%s / м²', 'remont' ), rmt_money( $rmt_t['rate'] ) ) ); ?></span>
									<?php if ( $rmt_t['subtitle'] ) : ?>
										<span class="tariff__sub"><?php echo esc_html( $rmt_t['subtitle'] ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<div class="calc__row">
					<div class="calc__label">
						<label class="display" for="calc-area"><?php esc_html_e( 'Площадь', 'remont' ); ?></label>
						<span class="calc__hint"><?php echo esc_html( sprintf( __( 'от %1$d до %2$d м²', 'remont' ), $rmt_s['min_area'], $rmt_s['max_area'] ) ); ?></span>
					</div>
					<div class="calc__area">
						<input class="range" type="range" id="calc-area-range" min="<?php echo esc_attr( $rmt_s['min_area'] ); ?>" max="<?php echo esc_attr( $rmt_s['max_area'] ); ?>" step="1" value="<?php echo esc_attr( $rmt_area ); ?>" aria-label="<?php esc_attr_e( 'Площадь, ползунок', 'remont' ); ?>" data-calc-range>
						<div class="calc__area-input">
							<input class="input" type="number" inputmode="decimal" id="calc-area" min="<?php echo esc_attr( $rmt_s['min_area'] ); ?>" max="<?php echo esc_attr( $rmt_s['max_area'] ); ?>" step="1" value="<?php echo esc_attr( $rmt_area ); ?>" data-calc-area>
							<span aria-hidden="true">м²</span>
						</div>
					</div>
				</div>

				<div class="calc__row calc__includes-row">
					<div class="calc__label">
						<span class="display" id="calc-includes-title"><?php esc_html_e( 'Состав работ', 'remont' ); ?></span>
						<span class="calc__hint" data-calc-includes-tariff></span>
					</div>
					<ul class="check-list calc__includes" role="list" aria-labelledby="calc-includes-title" data-calc-includes>
						<?php
						$rmt_current = $rmt_s['types'][ array_search( $rmt_type, $rmt_keys, true ) ];
						foreach ( rmt_calc_includes( $rmt_current ) as $rmt_line ) :
							?>
							<li><?php echo rmt_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $rmt_line ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</form>

			<aside class="calc__summary" aria-labelledby="calc-summary-title">
				<p class="calc__summary-kicker" id="calc-summary-title"><?php esc_html_e( 'Предварительная стоимость', 'remont' ); ?></p>
				<p class="calc__total" aria-live="polite" aria-atomic="true" data-calc-total>—</p>
				<dl class="calc__lines">
					<div><dt><?php esc_html_e( 'Тариф', 'remont' ); ?></dt><dd data-calc-tariff>—</dd></div>
					<div><dt><?php esc_html_e( 'Площадь', 'remont' ); ?></dt><dd data-calc-area-out>—</dd></div>
					<div><dt><?php esc_html_e( 'Ставка', 'remont' ); ?></dt><dd data-calc-rate>—</dd></div>
				</dl>
				<p class="calc__note">
					<?php esc_html_e( 'Это ориентир, а не смета: точная стоимость считается после замера и выбора материалов.', 'remont' ); ?>
					<?php if ( $rmt_s['demo'] ) : ?>
						<strong class="calc__demo"><?php esc_html_e( 'Сейчас используются демонстрационные ставки.', 'remont' ); ?></strong>
					<?php endif; ?>
				</p>
				<a class="btn btn--block" href="#lead-form" data-calc-send><?php esc_html_e( 'Передать расчёт в заявку', 'remont' ); ?></a>
				<noscript><p class="calc__note"><?php esc_html_e( 'Для расчёта включите JavaScript или оставьте заявку — посчитаем вручную.', 'remont' ); ?></p></noscript>
			</aside>
		</div>
	</div>
</section>
