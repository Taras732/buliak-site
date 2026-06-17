<?php
/* Plugin Name: Буляк Polish
 * Description: SEO/OG-теги, sticky-хедер, плавний скрол, прелоадер, skeleton зображень. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ---------- SEO / OpenGraph / Twitter ---------- */
add_action( 'wp_head', function () {
	$og_default = 'https://buliak.com/wp-content/uploads/2026/06/og.png';
	$title = 'БУЛЯК — мʼясні традиції Галичини';
	$desc  = 'Мʼясо, BBQ та копченості власного виробництва. Коптимо самі — свіже й справжнє. Передзамовлення з доставкою Новою Поштою по Україні.';
	$img   = $og_default; $url = home_url( '/' );

	if ( function_exists( 'is_product' ) && is_product() ) {
		$p = wc_get_product( get_the_ID() );
		if ( $p ) {
			$title = $p->get_name() . ' — БУЛЯК';
			$raw = $p->get_short_description() ?: $p->get_description();
			// пробіл перед тегами, щоб "г</p><p>Склад" не злипалось у "гСклад"
			$d = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( str_replace( array( '<', '>' ), array( ' <', '> ' ), $raw ) ) ) );
			if ( $d ) { $desc = mb_substr( $d, 0, 200 ); }
			$tid = $p->get_image_id();
			if ( $tid ) { $im = wp_get_attachment_image_url( $tid, 'large' ); if ( $im ) { $img = $im; } }
			$url = get_permalink();
		}
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$title = 'Магазин — БУЛЯК';
		$desc  = 'Каталог мʼясних виробів БУЛЯК: BBQ, копченості, домашні ковбаси, шашлики та для гриля. Передзамовлення з доставкою Новою Поштою по всій Україні.';
		$url   = get_permalink( wc_get_page_id( 'shop' ) );
	} elseif ( is_page() && ! is_front_page() ) {
		$title = get_the_title() . ' — БУЛЯК'; $url = get_permalink();
	}
	// головна — лишаємо брендований заголовок/опис/OG (не «Головна — БУЛЯК»)

	echo "\n<meta name=\"description\" content=\"" . esc_attr( $desc ) . "\">\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="БУЛЯК">' . "\n";
	echo '<meta property="og:locale" content="uk_UA">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
	if ( $img === $og_default ) { echo '<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">' . "\n"; }
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
}, 4 );

/* ---------- Google Analytics 4 (не трекаємо адмінів) ---------- */
add_action( 'wp_head', function () {
	if ( is_admin() || current_user_can( 'manage_options' ) ) { return; }
	$id = 'G-39R2Q8C3ZV';
	?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $id ); ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $id ); ?>');</script>
<?php
}, 2 );

/* ---------- Прелоадер (надійний: ховається на load + failsafe + noscript) ---------- */
add_action( 'wp_body_open', function () { ?>
<div id="blk-preloader" aria-hidden="true"><img src="https://buliak.com/wp-content/uploads/2026/06/mark-300x300.png" alt="" width="96" height="96"></div>
<script>
(function(){var p=document.getElementById('blk-preloader');if(!p)return;
 function hide(){p.classList.add('blk-pre-done');setTimeout(function(){if(p&&p.parentNode)p.parentNode.removeChild(p);},700);}
 if(document.readyState==='complete'){hide();}else{window.addEventListener('load',hide);}
 setTimeout(hide,4000);})();
</script>
<noscript><style>#blk-preloader{display:none!important}</style></noscript>
<?php } );

/* ---------- Sticky-хедер на скрол + плавний скрол + прелоадер + skeleton CSS ---------- */
add_action( 'wp_head', function () { ?>
<style id="blk-polish-css">
  /* плавний скрол + відступ під липкий хедер для якорів */
  html { scroll-behavior: smooth; }
  section[id], [id]:target { scroll-margin-top: 88px; }
  /* sticky-хедер */
  #masthead { position: sticky !important; top: 0; z-index: 999; transition: box-shadow .25s ease, background-color .25s ease; }
  body.blk-scrolled #masthead { box-shadow: 0 8px 28px rgba(0,0,0,.5); }
  body.blk-scrolled #masthead .main-header-bar, body.blk-scrolled #masthead .ast-primary-header-bar { background-color: #0F0D0C !important; }
  /* прелоадер */
  #blk-preloader { position: fixed; inset: 0; z-index: 100000; background: #0F0D0C;
    display: flex; align-items: center; justify-content: center;
    transition: opacity .6s ease, visibility .6s ease; }
  #blk-preloader img { width: 96px; height: 96px; animation: blk-pre-pulse 1.1s ease-in-out infinite; }
  #blk-preloader.blk-pre-done { opacity: 0; visibility: hidden; pointer-events: none; }
  @keyframes blk-pre-pulse { 0%,100% { transform: scale(.9); opacity: .55; } 50% { transform: scale(1.06); opacity: 1; } }
  /* skeleton-мерехтіння під зображеннями товарів (поки вантажиться / поганий нет) */
  .woocommerce ul.products li.product a img,
  .woocommerce div.product .woocommerce-product-gallery img,
  .blk-carousel-track li.product a img {
    background-image: linear-gradient(100deg, #221d1a 28%, #2e2723 48%, #221d1a 68%);
    background-size: 200% 100%; background-color: #221d1a;
    animation: blk-shimmer 1.25s linear infinite;
  }
  @keyframes blk-shimmer { to { background-position: -200% 0; } }
  /* доступність: візуально-прихований текст для скрінрідерів (SEO-збагачення H1) */
  .sr-only { position:absolute!important; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
  /* iOS Safari: hover-scale зображення не вилазить за радіус картки */
  .woocommerce ul.products li.product, .blk-carousel-track li.product { isolation: isolate; }
</style>
<?php }, 6 );

/* JS: позначка скролу для sticky-хедера */
add_action( 'wp_footer', function () { ?>
<script>
(function(){function s(){document.body.classList.toggle('blk-scrolled', (window.scrollY||window.pageYOffset)>10);}
 window.addEventListener('scroll', s, {passive:true}); s();})();
</script>
<?php } );

/* ---------- PageSpeed: preconnect + preload hero (LCP) ---------- */
add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	if ( is_front_page() ) {
		$h = get_stylesheet_directory_uri() . '/assets/hero_bbq.webp';
		echo '<link rel="preload" as="image" href="' . esc_url( $h ) . '" fetchpriority="high">' . "\n";
		$m = get_stylesheet_directory_uri() . '/assets/hero_bbq-m.webp';
		echo '<style>@media(max-width:768px){.hero-media{background-image:url(' . esc_url( $m ) . ') !important}}</style>' . "\n";
	}
}, 1 );

/* ---------- PageSpeed: прибрати зайве ---------- */
add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	add_filter( 'emoji_svg_url', '__return_false' );
} );
add_action( 'wp_enqueue_scripts', function () {
	if ( is_front_page() ) {
		// головна — шаблонна, без Gutenberg-блоків: блок-CSS не потрібні
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 100 );

/* ---------- Аудит-фікси: переклад skip-link + відправник email ---------- */
add_filter( 'gettext', function ( $translated, $text, $domain ) {
	if ( $text === 'Skip to content' ) { return 'Перейти до вмісту'; }
	return $translated;
}, 10, 3 );
add_filter( 'woocommerce_email_from_name', function () { return 'БУЛЯК'; } );
add_filter( 'woocommerce_email_from_address', function () { return 'noreply@buliak.com'; } );
add_filter( 'wp_mail_from_name', function ( $n ) { return $n === 'WordPress' ? 'БУЛЯК' : $n; } );
add_filter( 'wp_mail_from', function ( $e ) { return ( strpos( $e, 'wordpress@' ) === 0 ) ? 'noreply@buliak.com' : $e; } );

/* reveal-анімації для AJAX-довантажених товарів (фільтр/пагінація) — щоб не лишались невидимими */
add_action( 'wp_footer', function () { ?>
<script>
if (window.jQuery) { jQuery(document.body).on('post-load wc-blocks_product_list_rendered', function () {
  document.querySelectorAll('.reveal').forEach(function (e) { e.style.opacity = '1'; e.style.transform = 'none'; });
}); }
</script>
<?php }, 99 );
