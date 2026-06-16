<?php if (!defined('ABSPATH')) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div class="grain"></div>
<nav>
  <a class="nav-brand" href="<?php echo esc_url(home_url('/')); ?>"><b>◆</b> БУЛЯК</a>
  <div class="nav-links">
    <a href="<?php echo esc_url(home_url('/')); ?>">Головна</a>
    <a href="<?php echo esc_url(home_url('/shop/')); ?>">Магазин</a>
    <a href="<?php echo esc_url(home_url('/#about')); ?>">Про нас</a>
    <a href="<?php echo esc_url(home_url('/#contacts')); ?>">Контакти</a>
    <?php if (class_exists('WooCommerce')) : ?>
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>">Кошик</a>
    <?php endif; ?>
  </div>
  <a class="nav-cta" href="<?php echo esc_url(home_url('/shop/')); ?>">Замовити</a>
</nav>
