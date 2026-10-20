<?php
/**
 * Секция с формой заявки.
 *
 * $args: title, text, type (тип ремонта по умолчанию), heading_tag
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

$rmt_types   = rmt_repair_type_options();
$rmt_preset  = $args['type'] ?? '';
$rmt_steps   = array_filter( array_map( 'trim', explode( "\n", (string) rmt_opt( 'form_steps' ) ) ) );
$rmt_privacy = get_privacy_policy_url();

// Результат отправки без JavaScript (после редиректа): только коды из URL, тексты — из темы.
$rmt_code     = isset( $_GET['lead'] ) ? sanitize_key( wp_unslash( $_GET['lead'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$rmt_messages = rmt_lead_messages();
$rmt_status   = isset( $rmt_messages[ $rmt_code ] ) ? $rmt_messages[ $rmt_code ] : '';
$rmt_fields   = array();
if ( 'invalid' === $rmt_code && isset( $_GET['lead_fields'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$rmt_fields = array_intersect_key( rmt_lead_field_messages(), array_flip( explode( ',', sanitize_text_field( wp_unslash( $_GET['lead_fields'] ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$rmt_error = function ( $field ) use ( $rmt_fields ) {
	printf(
		'<p class="field__error" id="lead-%1$s-error" data-error-for="%1$s"%2$s>%3$s</p>',
		esc_attr( $field ),
		isset( $rmt_fields[ $field ] ) ? '' : ' hidden',
		esc_html( $rmt_fields[ $field ] ?? '' )
	);
};
$rmt_invalid = fn( $field ) => isset( $rmt_fields[ $field ] ) ? ' aria-invalid="true"' : '';
?>
<section class="section lead-section" id="lead-form" aria-labelledby="lead-title">
	<div class="container lead-section__grid">
		<div class="lead-section__intro">
			<?php
			rmt_section_head(
				array(
					'title' => $args['title'] ?? rmt_opt( 'form_title' ),
					'text'  => $args['text'] ?? rmt_opt( 'form_text' ),
					'id'    => 'lead-title',
					'tag'   => $args['heading_tag'] ?? 'h2',
				)
			);
			?>
			<?php if ( $rmt_steps ) : ?>
				<ol class="num-steps">
					<?php foreach ( $rmt_steps as $rmt_step ) : ?>
						<li><?php echo esc_html( $rmt_step ); ?></li>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
			<p class="lead-section__phone">
				<?php esc_html_e( 'Или позвоните:', 'remont' ); ?>
				<a href="<?php echo esc_url( rmt_phone_href() ); ?>"><?php echo esc_html( rmt_opt( 'phone' ) ); ?></a>
			</p>
		</div>

		<form class="lead-form card" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate data-lead-form>
			<p class="lead-form__title"><?php esc_html_e( 'Заявка на расчёт', 'remont' ); ?></p>

			<div class="form-status<?php echo 'ok' === $rmt_code ? ' is-success' : ( $rmt_status ? ' is-error' : '' ); ?>" role="status" aria-live="polite" data-form-status<?php echo $rmt_status ? '' : ' hidden'; ?>>
				<?php echo esc_html( $rmt_status ); ?>
			</div>

			<input type="hidden" name="action" value="rmt_lead">
			<input type="hidden" name="rmt_ts" value="<?php echo esc_attr( rmt_lead_time_token() ); ?>">
			<input type="hidden" name="source" value="<?php echo esc_url( rmt_current_url() ); ?>">
			<input type="hidden" name="calc_summary" value="" data-calc-summary>
			<?php wp_nonce_field( 'rmt_lead', '_rmt_nonce', false ); ?>

			<div class="hp" aria-hidden="true">
				<label for="lead-company-site"><?php esc_html_e( 'Не заполняйте это поле', 'remont' ); ?></label>
				<input type="text" id="lead-company-site" name="company_site" tabindex="-1" autocomplete="off">
			</div>

			<div class="form-grid">
				<div class="field">
					<label for="lead-name"><?php esc_html_e( 'Имя', 'remont' ); ?> <span class="req" aria-hidden="true">*</span></label>
					<input class="input" type="text" id="lead-name" name="name" required minlength="2" maxlength="60" autocomplete="name" aria-describedby="lead-name-error"<?php echo $rmt_invalid( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php $rmt_error( 'name' ); ?>
				</div>
				<div class="field">
					<label for="lead-phone"><?php esc_html_e( 'Телефон', 'remont' ); ?> <span class="req" aria-hidden="true">*</span></label>
					<input class="input" type="tel" id="lead-phone" name="phone" required inputmode="tel" autocomplete="tel" placeholder="+7 ___ ___-__-__" aria-describedby="lead-phone-error"<?php echo $rmt_invalid( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php $rmt_error( 'phone' ); ?>
				</div>
				<div class="field">
					<label for="lead-type"><?php esc_html_e( 'Тип ремонта', 'remont' ); ?> <span class="req" aria-hidden="true">*</span></label>
					<select class="input" id="lead-type" name="repair_type" required aria-describedby="lead-repair_type-error" data-lead-type<?php echo $rmt_invalid( 'repair_type' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
						<option value=""><?php esc_html_e( 'Выберите', 'remont' ); ?></option>
						<?php foreach ( $rmt_types as $rmt_key => $rmt_label ) : ?>
							<option value="<?php echo esc_attr( $rmt_key ); ?>" <?php selected( $rmt_key, $rmt_preset ); ?>><?php echo esc_html( $rmt_label ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php $rmt_error( 'repair_type' ); ?>
				</div>
				<div class="field">
					<label for="lead-area"><?php esc_html_e( 'Площадь, м²', 'remont' ); ?></label>
					<input class="input" type="number" id="lead-area" name="area" min="1" max="2000" step="0.1" inputmode="decimal" aria-describedby="lead-area-error" data-lead-area<?php echo $rmt_invalid( 'area' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<?php $rmt_error( 'area' ); ?>
				</div>
				<div class="field field--full">
					<label for="lead-comment"><?php esc_html_e( 'Комментарий', 'remont' ); ?></label>
					<textarea class="input" id="lead-comment" name="comment" rows="3" maxlength="2000" placeholder="<?php esc_attr_e( 'Что нужно сделать, когда удобно созвониться', 'remont' ); ?>" aria-describedby="lead-comment-error" data-lead-comment<?php echo $rmt_invalid( 'comment' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>></textarea>
					<?php $rmt_error( 'comment' ); ?>
				</div>
			</div>

			<p class="calc-attached" hidden data-calc-attached></p>

			<div class="field field--check">
				<input type="checkbox" id="lead-consent" name="consent" value="1" required aria-describedby="lead-consent-error"<?php echo $rmt_invalid( 'consent' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<label for="lead-consent">
					<?php esc_html_e( 'Согласен на обработку персональных данных', 'remont' ); ?>
					<?php if ( $rmt_privacy ) : ?>
						<?php esc_html_e( 'в соответствии с', 'remont' ); ?> <a href="<?php echo esc_url( $rmt_privacy ); ?>" target="_blank"><?php esc_html_e( 'политикой конфиденциальности', 'remont' ); ?></a>
					<?php endif; ?>
				</label>
				<?php $rmt_error( 'consent' ); ?>
			</div>

			<button class="btn btn--block btn--lg" type="submit" data-submit><?php esc_html_e( 'Отправить заявку', 'remont' ); ?></button>
			<p class="lead-form__note"><?php esc_html_e( 'Данные используются только для связи с вами по заявке и не передаются третьим лицам.', 'remont' ); ?></p>
		</form>
	</div>
</section>
