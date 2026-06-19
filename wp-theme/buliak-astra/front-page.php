<?php if (!defined('ABSPATH')) exit; get_header(); $hero = get_stylesheet_directory_uri() . '/assets/hero_bbq.webp'; ?>

<header class="hero" id="top">
  <div class="hero-media" style="background:#1a0f0c url('<?php echo esc_url($hero); ?>') center/cover"></div>
  <div class="hero-grad"></div>
  <div class="hero-inner">
    <span class="eyebrow">М'ясні традиції Галичини</span>
    <h1>БУ<span class="s">ЛЯК</span><span class="sr-only"> — мʼясні традиції Галичини: BBQ, копченості та домашні ковбаси з доставкою</span></h1>
    <p class="hero-sub">М'ясо, BBQ та копченості <b>власного виробництва</b>. Коптимо самі — свіже й справжнє. <b>Наше BBQ — твоя слабкість.</b></p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">До товарів →</a>
      <a class="btn btn-ghost" href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener">Написати в Telegram</a>
    </div>
  </div>
</header>

<?php $mq_block = str_repeat("Передзамовлення 1–2 дні <i>·</i> Коптимо самі на вільхових дровах <i>·</i> Вакуумна упаковка <i>·</i> Доставка Новою Поштою по Україні <i>·</i> Тільки свіже м'ясо <i>·</i> ", 2); ?>
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
  <div style="text-align:center;margin-top:30px"><a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">Усі товари →</a></div>
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

<section class="sec wrap" id="contacts">
  <div class="sec-head"><span class="eyebrow">Завітай</span><h2>Магазин у Зимній Воді</h2></div>
  <div class="blk-contacts-grid">
    <div class="blk-ci-card">
      <div class="blk-ig"><h5>🏬 Наша адреса</h5><p>Львівська обл., с. Зимна Вода, вул. Яворівська 2г</p></div>
      <div class="blk-ig"><h5>📞 Зв'язок та замовлення</h5><a href="tel:0731117670" class="blk-ig-phone">073 111 76 70</a><p class="blk-ig-muted">Пн–Сб 09:00–19:00 · Нд 10:00–18:00</p></div>
      <div class="blk-ig"><h5>🚚 Доставка</h5><p>Швидка доставка Новою Поштою по всій Україні. Термін виготовлення передзамовлення — 1–2 робочих дні.</p></div>
      <div class="blk-ig"><h5>💬 Опт / Співпраця</h5><p>Для великих замовлень, кейтерингу та оптових поставок зв'яжіться з нами в Telegram.</p><a href="https://t.me/BULIAK_DELIVERY" target="_blank" rel="noopener" class="blk-ig-btn"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21.94 4.6 18.9 19.2c-.23 1.02-.84 1.27-1.7.79l-4.7-3.46-2.27 2.18c-.25.25-.46.46-.95.46l.34-4.78L18.5 6.3c.38-.34-.08-.53-.6-.19L6.9 13.18l-4.65-1.45c-1.01-.32-1.03-1.01.21-1.5L20.63 3.2c.84-.32 1.58.2 1.31 1.4z"/></svg> Чат Telegram</a></div>
    </div>
    <div class="blk-map-card"><?php $blk_mapq = rawurlencode("Буляк - М'ясні Традиції Галичини, Зимна Вода"); ?><iframe src="https://maps.google.com/maps?q=<?php echo $blk_mapq; ?>&hl=uk&z=17&output=embed" width="100%" height="100%" style="border:0;min-height:440px" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
  </div>
  <style>
    #contacts .blk-contacts-grid { display: grid; grid-template-columns: 1fr 1.15fr; gap: 24px; align-items: stretch; margin-top: 6px; }
    #contacts .blk-ci-card { background: rgba(23,20,19,.5); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px); border: 1px solid rgba(224,181,87,.16); border-radius: 18px; padding: 34px; display: flex; flex-direction: column; gap: 24px; }
    #contacts .blk-ig h5 { color: #E0B557; font-family: 'Unbounded',sans-serif; font-size: .9rem; text-transform: uppercase; letter-spacing: .04em; margin: 0 0 8px; }
    #contacts .blk-ig p { color: rgba(247,239,228,.7); line-height: 1.55; margin: 0; font-size: 1rem; }
    #contacts .blk-ig-phone { font-family: 'Unbounded',sans-serif; font-size: 1.5rem; font-weight: 800; color: #E0B557; text-decoration: none; display: inline-block; }
    #contacts .blk-ig-phone:hover { color: #fff; }
    #contacts .blk-ig-muted { color: rgba(247,239,228,.5) !important; font-size: .9rem !important; margin-top: 4px !important; }
    #contacts .blk-ig-btn { display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; background: #B81F33; color: #fff !important; font-weight: 700; padding: 12px 24px; border-radius: 99px; text-decoration: none; transition: background .2s, transform .15s; box-shadow: 0 4px 15px rgba(184,31,51,.25); }
    #contacts .blk-ig-btn:hover { background: #9e1728; transform: translateY(-1px); }
    #contacts .blk-map-card { border-radius: 18px; overflow: hidden; border: 1px solid rgba(224,181,87,.16); min-height: 440px; background: #1a1614; }
    #contacts .blk-map-card iframe { display: block; width: 100%; height: 100%; min-height: 440px; filter: grayscale(.15) contrast(1.05) brightness(.95); }
    @media (max-width: 880px) { #contacts .blk-contacts-grid { grid-template-columns: 1fr; } #contacts .blk-map-card { min-height: 320px; } #contacts .blk-map-card iframe { min-height: 320px; } #contacts .blk-ci-card { padding: 26px; } }
  </style>
</section>

<section class="final"><div class="wrap">
  <h2>Зголоднів?</h2>
  <p>Обери з товарів — зберемо, закоптимо й привеземо. Наше BBQ — твоя слабкість.</p>
  <a class="btn btn-primary" href="<?php echo esc_url(home_url('/shop/')); ?>">До товарів →</a>
</div></section>

<?php get_footer(); ?>
