<?php
/* Конвертація додаткових зображень у WebP (для варіативності). docker exec buliak-wp php /tmp/img_webp2.php */
$A = '/var/www/html/wp-content/themes/buliak-astra/assets';
foreach ( array( 'sausages', 'set_feast', 'set_galician' ) as $name ) {
	$src = "$A/$name.png";
	if ( ! file_exists( $src ) ) { echo "skip $name (нема)\n"; continue; }
	$i = new Imagick( $src );
	$i->setImageColorspace( Imagick::COLORSPACE_SRGB );
	$i->resizeImage( 1600, 0, Imagick::FILTER_LANCZOS, 1 );
	$i->setImageFormat( 'webp' );
	$i->setImageCompressionQuality( 82 );
	$i->stripImage();
	$i->writeImage( "$A/$name.webp" );
	echo "$name.webp " . round( filesize( "$A/$name.webp" ) / 1024 ) . "KB\n";
}
