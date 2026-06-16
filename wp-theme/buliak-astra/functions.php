<?php
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('astra-parent', get_template_directory_uri() . '/style.css', array(), null);
    wp_enqueue_style('buliak-fonts', 'https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap', array(), null);
    wp_enqueue_style('buliak-brand', get_stylesheet_uri(), array('astra-parent', 'buliak-fonts'), '1.5');
    wp_enqueue_script('buliak-qty', get_stylesheet_directory_uri() . '/quantity.js', array(), '1.0', true);
    wp_enqueue_script('buliak-reveal', get_stylesheet_directory_uri() . '/reveal.js', array(), '1.0', true);
}, 15);

// Badge на картках: «Хіт» (featured) / «Новинка» (< 30 днів)
add_action('woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if (!$product) return;
    if ($product->is_featured()) {
        echo '<span class="buliak-badge">Хіт</span>';
    } elseif ($product->get_date_created() && strtotime($product->get_date_created()) > strtotime('-30 days')) {
        echo '<span class="buliak-badge is-new">Новинка</span>';
    }
}, 9);

add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
});

// Checkout під Україну — мінімум полів
add_filter('woocommerce_checkout_fields', function ($fields) {
    foreach (array('billing_company', 'billing_address_2', 'billing_state', 'billing_postcode') as $f) {
        unset($fields['billing'][$f]);
    }
    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country']['default'] = 'UA';
    }
    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['required'] = true;
        $fields['billing']['billing_phone']['priority'] = 25;
    }
    return $fields;
});

// Брендовий футер БУЛЯК
add_action('astra_footer_before', function () {
    $tel = '073 111 76 70';
    $blk_logo = wp_get_attachment_image_url( get_theme_mod('custom_logo'), 'full' );
    $blk_logo_html = '<a href="' . esc_url(home_url('/')) . '" class="footer-logo-link">' . ( $blk_logo ? '<img src="' . esc_url($blk_logo) . '" alt="Буляк" style="max-height:72px;width:auto">' : '◆ БУЛЯК' ) . '</a>';
    echo '<footer class="buliak-footer"><div class="fcols">'
        . '<div><div class="fb">' . $blk_logo_html . '</div></div>'
        . '<div><h4>Контакти</h4>'
        . '<a href="tel:0731117670">' . $tel . '</a>'
        . '<div class="blk-foot-social">'
        . '<a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener" aria-label="Telegram"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M21.94 4.6 18.9 19.2c-.23 1.02-.84 1.27-1.7.79l-4.7-3.46-2.27 2.18c-.25.25-.46.46-.95.46l.34-4.78L18.5 6.3c.38-.34-.08-.53-.6-.19L6.9 13.18l-4.65-1.45c-1.01-.32-1.03-1.01.21-1.5L20.63 3.2c.84-.32 1.58.2 1.31 1.4z"/></svg></a>'
        . '<a href="https://www.instagram.com/buliak_space" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg></a>'
        . '<a href="https://www.tiktok.com/@buliak_space" target="_blank" rel="noopener" aria-label="TikTok"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M16.6 5.82a4.3 4.3 0 0 1-1.06-2.82h-3.2v12.9a2.59 2.59 0 1 1-2.59-2.59c.27 0 .54.04.79.12V9.97a5.86 5.86 0 0 0-.79-.06 5.85 5.85 0 1 0 5.85 5.85V8.49a7.5 7.5 0 0 0 4.4 1.41V6.7a4.3 4.3 0 0 1-3.4-.88z"/></svg></a>'
        . '</div></div>'
        . '<div><h4>Магазин</h4>'
        . '<a href="' . esc_url(home_url('/shop/')) . '">Усі товари</a>'
        . '<a href="' . esc_url(home_url('/cart/')) . '">Кошик</a>' . '<a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Опт / гурт</a>'
        . '<a href="' . esc_url(home_url('/#contacts')) . '">Контакти</a></div>'
        . '</div><div class="copyr">'
        . '<span class="copyr-c">© ' . date('Y') . ' БУЛЯК. Усі права захищені. · Сайт викувано в <a href="https://kuznya.studio" target="_blank" rel="noopener" style="color:var(--gold,#e0b557)">Кузні</a></span>'
        . '<span class="blk-foot-legal"><a href="' . esc_url( function_exists('get_privacy_policy_url') && get_privacy_policy_url() ? get_privacy_policy_url() : home_url('/privacy-policy/') ) . '">Політика конфіденційності</a><span class="blk-foot-sep"> · </span><a href="' . esc_url(home_url('/refund_returns/')) . '">Повернення та обмін</a></span>'
        . '</div></footer>';
});

add_filter('loop_shop_columns', function () { return 3; });
add_filter('loop_shop_per_page', function () { return 12; });

// головна — на всю ширину (без контейнера Astra), щоб hero був повноекранний
add_filter('astra_get_content_layout', function ($layout) {
    return is_front_page() ? 'page-builder' : $layout;
});
add_filter('astra_the_title_enabled', function ($enabled) {
    return is_front_page() ? false : $enabled;
});
