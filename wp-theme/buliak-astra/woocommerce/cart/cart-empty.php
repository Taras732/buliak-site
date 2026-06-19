<?php
/**
 * Empty cart page — Буляк (чистий бренд-варіант, без «New in store»).
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="blk-empty-cart">
	<div class="blk-empty-emoji">🥩</div>
	<p class="blk-empty-title">Тут поки порожньо</p>
	<p class="blk-empty-sub">Обери щось смачне серед товарів — і повертайся.</p>
	<p class="blk-empty-actions">
		<a class="button blk-empty-btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">До магазину →</a>
	</p>
</div>
