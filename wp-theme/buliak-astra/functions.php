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
    echo '<footer class="buliak-footer"><div class="fcols">'
        . '<div><div class="fb">◆ БУЛЯК</div><p>М\'ясні традиції Галичини. М\'ясо, BBQ та копченості власного виробництва. Зимна Вода, Львівщина.</p></div>'
        . '<div><h4>Контакти</h4>'
        . '<a href="tel:0731117670">' . $tel . '</a>'
        . '<a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Telegram</a>'
        . '<a href="https://www.instagram.com/buliak_space" target="_blank" rel="noopener">Instagram</a>'
        . '<a href="https://www.tiktok.com/@buliak_space" target="_blank" rel="noopener">TikTok</a></div>'
        . '<div><h4>Магазин</h4>'
        . '<a href="' . esc_url(home_url('/shop/')) . '">Усі товари</a>'
        . '<a href="' . esc_url(home_url('/cart/')) . '">Кошик</a>'
        . '<p style="margin-top:10px">Зимна Вода,<br>вул. Яворівська 2г<br>Пн–Сб 09–19 · Нд 10–18</p></div>'
        . '</div><div class="copyr">© ' . date('Y') . ' БУЛЯК. Усі смаки захищені.</div></footer>';
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
