<?php
/* Сторінка «Про нас» (slug about) — наповнена. Запуск: wp eval-file - */
if ( ! function_exists( 'wp_insert_post' ) ) { WP_CLI::error( 'WP off' ); }
$hero = 'https://buliak.com/wp-content/themes/buliak-astra/assets/hero_bbq.webp';
$content = <<<HTML
<div class="about">
  <div class="about-media" style="background-image:url('$hero')"></div>
  <div class="about-text">
    <span class="eyebrow">Хто такий БУЛЯК</span>
    <h2 style="margin-top:16px">Ми — про справжнє м'ясо</h2>
    <p>БУЛЯК почався з простого: робити м'ясо так, як для своїх. Коптити самим, готувати на вогні, не економити на якості. Сьогодні нас обирають тисячі — бо смак не обдуриш, а м'ясо не бреше.</p>
    <p>Ми — виробництво з Зимної Води на Львівщині. BBQ, копченості, домашні ковбаси та м'ясні сети власного виготовлення. Те, що ставимо на свій стіл — пропонуємо й вам.</p>
  </div>
</div>

<div class="blk-ab-sec">
  <h3 class="blk-ab-h">Наші принципи</h3>
  <div class="blk-ab-vals">
    <div class="blk-ab-v"><span class="blk-ab-n">01</span><h4>Коптимо самі</h4><p>Справжній дим власного коптіння — без рідкого диму, ароматизаторів і «балончиків».</p></div>
    <div class="blk-ab-v"><span class="blk-ab-n">02</span><h4>Чесний склад</h4><p>Коротко й зрозуміло: м'ясо та спеції. А не купа хімічних складових.</p></div>
    <div class="blk-ab-v"><span class="blk-ab-n">03</span><h4>Завжди свіже</h4><p>Готуємо й коптимо постійно, розбирають швидко — жодного лежалого.</p></div>
    <div class="blk-ab-v"><span class="blk-ab-n">04</span><h4>Власне виробництво</h4><p>Від обробки до коптіння — усе в наших руках. Тому й відповідаємо за смак.</p></div>
  </div>
</div>

<div class="blk-ab-sec">
  <h3 class="blk-ab-h">Як це працює</h3>
  <div class="blk-ab-steps">
    <div class="blk-ab-step"><span class="blk-ab-sn">1</span><h4>Обираєш на сайті</h4><p>Додаєш у кошик потрібну вагу — ми готуємо під замовлення.</p></div>
    <div class="blk-ab-step"><span class="blk-ab-sn">2</span><h4>Готуємо й коптимо</h4><p>Виготовлення передзамовлення — 1–2 робочі дні. Свіже, не з вітрини.</p></div>
    <div class="blk-ab-step"><span class="blk-ab-sn">3</span><h4>Доставляємо</h4><p>Новою Поштою по всій Україні. Їде охолодженим — лишається розігріти.</p></div>
  </div>
</div>

<div class="blk-ab-cta">
  <h3>Скуштуй справжнє</h3>
  <p>Обери в меню — зберемо, закоптимо й привеземо.</p>
  <a class="blk-ab-btn" href="/shop/">До продукції →</a>
</div>

<style>
  .blk-ab-sec { margin-top: 64px; }
  .blk-ab-h { font-family: 'Unbounded',sans-serif; font-size: clamp(1.6rem,4vw,2.4rem); font-weight: 800; margin: 0 0 28px; text-align: center; }
  .blk-ab-vals { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; }
  .blk-ab-steps { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
  .blk-ab-v, .blk-ab-step { background: rgba(23,20,19,.5); border: 1px solid rgba(224,181,87,.14); border-radius: 16px; padding: 26px; }
  .blk-ab-n, .blk-ab-sn { font-family: 'Unbounded',sans-serif; font-size: 1.6rem; font-weight: 800; color: #B81F33; display: block; margin-bottom: 10px; }
  .blk-ab-sn { color: #E0B557; }
  .blk-ab-v h4, .blk-ab-step h4 { font-size: 1.1rem; margin: 0 0 8px; color: #f7efe4; }
  .blk-ab-v p, .blk-ab-step p { color: rgba(247,239,228,.65); line-height: 1.5; margin: 0; font-size: .92rem; }
  .blk-ab-cta { margin: 64px 0 20px; text-align: center; padding: 46px 30px; background: rgba(184,31,51,.08); border: 1px solid rgba(224,181,87,.18); border-radius: 20px; }
  .blk-ab-cta h3 { font-family: 'Unbounded',sans-serif; font-size: clamp(1.6rem,4vw,2.4rem); margin: 0 0 10px; }
  .blk-ab-cta p { color: rgba(247,239,228,.7); margin: 0 0 22px; }
  .blk-ab-btn { display: inline-block; background: #B81F33; color: #fff; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; padding: 15px 34px; border-radius: 99px; text-decoration: none; transition: background .2s, transform .15s; }
  .blk-ab-btn:hover { background: #9e1728; transform: translateY(-2px); }
  @media (max-width: 880px) { .blk-ab-vals { grid-template-columns: repeat(2,1fr); } .blk-ab-steps { grid-template-columns: 1fr; } }
</style>
HTML;

$existing = get_page_by_path( 'about' );
$data = array( 'post_title' => 'Про нас', 'post_name' => 'about', 'post_status' => 'publish', 'post_type' => 'page', 'post_content' => $content );
if ( $existing ) { $data['ID'] = $existing->ID; wp_update_post( $data ); WP_CLI::log( 'updated about ' . $existing->ID ); }
else { $id = wp_insert_post( $data ); WP_CLI::log( 'created about ' . $id ); }
WP_CLI::success( 'Про нас наповнено' );
