<?php
/* Картка товару БУЛЯК (дизайн buliak-modern) — діє СКРІЗЬ (магазин, бестселери, пошук, related).
 * фото · назва · ціна/кг · орієнтовна порція · вибір к-сті · В кошик. Без опису/категорії. */
defined( 'ABSPATH' ) || exit;
global $product;
if ( empty( $product ) || ! $product->is_visible() ) { return; }

$id      = $product->get_id();
$link    = get_permalink( $id );
$best    = has_term( 'bestseller', 'product_tag', $id );
$portion = function_exists( 'blk_portion_label' ) ? blk_portion_label( $product ) : '';
$price   = function_exists( 'wc_price' ) ? wc_price( $product->get_price(), array( 'decimals' => 0 ) ) : $product->get_price();
?>
<li <?php wc_product_class( 'blk-card', $product ); ?>>
	<?php if ( $best ) : ?><span class="blk-hit">🔥 ХІТ</span><?php endif; ?>
	<a href="<?php echo esc_url( $link ); ?>" class="blk-card-media"><?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore ?></a>
	<div class="blk-card-body">
		<h3 class="blk-card-title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
		<div class="blk-card-meta">
			<span class="blk-card-price"><?php echo wp_kses_post( $price ); ?> <span class="blk-card-unit">/ кг</span></span>
			<?php if ( $portion ) : ?><span class="blk-card-portion"><?php echo esc_html( $portion ); ?></span><?php endif; ?>
		</div>
		<div class="blk-card-actions">
			<div class="blk-card-qty">
				<button type="button" class="blk-qd" aria-label="Менше">−</button>
				<span class="blk-qv" data-q="1">1 кг</span>
				<button type="button" class="blk-qi" aria-label="Більше">+</button>
			</div>
			<button type="button" class="blk-card-add" data-id="<?php echo esc_attr( $id ); ?>">В кошик</button>
		</div>
	</div>
</li>
