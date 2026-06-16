<?php if (!defined('ABSPATH')) exit; get_header(); $hero = get_stylesheet_directory_uri() . '/assets/hero_bbq.png'; ?>

<header class="hero" id="top">
  <div class="hero-media" style="background:#1a0f0c url('<?php echo esc_url($hero); ?>') center/cover"></div>
  <div class="hero-grad"></div>
  <div class="hero-inner">
    <span class="eyebrow">М'ясні традиції Галичини</span>
    <h1>БУ<span class="s">ЛЯК</span></h1>
    <p class="hero-sub">М'ясо, BBQ та копченості <b>власного виробництва</b>. Коптимо самі — свіже й справжнє. <b>Наше BBQ — твоя слабкість.</b></p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">До меню →</a>
      <a class="btn btn-ghost" href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Написати в Telegram</a>
    </div>
  </div>
</header>

<?php $mq_block = str_repeat("М'ЯСО <i>◆</i> BBQ <i>◆</i> КОПЧЕНОСТІ <i>◆</i> ДОМАШНІ КОВБАСИ <i>◆</i> ШАШЛИКИ <i>◆</i> ГРИЛЬ <i>◆</i> ", 3); ?>
<div class="marquee"><div class="mtrack">
  <span><?php echo $mq_block; ?></span>
  <span><?php echo $mq_block; ?></span>
</div></div>

<section class="sec wrap" id="menu">
  <div class="sec-head">
    <span class="eyebrow">Хіти продажів</span>
    <h2>Бестселери, від яких тече слина</h2>
  </div>
  <?php if (class_exists('WooCommerce')) echo do_shortcode('[buliak_bestsellers]'); ?>
  <div style="text-align:center;margin-top:30px"><a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">Усе меню →</a></div>
</section>

<section class="sec wrap">
  <div class="sec-head"><span class="eyebrow">Чому БУЛЯК</span><h2>Чому повертаються</h2></div>
  <div class="why">
    <div class="why-item"><div class="ic">01</div><h3>Коптимо самі</h3><p>Справжній дим власного коптіння. Без рідкого диму, ароматизаторів і «балончиків».</p></div>
    <div class="why-item"><div class="ic">02</div><h3>Як у бабці</h3><p>Короткий чесний склад — м'ясо та спеції. А не купа хімічних складових.</p></div>
    <div class="why-item"><div class="ic">03</div><h3>Завжди свіже</h3><p>Готуємо й коптимо постійно, розбирають швидко — жодного лежалого, тільки свіже.</p></div>
    <div class="why-item"><div class="ic">04</div><h3>Повертаються по ще</h3><p>Скуштував раз — приходиш знову. І друзів приводиш.</p></div>
  </div>
</section>

<section class="sec wrap" id="about"><div class="about">
  <div class="about-media" style="background-image:url('<?php echo esc_url($hero); ?>')"></div>
  <div class="about-text">
    <span class="eyebrow">Хто такий БУЛЯК</span>
    <h2 style="margin-top:16px">Ми — про справжнє м'ясо</h2>
    <p>БУЛЯК почався з простого: робити м'ясо так, як для своїх. Коптити самим, готувати на вогні, не економити на якості. Сьогодні нас обирають тисячі — бо смак не обдуриш, а м'ясо не бреше.</p>
    <p>Працюємо як передзамовлення: оформлюй на сайті, відправляємо <b>Новою Поштою</b> по всій Україні за 1–2 робочі дні. Їде охолодженим — лишається тільки розігріти.</p>
  </div>
</div></section>

<section class="sec wrap" id="contacts">
  <div class="sec-head"><span class="eyebrow">Завітай</span><h2>Магазин у Зимній Воді</h2></div>
  <div class="contact">
    <div class="map-frame"><?php $map_q = rawurlencode("Буляк - М'ясні Традиції Галичини, Зимна Вода"); ?><iframe src="https://maps.google.com/maps?q=<?php echo $map_q; ?>&hl=uk&z=17&output=embed" loading="lazy"></iframe></div>
    <div>
      <div class="cline"><div class="k">Адреса</div><div class="v">Зимна Вода, вул. Яворівська 2г</div></div>
      <div class="cline"><div class="k">Телефон</div><div class="v"><a href="tel:0731117670">073 111 76 70</a></div></div>
      <div class="cline"><div class="k">Графік</div><div class="v">Пн–Сб 09–19 · Нд 10–18</div></div>
      <div class="social-row">
        <a class="social-ic" href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener" aria-label="Telegram"><svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M21.94 4.6 18.9 19.2c-.23 1.02-.84 1.27-1.7.79l-4.7-3.46-2.27 2.18c-.25.25-.46.46-.95.46l.34-4.78L18.5 6.3c.38-.34-.08-.53-.6-.19L6.9 13.18l-4.65-1.45c-1.01-.32-1.03-1.01.21-1.5L20.63 3.2c.84-.32 1.58.2 1.31 1.4z"/></svg></a>
        <a class="social-ic" href="https://instagram.com/buliak_space" target="_blank" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg></a>
      </div>
    </div>
  </div>
</section>

<section class="final"><div class="wrap">
  <h2>Зголоднів?</h2>
  <p>Обери в меню — зберемо, закоптимо й привеземо. Наше BBQ — твоя слабкість.</p>
  <a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">До меню →</a>
</div></section>

<?php get_footer(); ?>
