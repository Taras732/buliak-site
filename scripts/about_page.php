<?php
/* Сторінка «Про нас» — преміум-дизайн (порт з buliak-modern about.astro). Запуск: wp eval-file - */
if ( ! function_exists( 'wp_insert_post' ) ) { WP_CLI::error( 'WP off' ); }
$base    = 'https://buliak.com/wp-content/themes/buliak-astra/assets';
$hero_bg = "$base/set_feast.webp";
$media   = "$base/sausages.webp";

$svg_smoke = '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22s2.5-1.5 5-1.5 5 1.5 8 1.5 7-1.5 7-1.5"/><path d="M7 16c.5-2 1.5-3.5 3-4.5s2.5-3 2-5.5"/><path d="M12 14c.5-2 1.5-3.5 3-4.5s2.5-3 2-5.5"/><path d="M17 17.5c.3-1 .8-1.8 1.5-2.3s1.2-1.5 1-2.7"/></svg>';
$svg_shield = '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v8"/><path d="M9 11h6"/></svg>';
$svg_clock = '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
$svg_craft = '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>';

$content = <<<HTML
<div class="blk-ab2">
<section class="blk-ab2-hero" style="background-image:url('$hero_bg')">
  <div class="blk-ab2-hero-ov"></div>
  <div class="blk-ab2-cont blk-ab2-hero-in">
    <span class="blk-ab2-badge">🔥 Наша філософія</span>
    <h1 class="blk-ab2-h1">Ми — про <br><span class="blk-ab2-gold">справжнє м'ясо</span></h1>
    <p class="blk-ab2-hd">Історія крафтової коптильні БУЛЯК із Зимної Води, де смак не обдуриш, а м'ясо не бреше.</p>
  </div>
</section>

<section class="blk-ab2-story"><div class="blk-ab2-cont blk-ab2-story-grid">
  <div class="blk-ab2-card blk-ab2-story-text">
    <h2>Хто такий БУЛЯК?</h2>
    <p class="blk-ab2-lead"><span class="blk-ab2-drop">Б</span>УЛЯК почався з простого: робити м'ясо так, як для своїх. Коптити самим, готувати на вогні, не економити на якості. Сьогодні нас обирають тисячі — бо смак не обдуриш, а м'ясо не бреше.</p>
    <p>Ми — виробництво з Зимної Води на Львівщині. BBQ, копченості, домашні ковбаси та м'ясні сети власного виготовлення. Те, що ставимо на свій стіл — пропонуємо й вам.</p>
    <p>Кожен шматок м'яса відбираємо вручну, маринуємо за авторськими рецептами з натуральних спецій і відправляємо в коптильню на вільхових дровах. Жодної хімії, жодних замінників — тільки чистий м'ясний смак.</p>
  </div>
  <div class="blk-ab2-media" style="background-image:url('$media')"><div class="blk-ab2-media-ov"></div></div>
</div></section>

<section class="blk-ab2-principles"><div class="blk-ab2-cont">
  <div class="blk-ab2-sh"><h2>Наші принципи</h2><p>Правила, які ми ніколи не порушуємо заради вигоди</p></div>
  <div class="blk-ab2-pgrid">
    <div class="blk-ab2-card blk-ab2-pc"><div class="blk-ab2-pic">$svg_smoke</div><h3>Коптимо самі</h3><p>Справжній дим власного коптіння на вільхових дровах — без рідкого диму, ароматизаторів і штучних «балончиків».</p></div>
    <div class="blk-ab2-card blk-ab2-pc"><div class="blk-ab2-pic">$svg_shield</div><h3>Чесний склад</h3><p>Коротко й зрозуміло: тільки свіже м'ясо та натуральні свіжозмелені спеції. Ніяких консервантів та сої.</p></div>
    <div class="blk-ab2-card blk-ab2-pc"><div class="blk-ab2-pic">$svg_clock</div><h3>Завжди свіже</h3><p>Готуємо й коптимо постійно невеликими партіями, розбирають швидко — тому продукція ніколи не лежить на вітринах.</p></div>
    <div class="blk-ab2-card blk-ab2-pc"><div class="blk-ab2-pic">$svg_craft</div><h3>Власне виробництво</h3><p>Від обробки та зачистки м'яса до пакування готового продукту — усе робимо власними руками у Зимній Воді.</p></div>
  </div>
</div></section>

<section class="blk-ab2-process"><div class="blk-ab2-cont">
  <div class="blk-ab2-sh"><h2>Як це працює</h2><p>Три прості кроки від замовлення до свіжої вечері</p></div>
  <div class="blk-ab2-timeline">
    <div class="blk-ab2-card blk-ab2-step"><span class="blk-ab2-sn">01</span><h3>Обираєш на сайті</h3><p>Додаєш у кошик потрібну вагу страв чи сетів — ми готуємо виключно під замовлення клієнта.</p></div>
    <div class="blk-ab2-conn"></div>
    <div class="blk-ab2-card blk-ab2-step"><span class="blk-ab2-sn">02</span><h3>Готуємо й коптимо</h3><p>Виготовлення передзамовлення займає 1–2 робочі дні. Ви отримуєте свіжий продукт, а не розігрітий напівфабрикат.</p></div>
    <div class="blk-ab2-conn"></div>
    <div class="blk-ab2-card blk-ab2-step"><span class="blk-ab2-sn">03</span><h3>Доставляємо</h3><p>Швидко відправляємо Новою Поштою по всій Україні. М'ясо їде охолодженим у вакуумній упаковці.</p></div>
  </div>
</div></section>

<section class="blk-ab2-cta-sec"><div class="blk-ab2-cont"><div class="blk-ab2-card blk-ab2-cta">
  <div class="blk-ab2-glow"></div>
  <h2>Скуштуй справжнє</h2>
  <p>Замов свіжу порцію ароматного крафтового м'яса БУЛЯК вже сьогодні.</p>
  <a href="/shop/" class="blk-ab2-btn">Переглянути продукцію →</a>
</div></div></section>
</div>

<style>
  .blk-ab2 { --g:#E0B557; --p:#B81F33; --t:#F7EFE4; --m:rgba(247,239,228,.62); --bd:rgba(224,181,87,.16); --bg:#0c0a09; font-family:'Manrope',sans-serif; }
  .blk-ab2 h1,.blk-ab2 h2,.blk-ab2 h3 { font-family:'Unbounded',sans-serif; }
  .blk-ab2-cont { width:100%; max-width:1140px; margin:0 auto; padding:0 26px; box-sizing:border-box; }
  .blk-ab2-card { background:rgba(23,20,19,.5); -webkit-backdrop-filter:blur(14px); backdrop-filter:blur(14px); border:1px solid var(--bd); border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,.45); }
  /* hero */
  .blk-ab2-hero { position:relative; padding:150px 0 90px; background-size:cover; background-position:center; text-align:center; }
  .blk-ab2-hero-ov { position:absolute; inset:0; background:linear-gradient(180deg,rgba(8,7,7,.45),rgba(8,7,7,.85) 70%,var(--bg)); z-index:1; }
  .blk-ab2-hero-in { position:relative; z-index:2; max-width:820px; }
  .blk-ab2-badge { display:inline-block; font-family:'Unbounded',sans-serif; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--g); border:1px solid rgba(224,181,87,.25); background:rgba(224,181,87,.05); padding:7px 16px; border-radius:99px; margin-bottom:22px; }
  .blk-ab2-h1 { font-size:clamp(2.2rem,6vw,3.6rem); line-height:1.12; font-weight:800; margin:0 0 18px; color:var(--t); }
  .blk-ab2-gold { background:linear-gradient(135deg,#f7efe4,#e0b557); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
  .blk-ab2-hd { font-size:1.12rem; color:var(--m); line-height:1.6; max-width:600px; margin:0 auto; }
  /* story */
  .blk-ab2-story { padding:80px 0; }
  .blk-ab2-story-grid { display:grid; grid-template-columns:7fr 5fr; gap:40px; align-items:center; }
  .blk-ab2-story-text { padding:48px 40px; }
  .blk-ab2-story-text h2 { font-size:1.7rem; color:var(--g); margin:0 0 22px; }
  .blk-ab2-story-text p { font-size:.96rem; color:var(--m); line-height:1.65; margin:0 0 18px; }
  .blk-ab2-story-text p:last-child { margin-bottom:0; }
  .blk-ab2-lead { font-size:1.12rem !important; color:var(--t) !important; font-weight:500; }
  .blk-ab2-drop { font-family:'Unbounded',sans-serif; font-size:3rem; line-height:.8; float:left; margin:6px 12px 0 0; color:var(--g); font-weight:800; text-shadow:0 0 15px rgba(224,181,87,.3); }
  .blk-ab2-media { position:relative; border-radius:20px; overflow:hidden; aspect-ratio:4/5; border:1.5px solid var(--bd); background-size:cover; background-position:center; box-shadow:0 10px 40px rgba(0,0,0,.5); }
  .blk-ab2-media-ov { position:absolute; inset:0; background:linear-gradient(180deg,rgba(8,7,7,0) 50%,rgba(8,7,7,.55)); }
  /* section header */
  .blk-ab2-sh { text-align:center; margin-bottom:54px; }
  .blk-ab2-sh h2 { font-size:clamp(1.7rem,4vw,2.5rem); color:var(--t); margin:0 0 12px; }
  .blk-ab2-sh p { color:var(--m); font-size:1.04rem; margin:0; }
  /* principles */
  .blk-ab2-principles { padding:80px 0; border-top:1px solid var(--bd); border-bottom:1px solid var(--bd); }
  .blk-ab2-pgrid { display:grid; grid-template-columns:repeat(2,1fr); gap:28px; }
  .blk-ab2-pc { padding:34px; transition:transform .3s, border-color .3s, box-shadow .3s; }
  .blk-ab2-pc:hover { transform:translateY(-4px); border-color:rgba(224,181,87,.45); box-shadow:0 15px 45px rgba(224,181,87,.13); }
  .blk-ab2-pic { width:60px; height:60px; border-radius:50%; background:rgba(224,181,87,.06); border:1px solid rgba(224,181,87,.3); color:var(--g); display:flex; align-items:center; justify-content:center; margin-bottom:22px; transition:.3s; }
  .blk-ab2-pc:hover .blk-ab2-pic { background:var(--g); color:#000; transform:scale(1.05) rotate(5deg); }
  .blk-ab2-pc h3 { font-size:1.22rem; color:var(--t); margin:0 0 12px; }
  .blk-ab2-pc p { font-size:.9rem; color:var(--m); line-height:1.6; margin:0; }
  /* timeline */
  .blk-ab2-process { padding:80px 0; }
  .blk-ab2-timeline { display:grid; grid-template-columns:1fr auto 1fr auto 1fr; align-items:stretch; }
  .blk-ab2-step { padding:30px; display:flex; flex-direction:column; gap:12px; transition:border-color .3s, box-shadow .3s; }
  .blk-ab2-step:hover { border-color:rgba(224,181,87,.45); box-shadow:0 15px 45px rgba(224,181,87,.13); }
  .blk-ab2-sn { font-family:'Unbounded',sans-serif; font-size:1.5rem; font-weight:800; color:var(--g); opacity:.85; }
  .blk-ab2-step h3 { font-size:1.12rem; color:var(--t); margin:0 0 6px; }
  .blk-ab2-step p { font-size:.88rem; color:var(--m); line-height:1.5; margin:0; }
  .blk-ab2-conn { width:34px; height:2px; background:var(--bd); align-self:center; position:relative; }
  .blk-ab2-conn::after { content:''; position:absolute; width:6px; height:6px; border-radius:50%; background:var(--g); top:-2px; right:0; }
  /* cta */
  .blk-ab2-cta-sec { padding:40px 0 90px; }
  .blk-ab2-cta { position:relative; padding:60px 40px; text-align:center; overflow:hidden; border-color:rgba(224,181,87,.2); }
  .blk-ab2-glow { position:absolute; width:260px; height:260px; border-radius:50%; background:var(--p); opacity:.12; filter:blur(80px); top:50%; left:50%; transform:translate(-50%,-50%); }
  .blk-ab2-cta h2 { font-size:clamp(1.7rem,4vw,2.3rem); color:var(--g); margin:0 0 14px; position:relative; z-index:2; }
  .blk-ab2-cta p { font-size:1.04rem; color:var(--m); margin:0 0 28px; position:relative; z-index:2; }
  .blk-ab2-btn { position:relative; z-index:2; display:inline-block; background:var(--p); color:#fff; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:16px 36px; border-radius:99px; text-decoration:none; transition:background .2s, transform .15s; box-shadow:0 6px 20px rgba(184,31,51,.3); }
  .blk-ab2-btn:hover { background:#9e1728; transform:translateY(-2px); }
  @media (max-width:900px) { .blk-ab2-story-grid { grid-template-columns:1fr; gap:28px; } .blk-ab2-timeline { grid-template-columns:1fr; gap:20px; } .blk-ab2-conn { display:none; } }
  @media (max-width:768px) { .blk-ab2-pgrid { grid-template-columns:1fr; } .blk-ab2-hero { padding:120px 0 60px; } .blk-ab2-story,.blk-ab2-principles,.blk-ab2-process { padding:56px 0; } }
  @media (max-width:560px) { .blk-ab2-story-text { padding:28px 22px; } }
</style>
HTML;

$existing = get_page_by_path( 'about' );
$data = array( 'post_title' => 'Про нас', 'post_name' => 'about', 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => $content );
if ( $existing ) { $data['ID'] = $existing->ID; wp_update_post( $data ); WP_CLI::log( 'updated about ' . $existing->ID ); }
else { $id = wp_insert_post( $data ); WP_CLI::log( 'created about ' . $id ); }
WP_CLI::success( 'Про нас (преміум) готово' );
