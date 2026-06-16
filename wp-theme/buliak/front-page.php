<?php if (!defined('ABSPATH')) exit; get_header(); $hero = get_template_directory_uri() . '/assets/hero_bbq.png'; ?>

<header class="hero" id="top">
  <div class="hero-media" style="background:#1a0f0c url('<?php echo esc_url($hero); ?>') center/cover"></div>
  <div class="hero-grad"></div>
  <div class="hero-inner wrap">
    <span class="eyebrow">М'ясні традиції Галичини</span>
    <h1>БУ<span class="s">ЛЯК</span></h1>
    <p class="hero-sub">М'ясо, BBQ та копченості <b>власного виробництва</b>. Готові сети на компанію — збираємо, коптимо й привеземо. <b>Наше BBQ — твоя слабкість.</b></p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">Обрати та замовити →</a>
      <a class="btn btn-ghost" href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Написати в Telegram</a>
    </div>
  </div>
</header>

<div class="marquee"><div class="mtrack">
  <span>М'ЯСО <i>◆</i> BBQ <i>◆</i> КОПЧЕНОСТІ <i>◆</i> КОВБАСИ <i>◆</i> СИРИ <i>◆</i> М'ЯСНІ СЕТИ <i>◆</i> </span>
  <span>М'ЯСО <i>◆</i> BBQ <i>◆</i> КОПЧЕНОСТІ <i>◆</i> КОВБАСИ <i>◆</i> СИРИ <i>◆</i> М'ЯСНІ СЕТИ <i>◆</i> </span>
</div></div>

<section class="sec wrap" id="menu">
  <div class="sec-head">
    <span class="eyebrow">Наш товар</span>
    <h2>Те, від чого слина тече</h2>
    <p>Свіже м'ясо, копчене на власному виробництві. Бери поштучно або готовим сетом.</p>
  </div>
  <?php if (class_exists('WooCommerce')) echo do_shortcode('[products limit="6" columns="3" orderby="date"]'); ?>
  <div style="text-align:center;margin-top:40px">
    <a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">Усе меню →</a>
  </div>
</section>

<section class="sec wrap">
  <div class="sec-head"><span class="eyebrow">Чому БУЛЯК</span><h2>Чому повертаються</h2></div>
  <div class="why">
    <div class="why-item"><div class="ic">01</div><h3>Власне коптіння</h3><p>Коптимо самі, на власному виробництві — не перепродаж.</p></div>
    <div class="why-item"><div class="ic">02</div><h3>Галицькі традиції</h3><p>Рецепти, перевірені поколіннями. М'ясо, а не хімія.</p></div>
    <div class="why-item"><div class="ic">03</div><h3>Свіже щодня</h3><p>Привозимо свіжину — не лежиться, бо швидко розбирають.</p></div>
    <div class="why-item"><div class="ic">04</div><h3>Сотні замовлень</h3><p>Щотижня сотні людей обирають БУЛЯК. Долучайся.</p></div>
  </div>
</section>

<section class="sec wrap" id="about"><div class="about">
  <div class="about-media" style="background-image:url('<?php echo esc_url($hero); ?>')"></div>
  <div class="about-text">
    <span class="eyebrow">Хто такий БУЛЯК</span>
    <h2 style="margin-top:16px">Ми — про справжнє м'ясо</h2>
    <p>БУЛЯК почався з простого: робити м'ясо так, як для своїх. Коптити самим, готувати на вогні, не економити на якості. Сьогодні нас обирають тисячі — бо смак не обдуриш, а м'ясо не бреше.</p>
    <p>Заходь у магазин у Зимній Воді або замовляй — привеземо, поки не передумав 😏</p>
  </div>
</div></section>

<section class="sec wrap" id="contacts">
  <div class="sec-head"><span class="eyebrow">Завітай</span><h2>Магазин у Зимній Воді</h2></div>
  <div class="contact">
    <div class="map-frame">
      <iframe src="https://maps.google.com/maps?q=%D0%97%D0%B8%D0%BC%D0%BD%D0%B0%20%D0%92%D0%BE%D0%B4%D0%B0%20%D0%AF%D0%B2%D0%BE%D1%80%D1%96%D0%B2%D1%81%D1%8C%D0%BA%D0%B0%202%D0%B3&z=15&output=embed" loading="lazy"></iframe>
    </div>
    <div>
      <div class="cline"><div><div class="k">Адреса</div><div class="v">Зимна Вода, вул. Яворівська 2г</div></div></div>
      <div class="cline"><div><div class="k">Телефон</div><div class="v"><a href="tel:0731117670">073 111 76 70</a></div></div></div>
      <div class="cline"><div><div class="k">Замовлення</div><div class="v"><a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">@BULIAK_DELIVERY</a></div></div></div>
      <div class="cline"><div><div class="k">Графік</div><div class="v">Пн–Сб 09–19 · Нд 10–18</div></div></div>
      <a class="btn btn-primary" style="margin-top:24px" href="<?php echo esc_url(home_url('/shop/')); ?>">До магазину →</a>
    </div>
  </div>
</section>

<section class="final">
  <div class="wrap">
    <h2>Зголоднів?</h2>
    <p>Обери на сайті — зберемо й привеземо. Наше BBQ — твоя слабкість.</p>
    <a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">Замовити →</a>
  </div>
</section>

<?php get_footer(); ?>
