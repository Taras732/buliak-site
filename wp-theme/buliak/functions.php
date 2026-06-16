<?php
if (!defined('ABSPATH')) exit;

function buliak_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('html5', array('search-form', 'gallery', 'caption', 'style', 'script'));
    register_nav_menus(array('primary' => 'Головне меню'));
}
add_action('after_setup_theme', 'buliak_setup');

function buliak_assets() {
    wp_enqueue_style('buliak-fonts', 'https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap', array(), null);
    wp_enqueue_style('buliak-style', get_stylesheet_uri(), array('buliak-fonts'), '1.2');
}
add_action('wp_enqueue_scripts', 'buliak_assets');

// WooCommerce: 3 товари в ряд
add_filter('loop_shop_columns', function () { return 3; });
add_filter('loop_shop_per_page', function () { return 12; });

// Прибрати дефолтні обгортки WooCommerce-сторінок (свій контейнер у шаблонах)
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', function () {
    echo '<div class="sec wrap" style="padding-top:130px">';
}, 10);
add_action('woocommerce_after_main_content', function () {
    echo '</div>';
}, 10);
