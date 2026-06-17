<?php
/* Конвертація hero у WebP (desktop 1920 + mobile 900) для PageSpeed.
 * Запуск: docker cp у buliak-wp:/tmp + docker exec buliak-wp php /tmp/img_webp.php */
$A = '/var/www/html/wp-content/themes/buliak-astra/assets';
$src = "$A/hero_bbq.png";
if ( ! file_exists( $src ) ) { fwrite( STDERR, "no src\n" ); exit( 1 ); }

$i = new Imagick( $src );
$i->setImageColorspace( Imagick::COLORSPACE_SRGB );
$i->resizeImage( 1920, 0, Imagick::FILTER_LANCZOS, 1 );
$i->setImageFormat( 'webp' );
$i->setImageCompressionQuality( 82 );
$i->stripImage();
$i->writeImage( "$A/hero_bbq.webp" );

$m = clone $i;
$m->resizeImage( 900, 0, Imagick::FILTER_LANCZOS, 1 );
$m->setImageCompressionQuality( 78 );
$m->writeImage( "$A/hero_bbq-m.webp" );

echo 'OK webp=' . round( filesize( "$A/hero_bbq.webp" ) / 1024 ) . 'KB mobile=' . round( filesize( "$A/hero_bbq-m.webp" ) / 1024 ) . "KB\n";
