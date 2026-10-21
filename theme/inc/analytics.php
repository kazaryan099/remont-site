<?php
/**
 * Яндекс Метрика. Номер счётчика — в настройщике («Сайт ремонтной компании → Аналитика»).
 * Счётчик выводится только на боевом сайте (WP_ENVIRONMENT_TYPE = production),
 * чтобы локальные заходы не попадали в статистику.
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

/** Номер счётчика или 0, если счётчик выключен. */
function rmt_metrika_id() {
	return absint( rmt_opt( 'metrika_id' ) );
}

add_action( 'wp_head', 'rmt_metrika_counter', 20 );
function rmt_metrika_counter() {
	$id = rmt_metrika_id();
	if ( ! $id || 'production' !== wp_get_environment_type() || is_customize_preview() ) {
		return;
	}
	?>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=<?php echo (int) $id; ?>', 'ym');

    ym(<?php echo (int) $id; ?>, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/<?php echo (int) $id; ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
	<?php
}
