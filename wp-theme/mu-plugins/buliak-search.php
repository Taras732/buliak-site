<?php
/* Plugin Name: Буляк Search
 * Description: Живий пошук по товарах у дропдауні під хедером (не на весь екран). Результати під час набору. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* AJAX: живий пошук по товарах */
add_action( 'wp_ajax_blk_search', 'blk_search_ajax' );
add_action( 'wp_ajax_nopriv_blk_search', 'blk_search_ajax' );
function blk_search_ajax() {
	$q   = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$out = array( 'items' => array() );
	if ( function_exists( 'wc_get_product' ) && mb_strlen( $q ) >= 2 ) {
		$query = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 7,
			's'              => $q,
			'no_found_rows'  => true,
		) );
		foreach ( $query->posts as $post ) {
			$p = wc_get_product( $post->ID );
			if ( ! $p || ! $p->is_visible() ) { continue; }
			$img = $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), array( 64, 64 ) ) : '';
			$out['items'][] = array(
				'title' => $p->get_name(),
				'url'   => get_permalink( $p->get_id() ),
				'img'   => $img ? $img : '',
				'price' => wp_strip_all_tags( wc_price( $p->get_price(), array( 'decimals' => 0 ) ) ) . ' / кг',
			);
		}
		wp_reset_postdata();
	}
	wp_send_json_success( $out );
}

/* розмітка + CSS + JS */
add_action( 'wp_footer', function () {
	if ( is_admin() ) { return; }
	?>
<div id="blk-search-pop" class="blk-search-pop" hidden>
  <form class="blk-sp-form" role="search" onsubmit="return false">
    <svg class="blk-sp-ic" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="search" class="blk-sp-input" placeholder="Пошук: ковбаса, BBQ, шашлик…" autocomplete="off" aria-label="Пошук товарів">
    <button type="button" class="blk-sp-close" aria-label="Закрити">✕</button>
  </form>
  <div class="blk-sp-results"></div>
</div>
<style id="blk-search-css">
  .blk-search-pop { position: fixed; top: 90px; left: 50%; transform: translateX(-50%) translateY(-10px); z-index: 100002;
    width: 540px; max-width: calc(100% - 24px); background: rgba(20,17,16,.97); -webkit-backdrop-filter: blur(22px); backdrop-filter: blur(22px);
    border: 1px solid rgba(224,181,87,.30); border-radius: 16px; box-shadow: 0 22px 60px rgba(0,0,0,.6);
    opacity: 0; visibility: hidden; transition: opacity .2s ease, transform .2s ease, visibility .2s ease; font-family: 'Manrope',sans-serif; overflow: hidden; }
  .blk-search-pop.open { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
  .blk-sp-form { display: flex; align-items: center; gap: 12px; padding: 15px 18px; border-bottom: 1px solid rgba(224,181,87,.12); }
  .blk-sp-ic { color: #E0B557; flex-shrink: 0; }
  .blk-sp-input { flex: 1; background: none; border: 0; outline: none; color: #f7efe4; font-size: 1.08rem; font-family: 'Manrope',sans-serif; padding: 2px 0; }
  .blk-sp-input::placeholder { color: rgba(247,239,228,.4); }
  .blk-sp-close { background: none; border: 0; color: rgba(247,239,228,.45); cursor: pointer; font-size: 1.05rem; line-height: 1; padding: 4px; }
  .blk-sp-close:hover { color: #E0B557; }
  .blk-sp-results { max-height: 58vh; overflow-y: auto; }
  .blk-sp-item { display: flex; align-items: center; gap: 14px; padding: 11px 18px; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,.04); transition: background .15s; }
  .blk-sp-item:last-child { border-bottom: 0; }
  .blk-sp-item:hover { background: rgba(224,181,87,.07); }
  .blk-sp-item img, .blk-sp-item .blk-sp-ph { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
  .blk-sp-item .blk-sp-ph { background: #1a1614; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
  .blk-sp-name { flex: 1; color: #f7efe4; font-weight: 600; font-size: .96rem; line-height: 1.25; }
  .blk-sp-price { color: #E0B557; font-weight: 700; font-size: .88rem; white-space: nowrap; }
  .blk-sp-msg { padding: 26px 18px; text-align: center; color: rgba(247,239,228,.5); font-size: .92rem; }
  @media (max-width: 600px) { .blk-search-pop { top: 76px; } }
</style>
<script>
(function () {
  var pop = document.getElementById('blk-search-pop'); if (!pop) return;
  var input = pop.querySelector('.blk-sp-input'), results = pop.querySelector('.blk-sp-results');
  var AJAX = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
  var timer;
  function open() { pop.hidden = false; requestAnimationFrame(function () { pop.classList.add('open'); }); setTimeout(function () { input.focus(); }, 90); }
  function close() { pop.classList.remove('open'); setTimeout(function () { pop.hidden = true; }, 220); }
  function render(items, q) {
    if (!q || q.length < 2) { results.innerHTML = '<div class="blk-sp-msg">Почни вводити назву товару…</div>'; return; }
    if (!items.length) { results.innerHTML = '<div class="blk-sp-msg">Нічого не знайдено за «' + q + '»</div>'; return; }
    results.innerHTML = items.map(function (it) {
      var img = it.img ? '<img src="' + it.img + '" alt="">' : '<span class="blk-sp-ph">🥩</span>';
      return '<a class="blk-sp-item" href="' + it.url + '">' + img + '<span class="blk-sp-name">' + it.title + '</span><span class="blk-sp-price">' + it.price + '</span></a>';
    }).join('');
  }
  function doSearch(q) {
    fetch(AJAX + '?action=blk_search&q=' + encodeURIComponent(q), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) { if (input.value.trim() === q) { render((res.data && res.data.items) || [], q); } })
      .catch(function () {});
  }
  input.addEventListener('input', function () {
    var q = input.value.trim(); clearTimeout(timer);
    if (q.length < 2) { render([], q); return; }
    results.innerHTML = '<div class="blk-sp-msg">Шукаю…</div>';
    timer = setTimeout(function () { doSearch(q); }, 250);
  });
  document.addEventListener('click', function (e) {
    if (e.target.closest('.blk-search-btn')) { e.preventDefault(); if (pop.classList.contains('open')) { close(); } else { render([], ''); open(); } return; }
    if (e.target.closest('.blk-sp-close')) { close(); return; }
    if (pop.classList.contains('open') && !pop.contains(e.target) && !e.target.closest('.blk-search-btn')) { close(); }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && pop.classList.contains('open')) { close(); } });
})();
</script>
	<?php
}, 32 );
