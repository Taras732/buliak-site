<?php
/* Імпорт featured-фото товарів за SKU в імені файлу (BLK-XX.jpg).
   Запуск у контейнері: docker exec buliak-wp php /tmp/import_photos.php /tmp/blk_photos */
require '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dir   = isset( $argv[1] ) ? $argv[1] : '/tmp/blk_photos';
$files = glob( $dir . '/BLK-*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE );
if ( ! $files ) { echo "Немає файлів BLK-*.* у $dir\n"; exit; }

$ok = 0; $err = 0;
foreach ( $files as $f ) {
	if ( ! preg_match( '/(BLK-\d+)/i', basename( $f ), $m ) ) { echo "skip (no SKU): " . basename( $f ) . "\n"; continue; }
	$sku = strtoupper( $m[1] );
	$pid = wc_get_product_id_by_sku( $sku );
	if ( ! $pid ) { echo "✗ $sku — товар не знайдено\n"; $err++; continue; }
	$p = wc_get_product( $pid );

	$tmp = wp_tempnam( basename( $f ) );
	copy( $f, $tmp );
	$arr = array( 'name' => basename( $f ), 'tmp_name' => $tmp );
	$att = media_handle_sideload( $arr, $pid, $p->get_name() );
	if ( is_wp_error( $att ) ) { @unlink( $tmp ); echo "✗ $sku — " . $att->get_error_message() . "\n"; $err++; continue; }

	set_post_thumbnail( $pid, $att );
	echo "✓ $sku → id $pid (att $att) — " . $p->get_name() . "\n";
	$ok++;
}
echo "\nГотово: $ok ок, $err помилок\n";
