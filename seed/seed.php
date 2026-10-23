<?php
/**
 * Наполнение сайта демонстрационным контентом.
 * Запуск: docker compose run --rm wpcli wp eval-file /seed/seed.php
 *
 * Скрипт идемпотентный: повторный запуск не создаёт дублей.
 * Все цены, характеристики проектов и контакты — заполнители, их нужно заменить реальными.
 */

defined( 'ABSPATH' ) || exit;

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$log = function ( $msg ) {
	WP_CLI::log( $msg );
};

/* ---------- Изображения ---------- */

// Демо-фото CC0 больше не используются: все изображения — из реальных проектов (sinergiya.php, portfolio.php).
$alts = array();

$img = array();
foreach ( $alts as $name => $alt ) {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_key'    => '_rmt_seed_file',
			'meta_value'  => $name,
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);
	if ( $existing ) {
		$img[ $name ] = $existing[0];
		continue;
	}
	$src = '/seed/images/' . $name . '.jpg';
	if ( ! file_exists( $src ) ) {
		$log( "Нет файла {$src}, пропускаю" );
		continue;
	}
	$tmp = wp_tempnam( $name . '.jpg' );
	copy( $src, $tmp );
	$id = media_handle_sideload(
		array(
			'name'     => $name . '.jpg',
			'tmp_name' => $tmp,
		),
		0,
		$alt
	);
	if ( is_wp_error( $id ) ) {
		$log( 'Ошибка загрузки ' . $name . ': ' . $id->get_error_message() );
		continue;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_rmt_seed_file', $name );
	$img[ $name ] = $id;
	$log( "Фото: {$name}" );
}

$i = function ( $name ) use ( $img ) {
	return $img[ $name ] ?? 0;
};

/* ---------- Помощник: создать запись, если её ещё нет ---------- */

$upsert = function ( $post_type, $slug, $data, $meta = array(), $thumb = 0 ) {
	$found = get_page_by_path( $slug, OBJECT, $post_type );
	$args  = array_merge(
		array(
			'post_type'   => $post_type,
			'post_name'   => $slug,
			'post_status' => 'publish',
		),
		$data
	);
	if ( $found ) {
		return $found->ID; // Не перезаписываем правки администратора.
	}
	$id = wp_insert_post( wp_slash( $args ), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::warning( $id->get_error_message() );
		return 0;
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, '_rmt_' . $k, $v );
	}
	if ( $thumb ) {
		set_post_thumbnail( $id, $thumb );
	}
	WP_CLI::log( "Создано ({$post_type}): {$data['post_title']}" );
	return $id;
};

/* ---------- Виды ремонта и услуги ---------- */

$services = array(
	array(
		'slug'     => 'kosmeticheskij-remont',
		'title'    => 'Косметический ремонт',
		'calc'     => 'econom',
		'kicker'   => 'Освежить без перепланировки',
		'image'    => 'living-bright',
		'excerpt'  => 'Обновляем стены, потолки и полы, когда инженерия в порядке и менять планировку не нужно.',
		'includes' => "Защита мебели и полов на время работ\nПодготовка и выравнивание стен под покраску или обои\nПокраска потолков или натяжные потолки\nЗамена напольного покрытия и плинтусов\nЗамена розеток, выключателей и светильников\nУборка после работ",
		'content'  => "<p>Косметический ремонт подходит, если квартира в хорошем техническом состоянии: проводка и трубы не требуют замены, планировка устраивает, а интерьер хочется обновить.</p>\n<h2>Когда выбирают косметический ремонт</h2>\n<ul><li>перед заездом в купленную квартиру на вторичном рынке;</li><li>перед сдачей квартиры в аренду или продажей;</li><li>когда отделка устала, но перестраивать ничего не нужно.</li></ul>\n<h2>Как считаем стоимость</h2>\n<p>После замера составляем смету по видам работ и площадям. Материалы можно закупить у нас или по вашему списку — в смете это отражается отдельными строками.</p>",
	),
	array(
		'slug'     => 'kapitalnyj-remont',
		'title'    => 'Капитальный ремонт',
		'calc'     => 'standard',
		'kicker'   => 'С заменой инженерии',
		'image'    => 'works-floor',
		'excerpt'  => 'Демонтаж до основания, новая электрика и сантехника, выравнивание стен и полов, чистовая отделка.',
		'includes' => "Демонтаж старой отделки и вывоз мусора\nНовая электропроводка и щит\nРазводка водоснабжения и канализации\nСтяжка пола и штукатурка стен\nГидроизоляция мокрых зон\nЧистовая отделка всех помещений",
		'content'  => "<p>Капитальный ремонт нужен, когда отделка и инженерные сети устарели. Квартиру освобождают до бетона и собирают заново — с новой проводкой, трубами, ровными стенами и полами.</p>\n<h2>Что важно в капитальном ремонте</h2>\n<p>Самые ответственные работы скрываются под отделкой. Поэтому электрику, сантехнику, гидроизоляцию и стяжку мы принимаем отдельно и фотографируем до закрытия.</p>\n<h2>Перепланировка</h2>\n<p>Если планируется перенос перегородок или мокрых зон, подскажем, какие изменения требуют согласования, и учтём это в графике работ.</p>",
	),
	array(
		'slug'     => 'dizajnerskij-remont',
		'title'    => 'Дизайнерский ремонт',
		'calc'     => 'premium',
		'kicker'   => 'По дизайн-проекту',
		'image'    => 'bedroom-wood',
		'excerpt'  => 'Реализуем интерьер по дизайн-проекту: сложные потолки, скрытые двери, подсветка, авторские решения.',
		'includes' => "Разработка или адаптация дизайн-проекта\nВедомость материалов и комплектация\nСложные потолки, ниши и подсветка\nСкрытые двери и плинтусы\nАвторский надзор и согласование решений\nДекоративная отделка",
		'content'  => "<p>Дизайнерский ремонт — это точная реализация проекта: каждое решение заранее прорисовано, а материалы подобраны и посчитаны.</p>\n<h2>Работаем с вашим проектом или делаем свой</h2>\n<p>Если проект уже есть, проверим его на реализуемость и составим смету. Если нет — подготовим планировку, визуализации и рабочие чертежи.</p>\n<h2>Комплектация</h2>\n<p>Материалы и мебель закупаются по ведомости, поставки согласуются с графиком работ, чтобы бригада не простаивала.</p>",
	),
	array(
		'slug'     => 'remont-v-novostrojke',
		'title'    => 'Ремонт в новостройке',
		'calc'     => 'standard',
		'kicker'   => 'С нуля или от предчистовой',
		'image'    => 'living-concrete',
		'excerpt'  => 'Ремонт квартиры от застройщика: без отделки, с предчистовой или White Box отделкой.',
		'includes' => "Приёмка квартиры и проверка геометрии\nЭлектрика и сантехника под вашу расстановку\nШумоизоляция по желанию\nСтяжка и выравнивание стен\nЧистовая отделка и установка дверей\nМонтаж сантехники и светильников",
		'content'  => "<p>Квартира в новостройке — хорошая возможность сразу сделать всё под себя: расставить розетки под мебель, продумать свет и хранение.</p>\n<h2>От какого состояния начинаем</h2>\n<ul><li><strong>Без отделки</strong> — работы начинаются с разводки сетей и стяжки.</li><li><strong>Предчистовая (White Box)</strong> — часть черновых работ уже выполнена застройщиком, проверяем её качество и переходим к чистовой отделке.</li></ul>\n<h2>Усадка дома</h2>\n<p>Подскажем, какие решения снижают риск трещин в первые годы после сдачи дома.</p>",
	),
	array(
		'slug'     => 'remont-pod-klyuch',
		'title'    => 'Ремонт под ключ',
		'calc'     => 'standard',
		'kicker'   => 'От замера до уборки',
		'image'    => 'kitchen-island',
		'excerpt'  => 'Берём на себя весь цикл: замер, смету, черновые и чистовые работы, закупку материалов и уборку.',
		'includes' => "Замер и составление сметы\nГрафик работ в договоре\nЧерновые и чистовые работы\nЗакупка и доставка материалов\nМонтаж сантехники, дверей и света\nФинальная уборка и приёмка по чек-листу",
		'content'  => "<p>Ремонт под ключ — когда вы хотите получить готовую квартиру, а не управлять стройкой. Один прораб координирует мастеров, закупки и сроки.</p>\n<h2>Как идёт работа</h2>\n<p>Перед стартом фиксируем смету и график. Дальше вы получаете фотоотчёты по этапам и принимаете работы по чек-листу.</p>\n<h2>Что остаётся за вами</h2>\n<p>Выбрать материалы и согласовать ключевые решения. Всё остальное — на нашей стороне.</p>",
	),
);

$term_ids = array();
foreach ( $services as $order => $s ) {
	$term = term_exists( $s['slug'], 'repair_type' );
	if ( ! $term ) {
		$term = wp_insert_term( $s['title'], 'repair_type', array( 'slug' => $s['slug'] ) );
	}
	if ( ! is_wp_error( $term ) ) {
		$term_ids[ $s['slug'] ] = (int) $term['term_id'];
		update_term_meta( (int) $term['term_id'], 'calc_type', $s['calc'] );
	}

	$upsert(
		'service',
		$s['slug'],
		array(
			'post_title'   => $s['title'],
			'post_excerpt' => $s['excerpt'],
			'post_content' => $s['content'],
			'menu_order'   => $order,
		),
		array(
			'kicker'    => $s['kicker'],
			'includes'  => $s['includes'],
			'calc_type' => $s['calc'],
		),
		$i( $s['image'] )
	);
}

/* ---------- Вопросы и ответы ---------- */

$faq = array(
	array( 'Сколько стоит ремонт квартиры?', 'Стоимость зависит от типа ремонта, площади, состояния квартиры и выбранных материалов. Калькулятор на сайте даёт предварительную оценку, точную сумму покажет смета после замера.' ),
	array( 'Выезд замерщика платный?', 'Условия выезда уточните у менеджера — их нужно указать здесь, когда они будут утверждены.' ),
	array( 'Может ли стоимость измениться во время ремонта?', 'Объём и цена работ фиксируются в смете. Если по ходу ремонта появляются новые задачи, мы сначала согласуем их и стоимость письменно и только потом выполняем.' ),
	array( 'Кто закупает материалы?', 'Можно доверить закупку нам — материалы привезут к нужному этапу. Можно закупать самостоятельно по нашему списку. Оба варианта отражаются в смете.' ),
	array( 'Как контролировать ход работ, если я не могу приезжать?', 'Прораб присылает фотоотчёты по этапам, а скрытые работы фотографируются до закрытия. Вопросы можно задать в мессенджере.' ),
	array( 'Сколько длится ремонт?', 'Срок зависит от площади и объёма работ. Точный график составляется после замера и фиксируется в договоре.' ),
	array( 'Даёте ли вы гарантию?', 'Условия и срок гарантии указываются в договоре. Уточните их у менеджера — здесь нужно будет указать утверждённые условия.' ),
);
foreach ( $faq as $order => list( $q, $a ) ) {
	$exists = get_posts(
		array(
			'post_type'   => 'faq',
			'title'       => $q,
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( ! $exists ) {
		wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'faq',
					'post_title'   => $q,
					'post_content' => $a,
					'post_status'  => 'publish',
					'menu_order'   => $order,
				)
			)
		);
	}
}

/* ---------- Страницы ---------- */

$home = $upsert( 'page', 'glavnaya', array( 'post_title' => 'Главная', 'post_content' => '' ) );

$about = $upsert(
	'page',
	'o-kompanii',
	array(
		'post_title'   => 'Ремонт, который можно планировать',
		'post_excerpt' => 'Организуем ремонт квартир так, чтобы вы заранее знали состав работ, стоимость и сроки — и видели, что происходит на объекте.',
		'post_content' => "<p>Здесь будет рассказ о компании: когда и кем основана, в каком регионе работает, какие объекты ведёт. Сейчас — заполнитель, который нужно заменить реальными данными.</p>\n<p>Наш подход строится на трёх вещах: подробная смета до начала работ, понятный график и один ответственный за объект. Изменения согласуются письменно, а ход работ виден по фотоотчётам.</p>\n<h2>Процесс организации ремонта</h2>\n<p>Работа начинается с замера и обсуждения задачи. Затем — смета и договор с графиком. Черновые работы принимаются по этапам, после чистовой отделки объект сдаётся по чек-листу.</p>\n<h2>Контроль качества</h2>\n<p>Скрытые работы — электрику, сантехнику, гидроизоляцию и стяжку — фотографируем до закрытия. Каждый этап прораб проверяет по чек-листу, при сдаче подписывается акт.</p>",
	),
	array(),
	$i( 'works-site' )
);
if ( $about ) {
	update_post_meta( $about, '_wp_page_template', 'page-templates/about.php' );
}

$contacts = $upsert(
	'page',
	'kontakty',
	array(
		'post_title'   => 'Контакты',
		'post_excerpt' => 'Позвоните или оставьте заявку — расскажем, как проходит ремонт, и договоримся о замере.',
		'post_content' => '<p>Реквизиты компании и схема проезда — заполнитель. Укажите их, когда данные будут подтверждены.</p>',
	),
	array(),
	$i( 'office-white' )
);
if ( $contacts ) {
	update_post_meta( $contacts, '_wp_page_template', 'page-templates/contacts.php' );
}

$privacy = $upsert(
	'page',
	'politika-konfidencialnosti',
	array(
		'post_title'   => 'Политика конфиденциальности',
		'post_content' => "<p><strong>Это шаблон-заполнитель. Перед запуском сайта текст политики должен подготовить или проверить юрист.</strong></p>\n<h2>Какие данные мы получаем</h2>\n<p>Через форму заявки: имя, телефон, тип ремонта, площадь и комментарий. Эти данные используются только для связи по заявке.</p>\n<h2>Где хранятся данные</h2>\n<p>Заявки хранятся в системе управления сайтом и доступны только администраторам. Данные из заявок не передаются сторонним сервисам.</p>\n<h2>Как отозвать согласие</h2>\n<p>Позвоните по телефону, указанному в разделе «Контакты», — мы удалим ваши данные.</p>",
	)
);
if ( $privacy ) {
	update_option( 'wp_page_for_privacy_policy', $privacy );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );

/* ---------- Меню ---------- */

$make_menu = function ( $name, $location, $items ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		$locations              = get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $menu->term_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		return;
	}
	$menu_id = wp_create_nav_menu( $name );
	foreach ( $items as $item ) {
		$parent = wp_update_nav_menu_item( $menu_id, 0, $item['args'] );
		foreach ( $item['children'] ?? array() as $child ) {
			$child['menu-item-parent-id'] = $parent;
			wp_update_nav_menu_item( $menu_id, 0, $child );
		}
	}
	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
	WP_CLI::log( "Меню: {$name}" );
};

$service_children = array();
foreach ( $services as $s ) {
	$post = get_page_by_path( $s['slug'], OBJECT, 'service' );
	if ( $post ) {
		$service_children[] = array(
			'menu-item-object-id' => $post->ID,
			'menu-item-object'    => 'service',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		);
	}
}

$page_item = function ( $id ) {
	return array(
		'menu-item-object-id' => $id,
		'menu-item-object'    => 'page',
		'menu-item-type'      => 'post_type',
		'menu-item-status'    => 'publish',
	);
};

$make_menu(
	'Главное меню',
	'primary',
	array(
		array(
			'args'     => array(
				'menu-item-title'  => 'Услуги',
				'menu-item-type'   => 'post_type_archive',
				'menu-item-object' => 'service',
				'menu-item-status' => 'publish',
			),
			'children' => $service_children,
		),
		array(
			'args' => array(
				'menu-item-title'  => 'Портфолио',
				'menu-item-type'   => 'post_type_archive',
				'menu-item-object' => 'project',
				'menu-item-status' => 'publish',
			),
		),
		array(
			'args' => array(
				'menu-item-title'  => 'Стоимость',
				'menu-item-url'    => '/#calculator',
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			),
		),
		array( 'args' => array_merge( $page_item( $about ), array( 'menu-item-title' => 'О компании' ) ) ),
		array( 'args' => $page_item( $contacts ) ),
	)
);

$make_menu(
	'Подвал: компания',
	'footer',
	array(
		array( 'args' => array_merge( $page_item( $about ), array( 'menu-item-title' => 'О компании' ) ) ),
		array(
			'args' => array(
				'menu-item-title'  => 'Как мы работаем',
				'menu-item-url'    => '/#steps',
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			),
		),
		array(
			'args' => array(
				'menu-item-title'  => 'Вопросы и ответы',
				'menu-item-url'    => '/#faq',
				'menu-item-type'   => 'custom',
				'menu-item-status' => 'publish',
			),
		),
		array( 'args' => $page_item( $contacts ) ),
	)
);

/* ---------- Настройки темы и сайта ---------- */


update_option( 'blogname', 'DOMO' );

// Контакты компании.
set_theme_mod( 'phone', '+7 (906) 047-24-67' );

// Логотип DOMO (файл темы → медиатека → «Свойства сайта → Логотип»).
if ( ! get_theme_mod( 'custom_logo' ) ) {
	$logo_src = get_template_directory() . '/assets/img/logo-domo.png';
	$logo_tmp = wp_tempnam( 'logo-domo.png' );
	copy( $logo_src, $logo_tmp );
	$logo_id = media_handle_sideload( array( 'name' => 'logo-domo.png', 'tmp_name' => $logo_tmp ), 0, 'DOMO' );
	if ( ! is_wp_error( $logo_id ) ) {
		update_post_meta( $logo_id, '_wp_attachment_image_alt', 'DOMO' );
		set_theme_mod( 'custom_logo', $logo_id );
	}
}

// Значок сайта (favicon) — домик из логотипа, 512×512.
if ( ! get_option( 'site_icon' ) ) {
	$icon_tmp = wp_tempnam( 'site-icon.png' );
	copy( get_template_directory() . '/assets/img/site-icon.png', $icon_tmp );
	$icon_id = media_handle_sideload( array( 'name' => 'site-icon.png', 'tmp_name' => $icon_tmp ), 0, 'DOMO' );
	if ( ! is_wp_error( $icon_id ) ) {
		update_option( 'site_icon', $icon_id );
	}
}
update_option( 'blogdescription', 'Ремонт квартир и домов' );
update_option( 'timezone_string', 'Europe/Moscow' );
update_option( 'date_format', 'j F Y' );
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );

// Стандартные демо-записи WordPress не нужны.
foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
	$p = get_page_by_path( $slug, OBJECT, $type );
	if ( $p ) {
		wp_delete_post( $p->ID, true );
	}
}

// Реальный проект: фото главной, услуг и страниц берутся из него.
require __DIR__ . '/sinergiya.php';
require __DIR__ . '/portfolio.php';
require __DIR__ . '/hero-slides.php';
require __DIR__ . '/policy-metrika.php';

flush_rewrite_rules();
WP_CLI::success( 'Демо-контент готов.' );
