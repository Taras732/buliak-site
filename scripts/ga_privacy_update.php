<?php
/* Оновлення Політики конфіденційності (page 3) під підключений Google Analytics.
 * Запуск: cat | docker run ... wordpress:cli wp eval-file - */
if ( ! function_exists( 'wp_update_post' ) ) { WP_CLI::error( 'WP off' ); }
$id = 3; $post = get_post( $id );
if ( ! $post ) { WP_CLI::error( 'no page 3' ); }
$c = $post->post_content;

$c = str_replace(
	'Систем веб-аналітики чи рекламного відстеження (Google Analytics, Meta Pixel тощо) ми не використовуємо.',
	'Для аналізу відвідуваності сайту ми використовуємо <strong>Google Analytics (GA4)</strong> — він встановлює власні cookie зі знеособленою статистикою. Рекламного відстеження (Meta Pixel тощо) ми не використовуємо.',
	$c
);
$c = str_replace(
	'<li>сторонні cookie Google Maps — при перегляді карти на сторінці контактів.</li>',
	'<li>сторонні cookie Google Maps — при перегляді карти на сторінці контактів;</li>' . "\n" . '<li>аналітичні cookie Google Analytics — для статистики відвідувань.</li>',
	$c
);
$c = str_replace(
	'<li>месенджери (Telegram) — для повідомлень менеджеру про ваше замовлення.</li>',
	'<li>месенджери (Telegram) — для повідомлень менеджеру про ваше замовлення;</li>' . "\n" . '<li>Google (Google Analytics) — знеособлена статистика відвідувань сайту.</li>',
	$c
);

wp_update_post( array( 'ID' => $id, 'post_content' => $c ) );
WP_CLI::success( 'Privacy оновлено під GA' );
