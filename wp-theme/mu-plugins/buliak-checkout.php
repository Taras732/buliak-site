<?php
/* Plugin Name: Буляк Checkout
 * Description: Мінімальний checkout (телефон+НП+месенджер), метод «Передзамовлення», відправка замовлення в Telegram. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---- 1. Без доставки на сайті (отримувач платить НП окремо) ---- */
add_filter( 'woocommerce_cart_needs_shipping', '__return_false' );

/* ---- 2. Мінімальні поля checkout ---- */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	/* раніше збережені НП/месенджер -> автозаповнення (як стандартні поля) */
	$sess        = ( function_exists( 'WC' ) && WC()->session ) ? WC()->session : null;
	$saved_city   = $sess ? (string) $sess->get( 'blk_np_city', '' ) : '';
	$saved_branch = $sess ? (string) $sess->get( 'blk_np_branch', '' ) : '';
	$saved_msg    = $sess ? (string) $sess->get( 'blk_messenger', '' ) : '';

	$keep = array( 'billing_first_name', 'billing_last_name', 'billing_phone' );
	foreach ( array_keys( $fields['billing'] ) as $k ) {
		if ( ! in_array( $k, $keep, true ) ) { unset( $fields['billing'][ $k ] ); }
	}
	$fields['billing']['billing_last_name']['label']     = 'Прізвище';
	$fields['billing']['billing_last_name']['required']  = true;
	$fields['billing']['billing_last_name']['class']     = array( 'form-row-first' );
	$fields['billing']['billing_last_name']['priority']  = 10;
	$fields['billing']['billing_first_name']['label']    = 'Ім’я';
	$fields['billing']['billing_first_name']['required'] = true;
	$fields['billing']['billing_first_name']['class']    = array( 'form-row-last' );
	$fields['billing']['billing_first_name']['priority'] = 20;
	$fields['billing']['billing_phone']['label']    = 'Контактний телефон';
	$fields['billing']['billing_phone']['required'] = true;
	$fields['billing']['billing_phone']['type']     = 'tel';
	$fields['billing']['billing_phone']['class']    = array( 'form-row-wide' );
	$fields['billing']['billing_phone']['priority'] = 30;
	$fields['billing']['billing_phone']['placeholder'] = '+380XXXXXXXXX';
	$fields['billing']['billing_phone']['default']     = '+380';
	$fields['billing']['billing_phone']['custom_attributes'] = array( 'inputmode' => 'tel' );
	$fields['billing']['billing_np_city'] = array(
		'label' => 'Місто / населений пункт', 'required' => true,
		'class' => array( 'form-row-wide' ), 'priority' => 40,
		'custom_attributes' => array( 'autocomplete' => 'off' ),
		'default' => $saved_city,
	);
	$fields['billing']['billing_np_branch'] = array(
		'label' => 'Відділення / поштомат Нової Пошти', 'required' => true,
		'class' => array( 'form-row-wide' ), 'priority' => 50,
		'custom_attributes' => array( 'autocomplete' => 'off' ),
		'default' => $saved_branch,
	);
	$fields['billing']['billing_messenger'] = array(
		'type' => 'radio', 'label' => 'Зручний месенджер для зв’язку', 'required' => false,
		'class' => array( 'form-row-wide', 'blk-messenger' ), 'priority' => 60,
		'options' => array( 'Telegram' => 'Telegram', 'Viber' => 'Viber' ),
		'default' => $saved_msg ? $saved_msg : 'Telegram',
	);
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['placeholder'] = '';
	}
	return $fields;
}, 20 );

/* зберегти прізвище в meta замовлення теж (для НП) — first/last вже стандартні */

/* нотатка менеджера + іконки месенджера + вигляд-квитанція на checkout */
add_action( 'woocommerce_review_order_before_submit', function () {
	echo '<div class="blk-order-note"><strong>📋 Як це працює</strong>'
		. 'Менеджер зв’яжеться з вами для уточнення інформації, за потреби скоригуємо замовлення. Усе узгодимо особисто.</div>';
} );

add_action( 'wp_footer', function () {
	if ( ! ( function_exists( 'is_checkout' ) && is_checkout() ) ) { return; }
	?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var mf = document.getElementById('billing_messenger_field');
  if (mf) {
    var tg = '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M21.94 4.6 18.9 19.2c-.23 1.02-.84 1.27-1.7.79l-4.7-3.46-2.27 2.18c-.25.25-.46.46-.95.46l.34-4.78L18.5 6.3c.38-.34-.08-.53-.6-.19L6.9 13.18l-4.65-1.45c-1.01-.32-1.03-1.01.21-1.5L20.63 3.2c.84-.32 1.58.2 1.31 1.4z"/></svg>';
    var vb = '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.9 2 3 5.5 3 10c0 2.3 1.1 4.4 2.9 5.8-.1 1-.5 2.3-1.2 3.4-.2.3.1.7.4.5 1.9-.9 3.2-1.9 3.9-2.5.9.2 1.9.3 3 .3 5.1 0 9-3.5 9-8S17.1 2 12 2z"/></svg>';
    mf.querySelectorAll('.woocommerce-input-wrapper label').forEach(function (l) {
      var f = l.getAttribute('for') || '';
      if (/Telegram/.test(f)) { l.insertAdjacentHTML('afterbegin', tg); }
      if (/Viber/.test(f)) { l.insertAdjacentHTML('afterbegin', vb); }
    });
  }
});
</script>
	<?php
} );

/* ховаємо email-вимогу */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	unset( $fields['billing']['billing_email'] );
	return $fields;
}, 99 );

/* ---- 3. Зберегти кастомні поля в замовлення (HPOS-сумісно) ---- */
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	foreach ( array( 'billing_np_city', 'billing_np_branch', 'billing_messenger' ) as $k ) {
		if ( ! empty( $_POST[ $k ] ) ) {
			$order->update_meta_data( '_' . $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
		}
	}
	/* НП -> адреса доставки замовлення (щоб показувалось у блоці «Доставка») */
	$city   = ! empty( $_POST['billing_np_city'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_np_city'] ) ) : '';
	$branch = ! empty( $_POST['billing_np_branch'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_np_branch'] ) ) : '';
	$order->set_shipping_first_name( $order->get_billing_first_name() );
	$order->set_shipping_last_name( $order->get_billing_last_name() );
	$order->set_shipping_phone( $order->get_billing_phone() );
	$order->set_shipping_country( 'UA' );
	if ( $city ) { $order->set_shipping_city( $city ); }
	if ( $branch ) { $order->set_shipping_address_1( 'Нова Пошта: ' . $branch ); }
	/* запамʼятати НП/месенджер у сесії клієнта -> автозаповнення наступного разу */
	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->set( 'blk_np_city', $city );
		WC()->session->set( 'blk_np_branch', $branch );
		if ( ! empty( $_POST['billing_messenger'] ) ) {
			WC()->session->set( 'blk_messenger', sanitize_text_field( wp_unslash( $_POST['billing_messenger'] ) ) );
		}
	}
	/* пошту не зберігаємо й не використовуємо; замовлення гостьове (без привʼязки акаунта) */
	$order->set_billing_email( '' );
	$order->set_customer_id( 0 );
}, 99, 1 );

/* ---- 4. Показ в адмінці замовлення ---- */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	$msg   = $order->get_meta( '_billing_messenger' );
	$phone = $order->get_billing_phone();
	$digits = preg_replace( '/\D/', '', $phone );            // напр. 380931234567
	if ( strlen( $digits ) === 10 && $digits[0] === '0' ) { $digits = '38' . $digits; } // 0XX -> 380XX

	echo '<p><strong>Місто:</strong> ' . esc_html( $order->get_meta( '_billing_np_city' ) ) . '<br>';
	echo '<strong>Відділення НП:</strong> ' . esc_html( $order->get_meta( '_billing_np_branch' ) ) . '<br>';
	echo '<strong>Месенджер:</strong> ' . esc_html( $msg ?: '—' ) . '</p>';

	/* Швидкий контакт: відкрити чат у Telegram / Viber за номером */
	if ( $digits ) {
		$tg    = 'tg://resolve?phone=' . $digits;
		$viber = 'viber://chat?number=' . rawurlencode( '+' . $digits );
		$buttons = array();
		if ( $msg === 'viber' ) {
			$buttons[] = '<a class="button button-primary" href="' . esc_attr( $viber ) . '">💬 Написати у Viber</a>';
			$buttons[] = '<a class="button" href="' . esc_attr( $tg ) . '">Telegram</a>';
		} else {
			$buttons[] = '<a class="button button-primary" href="' . esc_attr( $tg ) . '">✈️ Написати в Telegram</a>';
			$buttons[] = '<a class="button" href="' . esc_attr( $viber ) . '">Viber</a>';
		}
		$buttons[] = '<a class="button" href="tel:+' . esc_attr( $digits ) . '">📞 Подзвонити</a>';
		echo '<p class="blk-contact-actions" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:4px">' . implode( '', $buttons ) . '</p>';
	}

	/* ---- Історія клієнта за номером телефону ---- */
	if ( $phone ) {
		$ids = wc_get_orders( array(
			'limit'          => 50,
			'billing_phone'  => $phone,
			'exclude'        => array( $order->get_id() ),
			'status'         => array_keys( wc_get_order_statuses() ),
			'return'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		$cnt = count( $ids );
		if ( $cnt ) {
			$sum  = 0;
			$last = '';
			$rows = array();
			foreach ( $ids as $i => $oid ) {
				$o = wc_get_order( $oid );
				if ( ! $o ) { continue; }
				$sum += (float) $o->get_total();
				$d = $o->get_date_created() ? $o->get_date_created()->date_i18n( 'd.m.Y' ) : '';
				if ( ! $last ) { $last = $d; }
				if ( $i < 5 ) {
					$rows[] = '<a href="' . esc_url( $o->get_edit_order_url() ) . '">#' . $o->get_id() . '</a> · '
						. esc_html( $d ) . ' · ' . wc_price( $o->get_total() ) . ' · '
						. esc_html( wc_get_order_status_name( $o->get_status() ) );
				}
			}
			echo '<div class="blk-cust-history" style="margin-top:10px;padding:10px 12px;border:1px solid #c3c4c7;border-radius:6px;background:#fff7e6">';
			echo '<strong>🔁 Постійний клієнт</strong><br>';
			echo 'Замовлень раніше: <b>' . (int) $cnt . '</b> · на суму <b>' . wp_kses_post( wc_price( $sum ) ) . '</b>'
				. ( $last ? ' · останнє ' . esc_html( $last ) : '' ) . '<br>';
			if ( $rows ) {
				echo '<span style="opacity:.8;font-size:12px">' . implode( '<br>', $rows ) . '</span>';
				if ( $cnt > 5 ) { echo '<br><span style="opacity:.6;font-size:12px">…та ще ' . ( $cnt - 5 ) . '</span>'; }
			}
			echo '</div>';
		} else {
			echo '<p class="blk-cust-history" style="margin-top:8px;opacity:.7">🆕 Новий клієнт (перше замовлення)</p>';
		}
	}
} );

/* ---- 5. Платіжний метод «Передзамовлення» (без онлайн-оплати поки) ---- */
add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	class Buliak_Preorder_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			$this->id                 = 'buliak_preorder';
			$this->method_title       = 'Передзамовлення';
			$this->method_description = 'Замовлення як передзамовлення; менеджер уточнює вагу/ціну.';
			$this->title              = 'Оформити замовлення';
			$this->description        = 'Це передзамовлення. Менеджер зв’яжеться у вашому месенджері, уточнить наявність, фактичну вагу й фінальну суму. Відправка лише Новою Поштою за 1–2 робочі дні після підтвердження (доставку оплачує отримувач).';
			$this->enabled            = 'yes';
			$this->has_fields         = false;
			$this->init_form_fields();
			$this->init_settings();
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$order->update_status( 'blk-new', 'Передзамовлення з сайту — очікує опрацювання.' );
			if ( WC()->cart ) { WC()->cart->empty_cart(); }
			return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
		}
	}
} );
add_filter( 'woocommerce_payment_gateways', function ( $g ) { $g[] = 'Buliak_Preorder_Gateway'; return $g; } );
/* лишити на checkout лише наш метод */
add_filter( 'woocommerce_available_payment_gateways', function ( $gws ) {
	if ( isset( $gws['buliak_preorder'] ) ) {
		return array( 'buliak_preorder' => $gws['buliak_preorder'] );
	}
	return $gws;
} );

/* ---- 7. Кошик: без купонів, чистий підсумок ---- */
add_filter( 'woocommerce_coupons_enabled', '__return_false' );
/* прибрати верхнє повідомлення "товар додано" (досить лічильника + тост) */
add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

/* "Total/Усього" -> "Сума замовлення" на кошику/checkout */
add_filter( 'gettext', function ( $translated, $text, $domain ) {
	if ( $domain !== 'woocommerce' ) { return $translated; }
	if ( is_cart() || is_checkout() ) {
		if ( $text === 'Total' ) { return 'Сума замовлення'; }
		if ( $text === 'Subtotal' ) { return 'Вартість'; }
		if ( $text === 'Billing details' ) { return 'Контакти та доставка'; }
		if ( $text === 'Additional information' ) { return 'Коментар до замовлення'; }
		if ( $text === 'Your order' ) { return 'Ваше замовлення'; }
		if ( $text === 'Order notes' ) { return 'Коментар до замовлення'; }
	}
	return $translated;
}, 20, 3 );

/* кнопка «Продовжити покупки» в кошику */
add_action( 'woocommerce_after_cart_table', function () {
	echo '<p class="blk-continue"><a class="button blk-continue-btn" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">← Продовжити</a></p>';
} );

/* згрупувати «Продовжити» + «Оформити» в один ряд */
add_action( 'wp_footer', function () {
	if ( ! ( function_exists( 'is_cart' ) && is_cart() ) ) { return; }
	?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var cont = document.querySelector('.blk-continue');
  var proceed = document.querySelector('.wc-proceed-to-checkout');
  if (!cont || !proceed) { return; }
  var row = document.createElement('div'); row.className = 'blk-cart-actions';
  proceed.parentNode.insertBefore(row, proceed);
  row.appendChild(cont);
  row.appendChild(proceed);
  var cb = row.querySelector('.checkout-button'); if (cb) { cb.textContent = 'Оформити →'; }
});
</script>
	<?php
} );

/* AJAX: плавне оновлення кількості в кошику (без перезавантаження/спінера) */
add_action( 'wp_ajax_blk_update_cart', 'blk_ajax_update_cart' );
add_action( 'wp_ajax_nopriv_blk_update_cart', 'blk_ajax_update_cart' );
function blk_ajax_update_cart() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) { wp_send_json( array( 'count' => 0 ) ); }
	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? floatval( str_replace( ',', '.', $_POST['qty'] ) ) : 0;
	$item = $key ? WC()->cart->get_cart_item( $key ) : false;
	if ( $item ) {
		if ( $qty > 0 ) { WC()->cart->set_quantity( $key, $qty, false ); }
		else { WC()->cart->remove_cart_item( $key ); }
	}
	WC()->cart->calculate_totals();
	$item = $key ? WC()->cart->get_cart_item( $key ) : false;
	wp_send_json( array(
		'line'    => $item ? WC()->cart->get_product_subtotal( $item['data'], $item['quantity'] ) : '',
		'total'   => WC()->cart->get_total(),
		'count'   => count( WC()->cart->get_cart() ),
		'removed' => empty( $item ),
	) );
}

/* нотатка про фактичну вагу під підсумком */
function buliak_weight_note() {
	echo '<tr class="blk-weight-note"><td colspan="2" style="padding-top:6px"><small style="opacity:.8">Сума орієнтовна — фінальна може скоригуватись залежно від фактичної ваги продукції. Менеджер уточнить при підтвердженні.</small></td></tr>';
}
add_action( 'woocommerce_cart_totals_after_order_total', 'buliak_weight_note' );
add_action( 'woocommerce_review_order_after_order_total', 'buliak_weight_note' );

/* сховати рядок "Проміжний підсумок" (доставки нема -> = сумі замовлення) */
add_action( 'wp_head', function () {
	if ( ! ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) ) { return; }
	echo '<style>.cart_totals .cart-subtotal, .woocommerce-checkout-review-order-table .cart-subtotal{display:none!important;}'
		. '.cart_totals > h2{display:none!important;}'
		. '.cart_totals{border:0!important;background:none!important;}'
		. 'button[name="update_cart"], input[name="update_cart"]{display:none!important;}'
		. '.woocommerce-cart .woocommerce-message{display:none!important;}'
		. '.woocommerce-cart-form table.cart th, .woocommerce-cart-form table.cart td{text-align:center;vertical-align:middle;}'
		. '.woocommerce-cart-form table.cart td.product-name, .woocommerce-cart-form table.cart th.product-name{text-align:left;}'
		. '.woocommerce-cart .blockUI.blockOverlay, .woocommerce-cart .blockUI{display:none!important;opacity:0!important;}'
		. '.blk-weight-note td::before,tr.blk-weight-note td::before{content:none!important;display:none!important;}'
		/* рівна суцільна лінія між рядками підсумку (замість зміщеного пунктиру) */
		. '.woocommerce-checkout-review-order-table td,.woocommerce-checkout-review-order-table th,'
		. '.woocommerce-checkout-review-order-table tr{border-bottom:1px solid rgba(224,181,87,.14)!important;border-top:0!important;}'
		. '.woocommerce-checkout-review-order-table .cart_item td{border-bottom:1px solid rgba(224,181,87,.10)!important;}'
		. '.blk-continue{margin-top:16px;}'
		. '.blk-continue-btn{background:transparent!important;color:#e0b557!important;border:1px solid rgba(224,181,87,.4)!important;border-radius:999px;padding:11px 18px;font-weight:700;}'
		. '.blk-continue-btn:hover{background:rgba(224,181,87,.12)!important;color:#e0b557!important;}'
		/* помітна кнопка видалення замість дрібного хрестика */
		. '.woocommerce-cart-form .product-remove{text-align:center!important;}'
		. '.woocommerce-cart-form .product-remove a.remove{background:#b00020!important;color:#fff!important;width:auto!important;height:auto!important;line-height:1!important;font-size:.95rem!important;border-radius:999px;padding:8px 15px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;}'
		. '.woocommerce-cart-form .product-remove a.remove::after{content:"Прибрати";font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;}'
		. '.woocommerce-cart-form .product-remove a.remove:hover{background:#8a0019!important;color:#fff!important;}'
		/* дві дії поруч (Продовжити / Оформити) */
		. '.blk-cart-actions{display:flex;gap:10px;margin:18px 0 4px;flex-wrap:nowrap;}'
		. '.blk-cart-actions>.blk-continue,.blk-cart-actions>.wc-proceed-to-checkout{margin:0!important;flex:1 1 0;padding:0!important;}'
		. '.blk-cart-actions .blk-continue-btn,.blk-cart-actions .checkout-button{display:flex!important;width:100%!important;margin:0!important;text-align:center;justify-content:center;font-size:.74rem!important;padding:13px 8px!important;white-space:nowrap;box-sizing:border-box;}'
		. '.blk-cart-actions .blk-continue-btn{font-size:.7rem!important;}'
		/* === Мобільний кошик: компактні картки замість стек-таблиці === */
		. '@media(max-width:600px){'
		. '.woocommerce-cart-form table.cart thead{display:none!important;}'
		. '.woocommerce-cart-form table.cart,.woocommerce-cart-form table.cart tbody{display:block!important;width:100%!important;}'
		. '.woocommerce-cart-form table.cart tbody tr.cart_item{display:grid!important;grid-template-columns:74px 1fr auto;grid-template-areas:"img name remove" "img price price" "img qty sub";gap:6px 12px;align-items:center;padding:12px 14px;margin-bottom:12px;background:#1d1917!important;border:1px solid rgba(224,181,87,.12)!important;border-radius:14px;}'
		. '.woocommerce-cart-form table.cart tbody tr.cart_item td{display:block!important;border:0!important;padding:0!important;text-align:left!important;width:auto!important;}'
		. '.woocommerce-cart-form table.cart tbody td::before{display:none!important;}'
		. 'td.product-thumbnail{grid-area:img!important;}'
		. 'td.product-thumbnail img{width:74px!important;height:74px!important;border-radius:10px;object-fit:cover;margin:0!important;}'
		. 'td.product-name{grid-area:name!important;font-weight:700;font-size:.95rem;align-self:end;line-height:1.25;}'
		. 'td.product-price{grid-area:price!important;opacity:.7;font-size:.82rem;align-self:start;}'
		. 'td.product-quantity{grid-area:qty!important;}'
		. 'td.product-subtotal{grid-area:sub!important;text-align:right!important;color:#e0b557!important;font-weight:700;font-size:1rem;}'
		. 'td.product-remove{grid-area:remove!important;text-align:right!important;align-self:start;}'
		. 'td.product-remove a.remove::after{content:none!important;}'
		. 'td.product-remove a.remove{padding:5px 10px!important;font-size:1rem!important;}'
		. '}</style>';
} );

/* ---- 8. Кількість у КІЛОГРАМАХ (дробова) ---- */
add_action( 'init', function () {
	remove_filter( 'woocommerce_stock_amount', 'intval' );
	add_filter( 'woocommerce_stock_amount', 'floatval' );
}, 20 );
add_filter( 'woocommerce_quantity_input_args', function ( $args, $product ) {
	$args['step']      = 0.25;
	$args['min_value'] = 0.25;
	if ( empty( $args['input_value'] ) ) { $args['input_value'] = 1; }
	return $args;
}, 10, 2 );
/* при додаванні з картки магазину (ajax, без поля) — за замовчуванням 1 кг */
add_filter( 'woocommerce_add_to_cart_quantity', function ( $qty ) { return $qty > 0 ? $qty : 1; } );

/* підпис "кг" біля кількості + заспокійлива нотатка на сторінці товару */
add_action( 'woocommerce_before_add_to_cart_quantity', function () {
	echo '<span class="blk-qty-unit">Скільки кг:</span>';
} );
add_action( 'woocommerce_single_product_summary', function () {
	echo '<div class="blk-order-note">'
		. '<strong>📋 Як це працює</strong>'
		. 'Менеджер зв’яжеться з вами для уточнення інформації, за потреби скоригуємо замовлення. Усе узгодимо особисто.'
		. '</div>';
}, 35 );

/* CSS для кг-підпису й підсвіченої нотатки (на сторінці товару) */
add_action( 'wp_head', function () {
	echo '<style>'
		. '.blk-qty-unit{display:block;font-size:.85rem;opacity:.85;margin-bottom:4px;font-weight:600;}'
		. '.blk-order-note{font-size:.9rem;line-height:1.55;margin-top:18px;padding:13px 16px;'
		. 'background:rgba(224,181,87,.08);border:1px solid rgba(224,181,87,.30);border-radius:10px;}'
		. '.blk-order-note strong{display:block;margin-bottom:4px;color:#e0b557;font-size:.95rem;letter-spacing:.02em;}'
		. '/* сторінка подяки */'
		. '.blk-thankyou{text-align:center;max-width:560px;margin:20px auto;padding:10px;}'
		. '.blk-ty-emoji{font-size:3rem;line-height:1;}'
		. ".blk-ty-title{font-family:'Unbounded',sans-serif;font-size:1.6rem;font-weight:800;margin:10px 0 4px;color:#f3e9d6;}"
		. '.blk-ty-num{opacity:.85;margin-bottom:18px;color:#f3e9d6;}'
		. '.blk-ty-actions{margin-top:18px;}'
		. 'body.woocommerce-order-received .entry-title,body.woocommerce-order-received .ast-archive-title,body.woocommerce-order-received .entry-header,body.woocommerce-order-received .ast-single-post-title{display:none!important;}'
		. '/* месенджер іконками */'
		. '.blk-messenger input[type=radio]{position:absolute;opacity:0;width:0;height:0;}'
		. '.blk-messenger .woocommerce-input-wrapper{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;}'
		. '.blk-messenger label[for^="billing_messenger_"]{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border:1px solid rgba(224,181,87,.35);border-radius:999px;cursor:pointer;font-weight:700;transition:.15s;}'
		. '.blk-messenger input:checked + label{background:#b00020;color:#fff;border-color:#b00020;}'
		. '/* вигляд-квитанція */'
		. '#order_review_heading{font-family:\'Unbounded\',sans-serif;font-size:1.3rem;margin:10px 0 16px;}'
		. '.woocommerce-checkout-review-order-table th,.woocommerce-checkout-review-order-table td{padding:10px 6px;border-bottom:1px dashed rgba(224,181,87,.22);}'
		. '.woocommerce-checkout-review-order-table .order-total td,.woocommerce-checkout-review-order-table .order-total th{border-bottom:0;font-size:1.1rem;padding-top:14px;}'
		. '.woocommerce-checkout #payment{background:transparent!important;border:0!important;}'
		. '.woocommerce-checkout #payment .wc_payment_methods, .woocommerce-checkout #payment .payment_box{display:none!important;}'
		. '/* заголовки квитанції — світлі/читабельні */'
		. '#order_review_heading,.woocommerce-checkout h3,.woocommerce-billing-fields h3,.woocommerce-additional-fields h3,.woocommerce-shipping-fields h3,#order_review h3{color:#f3e9d6!important;}'
		. '#order_review_heading{border:0!important;background:none!important;box-shadow:none!important;padding:0!important;margin:0 0 14px!important;}'
		. '.woocommerce-checkout #order_review{background:transparent!important;border:0!important;padding:0!important;}'
		. '.woocommerce-checkout-review-order-table{border:0!important;}'
		. '.woocommerce-additional-fields > h3{display:none!important;}'
		. '.woocommerce-checkout .optional{display:none!important;}'
		. '#place_order:disabled,#place_order.blk-btn-disabled{opacity:.45!important;cursor:not-allowed!important;pointer-events:none;}'
		. '.blk-gate-hint{text-align:center;font-size:.85rem;opacity:.7;margin-top:10px;}'
		. '.woocommerce-checkout-review-order-table .order-total th{background:transparent!important;color:#e0b557!important;}'
		. '.woocommerce-checkout-review-order-table .order-total td{color:#e0b557!important;}'
		. '/* НП випадайки */'
		. '#billing_np_city_field,#billing_np_branch_field{position:relative;}'
		. '.blk-np-dd{position:absolute;z-index:60;left:0;right:0;top:100%;margin-top:3px;background:#1d1917;border:1px solid rgba(224,181,87,.3);border-radius:8px;max-height:260px;overflow:auto;list-style:none;padding:4px 0;display:none;box-shadow:0 12px 30px rgba(0,0,0,.45);}'
		. '.blk-np-dd.show{display:block;}'
		. '.blk-np-dd li{padding:9px 14px;cursor:pointer;font-size:.92rem;}'
		. '.blk-np-dd li:hover{background:rgba(224,181,87,.14);}'
		. '/* акуратний лоадер замість дефолтного блок-оверлею */'
		. '.woocommerce .blockUI.blockOverlay{background:rgba(15,13,12,.55)!important;opacity:1!important;}'
		. '.woocommerce .blockUI.blockOverlay::before{content:""!important;display:block;position:absolute;top:50%;left:50%;width:46px;height:46px;margin:-23px 0 0 -23px;border:4px solid rgba(224,181,87,.25);border-top-color:#b00020;border-radius:50%;background:none!important;animation:blk-spin .8s linear infinite;}'
		. '@keyframes blk-spin{to{transform:rotate(360deg);}}'
		. '</style>';
} );

/* ---- 9. Поле «кг» + кнопка на картках магазину (ЗАМІНА loop add-to-cart -> без дублю) ---- */
add_filter( 'woocommerce_loop_add_to_cart_link', function ( $html, $product ) {
	if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) { return $html; }
	ob_start();
	echo '<form class="cart blk-loop-form" action="' . esc_url( $product->get_permalink() ) . '" method="post">';
	echo '<div class="blk-loop-qty">';
	woocommerce_quantity_input( array( 'min_value' => 0.25, 'step' => 0.25, 'input_value' => 1 ), $product );
	echo '</div>';
	echo '<button type="submit" name="add-to-cart" value="' . esc_attr( $product->get_id() ) . '" class="button add_to_cart_button">Додати в кошик</button>';
	echo '</form>';
	return ob_get_clean();
}, 10, 2 );

/* ---- 10. Порожній кошик: прибрати чужорідні картки + стилізувати ---- */
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_filter( 'wc_empty_cart_message', function () {
	return 'Тут поки порожньо. Обери щось смачне в меню — і повертайся 🔥';
} );
add_action( 'wp_head', function () {
	if ( ! ( function_exists( 'is_cart' ) && is_cart() ) ) { return; }
	echo '<style>'
		. '.blk-empty-cart{text-align:center;padding:50px 16px 30px;}'
		. '.blk-empty-emoji{font-size:3rem;line-height:1;}'
		. ".blk-empty-title{font-family:'Unbounded',sans-serif;font-size:1.6rem;font-weight:800;margin:12px 0 4px;}"
		. '.blk-empty-sub{opacity:.75;margin-bottom:22px;}'
		. '.blk-empty-btn{background:#b00020!important;color:#fff!important;border-radius:999px;padding:14px 32px;font-weight:700;display:inline-flex;}'
		. '</style>';
} );

/* ---- НП: пошук міста + відділення через API (без плагіна) ---- */
function blk_np_request( $method, $props ) {
	$key = get_option( 'buliak_np_key' );
	if ( ! $key ) { return array(); }
	$resp = wp_remote_post( 'https://api.novaposhta.ua/v2.0/json/', array(
		'timeout' => 20,
		'headers' => array( 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode( array( 'apiKey' => $key, 'modelName' => 'Address', 'calledMethod' => $method, 'methodProperties' => $props ) ),
	) );
	if ( is_wp_error( $resp ) ) { return array(); }
	$d = json_decode( wp_remote_retrieve_body( $resp ), true );
	return isset( $d['data'] ) ? $d['data'] : array();
}
add_action( 'wp_ajax_blk_np_city', 'blk_np_city' );
add_action( 'wp_ajax_nopriv_blk_np_city', 'blk_np_city' );
function blk_np_city() {
	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	if ( mb_strlen( $q ) < 2 ) { wp_send_json( array() ); }
	$data = blk_np_request( 'searchSettlements', array( 'CityName' => $q, 'Limit' => '20' ) );
	$out = array();
	if ( ! empty( $data[0]['Addresses'] ) ) {
		foreach ( $data[0]['Addresses'] as $a ) {
			if ( ! empty( $a['DeliveryCity'] ) ) { $out[] = array( 'ref' => $a['DeliveryCity'], 'name' => $a['Present'] ); }
		}
	}
	wp_send_json( $out );
}
add_action( 'wp_ajax_blk_np_wh', 'blk_np_wh' );
add_action( 'wp_ajax_nopriv_blk_np_wh', 'blk_np_wh' );
function blk_np_wh() {
	$ref = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : '';
	if ( ! $ref ) { wp_send_json( array() ); }
	$data = blk_np_request( 'getWarehouses', array( 'CityRef' => $ref ) );
	$out = array();
	foreach ( $data as $w ) {
		if ( ! empty( $w['Description'] ) ) {
			$out[] = array( 'num' => isset( $w['Number'] ) ? $w['Number'] : '', 'desc' => $w['Description'] );
		}
	}
	wp_send_json( $out );
}
add_action( 'wp_footer', function () {
	if ( ! ( function_exists( 'is_checkout' ) && is_checkout() ) ) { return; }
	?>
<script>
(function () {
function blkNpInit() {
  var AJ = '/wp-admin/admin-ajax.php';
  var cityInput = document.getElementById('billing_np_city');
  var brInput = document.getElementById('billing_np_branch');
  if (!cityInput || !brInput) { return; }
  var cityField = document.getElementById('billing_np_city_field');
  var brField = document.getElementById('billing_np_branch_field');
  var cityRef = '', whCache = [], t;
  function dd(f){ var u=f.querySelector('ul.blk-np-dd'); if(!u){u=document.createElement('ul');u.className='blk-np-dd';f.appendChild(u);} return u; }
  function render(u,items,pick){ u.innerHTML=''; if(!items.length){u.classList.remove('show');return;} items.slice(0,40).forEach(function(it){ var li=document.createElement('li'); li.textContent=it.name||it; li.addEventListener('mousedown',function(e){e.preventDefault();pick(it);u.classList.remove('show');}); u.appendChild(li); }); u.classList.add('show'); }
  cityInput.setAttribute('autocomplete','off'); brInput.setAttribute('autocomplete','off');
  var popular = ['Львів','Київ','Дніпро','Одеса','Харків','Вінниця','Тернопіль','Івано-Франківськ','Рівне','Луцьк'];
  function showPopular(){ render(dd(cityField), popular, function(name){ resolveCity(name); }); }
  function resolveCity(name){ fetch(AJ+'?action=blk_np_city&q='+encodeURIComponent(name)).then(function(r){return r.json();}).then(function(list){ if(list&&list.length){ var it=list[0]; cityInput.value=it.name; cityRef=it.ref; brInput.value=''; whCache=[]; loadAllWh(); } }); }
  cityInput.addEventListener('focus', function(){ if(cityInput.value.trim()===''){ showPopular(); } });
  cityInput.addEventListener('input', function(){ clearTimeout(t); cityRef=''; whCache=[]; var q=cityInput.value.trim(); if(q===''){ showPopular(); return; } if(q.length<2){ return; } t=setTimeout(function(){ fetch(AJ+'?action=blk_np_city&q='+encodeURIComponent(q)).then(function(r){return r.json();}).then(function(list){ render(dd(cityField), list, function(it){ cityInput.value=it.name; cityRef=it.ref; brInput.value=''; whCache=[]; loadAllWh(); }); }); },300); });
  function loadAllWh(){ if(!cityRef){return;} fetch(AJ+'?action=blk_np_wh&ref='+encodeURIComponent(cityRef)).then(function(r){return r.json();}).then(function(list){ whCache=list||[]; filterWh(); }); }
  function filterWh(){ var q=brInput.value.trim().toLowerCase(); var items=whCache; if(q){ items=whCache.filter(function(w){ var n=(w.num||'').toLowerCase(), d=(w.desc||'').toLowerCase(); return n.indexOf(q)===0 || d.indexOf(q)>-1; }); items=items.slice(0).sort(function(a,b){ return ((a.num||'').toLowerCase().indexOf(q)===0?0:1)-((b.num||'').toLowerCase().indexOf(q)===0?0:1); }); } render(dd(brField), items.slice(0,40).map(function(w){return w.desc;}), function(name){ brInput.value=name; }); }
  brInput.addEventListener('focus', function(){ if(cityRef && !whCache.length){ loadAllWh(); } else { filterWh(); } });
  brInput.addEventListener('input', filterWh);
  document.addEventListener('click', function(e){ if(!cityField.contains(e.target)){dd(cityField).classList.remove('show');} if(!brField.contains(e.target)){dd(brField).classList.remove('show');} });
}
function blkGate() {
  var req = ['billing_last_name','billing_first_name','billing_phone','billing_np_city','billing_np_branch'];
  function check(){
    var btn = document.getElementById('place_order');
    if (!btn) { return; }
    var ok = req.every(function(id){ var el=document.getElementById(id); if(!el){return false;} var v=el.value.trim(); if(id==='billing_phone'){ return v.replace(/\D/g,'').length>=12; } return v!==''; });
    btn.disabled = !ok; btn.classList.toggle('blk-btn-disabled', !ok);
    var wrap = btn.parentNode, hint = wrap ? wrap.querySelector('.blk-gate-hint') : null;
    if (!hint && wrap) { hint=document.createElement('div'); hint.className='blk-gate-hint'; hint.textContent='Заповніть обов’язкові поля, щоб підтвердити замовлення.'; wrap.appendChild(hint); }
    if (hint) { hint.style.display = ok ? 'none' : 'block'; }
  }
  document.addEventListener('input', check, true);
  document.addEventListener('change', check, true);
  if (window.jQuery) { jQuery(document.body).on('updated_checkout', check); }
  setInterval(check, 700);
  check();
}
function blkPhone(){
  var p = document.getElementById('billing_phone');
  if (!p) { return; }
  function fmt(){ var d = p.value.replace(/\D/g,'').replace(/^380/,'').replace(/^0+/,'').slice(0,9); p.value = '+380' + d; }
  if (!p.value || p.value.indexOf('+380') !== 0) { fmt(); }
  p.addEventListener('input', fmt);
  p.addEventListener('focus', function(){ if (!p.value) { p.value = '+380'; } });
}
function blkRun(){ blkNpInit(); blkGate(); blkPhone(); }
if (document.readyState !== 'loading') { blkRun(); } else { document.addEventListener('DOMContentLoaded', blkRun); }
})();
</script>
	<?php
} );

/* ---- 6. Відправка замовлення в Telegram ---- */
function buliak_tg_notify( $order_id ) {
	$token = get_option( 'buliak_tg_token' );
	$chat  = get_option( 'buliak_tg_chat' );
	if ( ! $token || ! $chat ) { return; }
	$order = wc_get_order( $order_id );
	if ( ! $order ) { return; }
	if ( $order->get_meta( '_buliak_tg_sent' ) ) { return; }

	$L   = array();
	$L[] = '🥩 НОВЕ ЗАМОВЛЕННЯ #' . $order->get_order_number();
	$L[] = '';
	$L[] = '👤 ' . $order->get_billing_first_name();
	$L[] = '📞 ' . $order->get_billing_phone();
	$L[] = '💬 ' . $order->get_meta( '_billing_messenger' );
	$L[] = '🏙 ' . $order->get_meta( '_billing_np_city' );
	$L[] = '📦 ' . $order->get_meta( '_billing_np_branch' );
	$L[] = '';
	$L[] = '🧾 Товари:';
	foreach ( $order->get_items() as $it ) {
		$L[] = '• ' . $it->get_name() . ' — ' . $it->get_quantity() . ' × ' . $it->get_total() . ' ₴';
	}
	$L[] = '';
	$L[] = '💰 Орієнтовна сума: ' . $order->get_total() . ' ₴  (фінал — за фактичною вагою)';
	if ( $order->get_customer_note() ) { $L[] = '📝 ' . $order->get_customer_note(); }

	$resp = wp_remote_post( "https://api.telegram.org/bot{$token}/sendMessage", array(
		'timeout' => 15,
		'body'    => array( 'chat_id' => $chat, 'text' => implode( "\n", $L ), 'disable_web_page_preview' => true ),
	) );
	if ( ! is_wp_error( $resp ) ) {
		$order->update_meta_data( '_buliak_tg_sent', 1 );
		$order->save();
	}
}
add_action( 'woocommerce_checkout_order_processed', 'buliak_tg_notify', 20, 1 );
add_action( 'woocommerce_store_api_checkout_order_processed', 'buliak_tg_notify', 20, 1 );
