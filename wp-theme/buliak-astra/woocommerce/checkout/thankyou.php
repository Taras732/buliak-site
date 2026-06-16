<?php
/**
 * Thank you / Order received — Буляк (мінімальна квитанція: № + дата + що далі).
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="blk-thankyou">
	<?php if ( $order ) : ?>
		<div class="blk-ty-emoji">🥩</div>
		<h2 class="blk-ty-title">Дякуємо! Замовлення прийнято</h2>
		<p class="blk-ty-num">Замовлення <strong>№<?php echo esc_html( $order->get_order_number() ); ?></strong> · <?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'd.m.Y H:i' ) ); ?></p>

		<div class="blk-order-note"><strong>📋 Що далі</strong>Менеджер зв’яжеться з вами для уточнення інформації, за потреби скоригуємо замовлення. Усе узгодимо особисто.</div>

		<p class="blk-ty-actions"><a class="button blk-empty-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">До магазину →</a></p>
	<?php else : ?>
		<h2 class="blk-ty-title">Дякуємо! Замовлення прийнято</h2>
		<p>Менеджер невдовзі з вами зв’яжеться.</p>
	<?php endif; ?>
</div>
