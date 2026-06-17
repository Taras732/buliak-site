<?php
/* Plugin Name: Буляк Roles Hardening
 * Description: Least-privilege для менеджерів (Shop Manager): без сторінок/кастомайзера/налаштувань WC.
 *              Захист від «покладе сайт» через адмінку. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* 1) Зрізати небезпечні права у Shop Manager (ідемпотентно — пише в БД лише поки cap є) */
add_action( 'init', function () {
	$r = get_role( 'shop_manager' );
	if ( ! $r ) { return; }
	$strip = array(
		// сторінки: щоб не редагували/видаляли головну, privacy, shop, cart, checkout
		'edit_pages', 'edit_others_pages', 'edit_published_pages', 'publish_pages',
		'delete_pages', 'delete_others_pages', 'delete_published_pages', 'delete_private_pages', 'edit_private_pages',
		// кастомайзер/тема
		'edit_theme_options', 'customize',
	);
	foreach ( $strip as $c ) {
		if ( $r->has_cap( $c ) ) { $r->remove_cap( $c ); }
	}
} );

/* 2) Заблокувати сторінки НАЛАШТУВАНЬ WooCommerce/системні для менеджерів (Замовлення/Товари лишаються) */
add_action( 'admin_init', function () {
	if ( ! is_user_logged_in() ) { return; }
	if ( current_user_can( 'manage_options' ) ) { return; }            // адмінів не чіпаємо
	if ( ! current_user_can( 'edit_shop_orders' ) ) { return; }        // діє лише для менеджерів магазину
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	$blocked = array( 'wc-settings', 'wc-status', 'wc-addons' );        // оплата/доставка/податки/інструменти/системний стан
	if ( in_array( $page, $blocked, true ) ) {
		wp_die(
			'<h2>Доступ обмежено</h2><p>Налаштування магазину (оплата, доставка, система) змінює лише адміністратор — щоб випадково не порушити роботу сайту. Якщо потрібна зміна — зверніться до адміністратора.</p>',
			'Доступ обмежено',
			array( 'response' => 403, 'back_link' => true )
		);
	}
} );

/* 3) Прибрати зайві пункти меню для менеджерів (косметика поверх guard) */
add_action( 'admin_menu', function () {
	if ( current_user_can( 'manage_options' ) ) { return; }
	if ( ! current_user_can( 'edit_shop_orders' ) ) { return; }
	remove_submenu_page( 'woocommerce', 'wc-settings' );
	remove_submenu_page( 'woocommerce', 'wc-status' );
	remove_menu_page( 'tools.php' );      // інструменти (експорт/імпорт/здоровʼя сайту)
	remove_menu_page( 'themes.php' );     // вигляд/кастомайзер
}, 999 );
