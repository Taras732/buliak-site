<?php
/* Plugin Name: Буляк Search
 * Description: Преміум-оверлей пошуку по товарах (іконка-лупа в хедері → пошук WooCommerce). */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'wp_footer', function () {
	if ( is_admin() ) { return; }
	$action = esc_url( home_url( '/' ) );
	?>
<div id="blk-search-overlay" class="blk-search-overlay" aria-hidden="true">
  <button type="button" class="blk-search-close" aria-label="Закрити"><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
  <form class="blk-search-box" method="get" action="<?php echo $action; ?>" role="search">
    <span class="blk-search-eyebrow">Пошук по товарах</span>
    <div class="blk-search-field">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="search" name="s" class="blk-search-input" placeholder="Шукати м'ясо, BBQ, ковбаски…" autocomplete="off" aria-label="Пошук">
      <input type="hidden" name="post_type" value="product">
    </div>
    <button type="submit" class="blk-search-submit">Знайти</button>
  </form>
</div>
<style id="blk-search-css">
  .blk-search-overlay { --bx-gold:#E0B557; --bx-primary:#B81F33; --bx-text:#F7EFE4; --bx-muted:rgba(247,239,228,.6);
    position: fixed; inset: 0; z-index: 100002; background: rgba(10,9,8,.85); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
    display: flex; align-items: center; justify-content: center; padding: 24px; opacity: 0; visibility: hidden;
    transition: opacity .3s ease, visibility .3s ease; font-family: 'Manrope', sans-serif; }
  .blk-search-overlay.open { opacity: 1; visibility: visible; }
  .blk-search-close { position: absolute; top: 28px; right: 28px; background: none; border: 0; cursor: pointer; color: var(--bx-muted); transition: color .2s, transform .2s; }
  .blk-search-close:hover { color: var(--bx-gold); transform: rotate(90deg); }
  .blk-search-box { width: 100%; max-width: 640px; display: flex; flex-direction: column; align-items: center; gap: 22px;
    transform: translateY(14px); transition: transform .35s cubic-bezier(.16,1,.3,1); }
  .blk-search-overlay.open .blk-search-box { transform: translateY(0); }
  .blk-search-eyebrow { font-family: 'Unbounded',sans-serif; font-size: .72rem; font-weight: 700; letter-spacing: .3em; text-transform: uppercase; color: var(--bx-gold); }
  .blk-search-field { width: 100%; display: flex; align-items: center; gap: 14px; padding: 0 4px 16px;
    border-bottom: 2px solid rgba(224,181,87,.35); color: var(--bx-muted); }
  .blk-search-input { flex: 1; background: none; border: 0; outline: none; color: var(--bx-text);
    font-size: clamp(1.3rem, 4vw, 2rem); font-family: 'Unbounded',sans-serif; font-weight: 500; padding: 6px 0; }
  .blk-search-input::placeholder { color: rgba(247,239,228,.35); }
  .blk-search-submit { background: var(--bx-primary); color: #fff; border: 0; border-radius: 999px; cursor: pointer;
    padding: 13px 38px; font-weight: 700; font-size: .9rem; letter-spacing: .04em; text-transform: uppercase;
    box-shadow: 0 4px 15px rgba(184,31,51,.3); transition: background .2s, transform .15s; }
  .blk-search-submit:hover { background: #9e1728; transform: translateY(-1px); }
</style>
<script>
(function(){
  var ov = document.getElementById('blk-search-overlay'); if(!ov) return;
  var input = ov.querySelector('.blk-search-input');
  function open(){ ov.classList.add('open'); ov.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; setTimeout(function(){input.focus();},120); }
  function close(){ ov.classList.remove('open'); ov.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
  document.addEventListener('click', function(e){
    var t = e.target.closest('.blk-search-btn'); if(t){ e.preventDefault(); open(); return; }
    if(e.target===ov){ close(); }
    if(e.target.closest('.blk-search-close')){ close(); }
  });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape' && ov.classList.contains('open')) close(); });
})();
</script>
	<?php
}, 32 );
