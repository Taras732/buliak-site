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

// Брендовий футер БУЛЯК (дизайн buliak-modern)
add_action('astra_footer_before', function () {
    $logo = wp_get_attachment_image_url( get_theme_mod('custom_logo'), 'full' );
    if ( ! $logo ) { $logo = 'https://buliak.com/wp-content/uploads/2026/06/logow.png'; }
    $priv = ( function_exists('get_privacy_policy_url') && get_privacy_policy_url() ) ? get_privacy_policy_url() : home_url('/privacy-policy/');
    $tg = '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M21.94 4.6 18.9 19.2c-.23 1.02-.84 1.27-1.7.79l-4.7-3.46-2.27 2.18c-.25.25-.46.46-.95.46l.34-4.78L18.5 6.3c.38-.34-.08-.53-.6-.19L6.9 13.18l-4.65-1.45c-1.01-.32-1.03-1.01.21-1.5L20.63 3.2c.84-.32 1.58.2 1.31 1.4z"/></svg>';
    $ig = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>';
    $tt = '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M16.6 5.82a4.3 4.3 0 0 1-1.06-2.82h-3.2v12.9a2.59 2.59 0 1 1-2.59-2.59c.27 0 .54.04.79.12V9.97a5.86 5.86 0 0 0-.79-.06 5.85 5.85 0 1 0 5.85 5.85V8.49a7.5 7.5 0 0 0 4.4 1.41V6.7a4.3 4.3 0 0 1-3.4-.88z"/></svg>';
    ?>
    <footer class="buliak-footer">
      <div class="blk-fc-cont blk-fc-cols">
        <div class="blk-fc-brand">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="blk-fc-logo"><img src="<?php echo esc_url($logo); ?>" alt="БУЛЯК"></a>
        </div>
        <div class="blk-fc-col">
          <h4>Контакти</h4>
          <a href="tel:0731117670" class="blk-fc-tel">073 111 76 70</a>
          <a href="<?php echo esc_url(home_url('/#contacts')); ?>">Зимна Вода, вул. Яворівська 2г</a>
          <span class="blk-fc-plain">Пн–Сб: 09:00–19:00</span>
          <div class="blk-fc-social">
            <a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener" aria-label="Telegram"><?php echo $tg; ?></a>
            <a href="https://www.instagram.com/buliak_space" target="_blank" rel="noopener" aria-label="Instagram"><?php echo $ig; ?></a>
            <a href="https://www.tiktok.com/@buliak_space" target="_blank" rel="noopener" aria-label="TikTok"><?php echo $tt; ?></a>
          </div>
        </div>
        <div class="blk-fc-col">
          <h4>Магазин</h4>
          <a href="<?php echo esc_url(home_url('/shop/')); ?>">Продукція</a>
          <a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Опт / гурт</a>
          <a href="<?php echo esc_url(home_url('/about/')); ?>">Про нас</a>
        </div>
      </div>
      <div class="blk-fc-bottom"><div class="blk-fc-cont blk-fc-bflex">
        <span class="blk-fc-copy">© <?php echo date('Y'); ?> БУЛЯК. Усі права захищені. · Викувано в <a href="https://kuznya.studio" target="_blank" rel="noopener" class="blk-fc-kuz">Кузні</a></span>
        <span class="blk-fc-legal"><a href="<?php echo esc_url($priv); ?>">Політика конфіденційності</a><span class="blk-fc-sep">·</span><a href="<?php echo esc_url(home_url('/refund_returns/')); ?>">Повернення та обмін</a></span>
      </div></div>
    </footer>
    <style id="blk-footer-css">
      .buliak-footer { background: linear-gradient(180deg, rgba(8,7,7,0), rgba(12,10,9,.85)) !important; -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px);
        border-top: 1px solid rgba(224,181,87,.14) !important; padding: 58px 0 0 !important; margin-top: 60px !important; font-family: 'Manrope',sans-serif; }
      .blk-fc-cont { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 26px; box-sizing: border-box; }
      .blk-fc-cols { display: grid !important; grid-template-columns: 2fr 1fr 1fr; gap: 40px; padding-bottom: 40px; }
      .blk-fc-brand .blk-fc-logo img { max-height: 66px; width: auto; display: block; }
      .blk-fc-social { display: flex; gap: 12px; margin-top: 16px; }
      .blk-fc-social a { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 50%;
        background: rgba(255,255,255,.02); border: 1px solid rgba(224,181,87,.14); color: #f7efe4 !important; transition: background .25s, color .25s, transform .25s, border-color .25s; }
      .blk-fc-social a:hover { background: #E0B557; color: #000 !important; border-color: #E0B557; transform: translateY(-4px); box-shadow: 0 5px 15px rgba(224,181,87,.25); }
      .blk-fc-col h4 { color: #E0B557; font-family: 'Unbounded',sans-serif; font-size: .92rem; text-transform: uppercase; letter-spacing: .05em; margin: 0 0 18px; }
      .blk-fc-col a, .blk-fc-plain { display: block; color: rgba(247,239,228,.62) !important; margin-bottom: 12px; text-decoration: none; font-size: 1rem; transition: color .2s; }
      .blk-fc-col a:hover { color: #E0B557 !important; }
      .blk-fc-tel { font-size: 1.4rem !important; font-weight: 700; color: #f7efe4 !important; margin-bottom: 14px !important; }
      .blk-fc-tel:hover { color: #B81F33 !important; }
      .blk-fc-bottom { border-top: 1px solid rgba(224,181,87,.14); padding: 22px 0; color: rgba(247,239,228,.55); font-size: .8rem; }
      .blk-fc-bflex { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
      .blk-fc-copy { color: rgba(247,239,228,.55); }
      .blk-fc-kuz { color: #E0B557; font-weight: 600; }
      .blk-fc-kuz:hover { text-decoration: underline; }
      .blk-fc-legal a { color: rgba(247,239,228,.6); text-decoration: none; }
      .blk-fc-legal a:hover { color: #E0B557; text-decoration: underline; }
      .blk-fc-sep { margin: 0 8px; color: rgba(224,181,87,.3); }
      @media (max-width: 768px) { .blk-fc-cols { grid-template-columns: 1fr !important; gap: 28px; } .blk-fc-bflex { flex-direction: column; text-align: center; } }
    </style>
    <?php
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
