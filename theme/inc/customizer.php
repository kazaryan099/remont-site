<?php
/**
 * Настройки в «Внешний вид → Настроить»: контакты компании и тексты главной страницы.
 * Списки задаются построчно в формате «Заголовок | Текст».
 *
 * @package Remont
 */

defined( 'ABSPATH' ) || exit;

/**
 * Значения по умолчанию. Контактные данные — заведомые заполнители.
 */
function rmt_defaults() {
	return array(
		// Контакты.
		'phone'            => '+7 (000) 000-00-00',
		'email'            => '',
		'region'           => 'Москва и Московская область',
		'address'          => '',
		'schedule'         => 'Пн–Пт 9:00–19:00, Сб–Вс 10:00–16:00',
		'telegram'         => '',
		'whatsapp'         => '',
		'legal'            => '',
		'schema_confirmed' => false,
		'metrika_id'       => '113714464',

		// Герой.
		'hero_kicker'      => 'Ремонт квартир под ключ',
		'hero_title'       => 'Ремонт квартир и домов',
		'hero_text'        => '',
		'home_desc'        => 'Ремонт квартир и домов под ключ в Москве и Московской области: замер, смета, черновые и чистовые работы, закупка материалов и уборка. Этапы и стоимость фиксируем в договоре.',
		'hero_btn'         => 'Рассчитать стоимость',
		'hero_link'        => 'Получить смету бесплатно',
		'hero_image'       => 0,
		'hero_image_2'     => 0,
		'hero_image_3'     => 0,
		'hero_image_4'     => 0,
		'hero_image_5'     => 0,
		'facts'          => "Смета до старта | объём и цена фиксируются в договоре\nПоэтапная оплата | платите за выполненный этап\nФотоотчёты | показываем ход работ на объекте\nОдин ответственный | прораб ведёт объект от замера до сдачи\nМатериалы | закупаем и привозим сами или по вашему списку",

		// Секции главной.
		'services_title'   => 'Какой ремонт нужен',
		'featured_project' => 0,
		'projects_kicker'  => 'Портфолио',
		'projects_title'   => 'Наши проекты',
		'projects_text'    => 'Реализованные ремонты и дизайн-проекты: фото, визуализации, площадь и состав работ.',
		'calc_kicker'      => 'Калькулятор',
		'calc_title'       => 'Сколько стоит ремонт',
		'calc_text'        => 'Выберите тип ремонта и укажите площадь — калькулятор покажет предварительную стоимость и состав работ. Точную стоимость покажет смета после замера.',
		'steps_kicker'     => 'Как мы работаем',
		'steps_title'      => 'Шесть этапов ремонта',
		'steps_text'       => 'На каждом этапе понятно, что делается, сколько это стоит и кто отвечает за результат.',
		'steps'            => "Замер и консультация | Выезжаем на объект, обсуждаем задачу, делаем обмеры и фото.\nСмета и договор | Составляем подробную смету, согласуем сроки и порядок оплаты.\nЧерновые работы | Демонтаж, перегородки, электрика, сантехника, стяжка и штукатурка.\nЧистовая отделка | Плитка, покраска, напольные покрытия, двери и потолки.\nКонтроль качества | Прораб принимает каждый этап, вы получаете фотоотчёт.\nСдача объекта | Уборка, приёмка по чек-листу и подписание акта.",
		'adv_title'        => 'Почему с нами спокойно',
		'adv_text'         => 'Ремонт — это сотни решений. Мы делаем так, чтобы вы принимали только главные из них, а остальное было под контролем.',
		'advantages'       => "Понятная смета | Каждая позиция расписана: работы, материалы, количество. Изменения согласуются письменно до начала работ.\nСроки в договоре | График работ согласуется до старта. Вы знаете, когда начнётся и закончится каждый этап.\nОдин ответственный | Прораб ведёт объект целиком: координирует мастеров, закупки и отвечает на ваши вопросы.",
		'quality_kicker'   => 'Контроль качества',
		'quality_title'    => 'Проверяем то, что потом не увидеть',
		'quality_text'     => 'Скрытые работы — электрика, гидроизоляция, стяжка — определяют, сколько прослужит ремонт. Поэтому их мы принимаем отдельно и фиксируем до закрытия.',
		'quality'          => "Скрытые работы | фото до закрытия\nЧек-листы | на каждом этапе\nАкт приёмки | при сдаче объекта",
		'quality_image'    => 0,
		'faq_title'        => 'Частые вопросы',
		'form_title'       => 'Бесплатный дизайн-проект для тех, кто оставил заявку на сайте',
		'form_text'        => 'Расскажите о квартире и задаче — перезвоним, уточним детали и договоримся о выезде замерщика.',
		'form_steps'       => "Оставьте телефон и коротко опишите задачу\nУточним детали и договоримся о замере\nПосле замера пришлём подробную смету",
		'cta_title'        => 'Начнём с замера',
		'cta_text'         => 'Приедем, посмотрим квартиру и обсудим задачу. После замера — подробная смета и график работ.',
		'cta_image'        => 0,
	);
}

/** Значение настройки с учётом значения по умолчанию. */
function rmt_opt( $key ) {
	$defaults = rmt_defaults();
	return get_theme_mod( $key, $defaults[ $key ] ?? '' );
}

/**
 * Список из многострочной настройки «Заголовок | Текст».
 *
 * @return array<int, array{title:string,text:string}>
 */
function rmt_opt_pairs( $key ) {
	$items = array();
	foreach ( explode( "\n", (string) rmt_opt( $key ) ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts   = array_map( 'trim', explode( '|', $line, 2 ) );
		$items[] = array(
			'title' => $parts[0],
			'text'  => $parts[1] ?? '',
		);
	}
	return $items;
}

/**
 * Фото карусели первого экрана: заполненные поля «Фон: фото 1–5» по порядку.
 *
 * @return int[]
 */
function rmt_hero_images() {
	$ids = array();
	foreach ( array( 'hero_image', 'hero_image_2', 'hero_image_3', 'hero_image_4', 'hero_image_5' ) as $key ) {
		$id = (int) rmt_opt( $key );
		if ( $id && wp_attachment_is_image( $id ) && ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * Опубликованный проект, в галерее (или на обложке) которого есть это фото — для подписи в карусели.
 *
 * @return WP_Post|null
 */
function rmt_image_project( $attachment_id ) {
	static $map = null;
	if ( null === $map ) {
		$map = array();
		foreach ( get_posts( array( 'post_type' => 'project', 'posts_per_page' => 100, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $project ) {
			foreach ( array_merge( rmt_gallery_ids( $project->ID ), array( (int) get_post_thumbnail_id( $project ) ) ) as $id ) {
				if ( $id && ! isset( $map[ $id ] ) ) {
					$map[ $id ] = $project;
				}
			}
		}
	}
	return $map[ (int) $attachment_id ] ?? null;
}

add_action( 'customize_register', 'rmt_customize_register' );
function rmt_customize_register( WP_Customize_Manager $wp_customize ) {
	$defaults = rmt_defaults();

	$wp_customize->add_panel(
		'rmt_panel',
		array(
			'title'    => __( 'Сайт ремонтной компании', 'remont' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'rmt_contacts' => array(
			'title'  => __( 'Контакты и реквизиты', 'remont' ),
			'fields' => array(
				'phone'            => array( 'text', __( 'Телефон', 'remont' ) ),
				'email'            => array( 'email', __( 'Электронная почта', 'remont' ) ),
				'region'           => array( 'text', __( 'Регион работы', 'remont' ) ),
				'address'          => array( 'text', __( 'Адрес офиса (необязательно)', 'remont' ) ),
				'schedule'         => array( 'text', __( 'График работы', 'remont' ) ),
				'telegram'         => array( 'url', __( 'Ссылка на Telegram', 'remont' ) ),
				'whatsapp'         => array( 'url', __( 'Ссылка на WhatsApp', 'remont' ) ),
				'legal'            => array( 'text', __( 'Юридическое лицо и ИНН (подвал)', 'remont' ) ),
				'schema_confirmed' => array( 'checkbox', __( 'Контакты проверены — выводить их в микроразметку для поисковиков', 'remont' ) ),
			),
		),
		'rmt_analytics' => array(
			'title'  => __( 'Аналитика', 'remont' ),
			'fields' => array(
				'metrika_id' => array( 'text', __( 'Номер счётчика Яндекс Метрики (пусто — счётчик выключен). Работает только на боевом сайте.', 'remont' ) ),
			),
		),
		'rmt_hero'     => array(
			'title'  => __( 'Главная: первый экран', 'remont' ),
			'fields' => array(
				'hero_kicker' => array( 'text', __( 'Надзаголовок', 'remont' ) ),
				'hero_title'  => array( 'text', __( 'Заголовок (H1)', 'remont' ) ),
				'hero_text'   => array( 'textarea', __( 'Подзаголовок (пусто — не показывается)', 'remont' ) ),
				'home_desc'   => array( 'textarea', __( 'Описание главной для поисковиков (на странице не показывается)', 'remont' ) ),
				'hero_btn'    => array( 'text', __( 'Текст кнопки', 'remont' ) ),
				'hero_link'   => array( 'text', __( 'Текст ссылки', 'remont' ) ),
				'hero_image'   => array( 'image', __( 'Фон: фото 1 (оно же — картинка для соцсетей)', 'remont' ) ),
				'hero_image_2' => array( 'image', __( 'Фон: фото 2', 'remont' ) ),
				'hero_image_3' => array( 'image', __( 'Фон: фото 3', 'remont' ) ),
				'hero_image_4' => array( 'image', __( 'Фон: фото 4', 'remont' ) ),
				'hero_image_5' => array( 'image', __( 'Фон: фото 5', 'remont' ) ),
				'facts'       => array( 'textarea', __( 'Полоса фактов (Заголовок | Подпись, по строке на пункт)', 'remont' ) ),
			),
		),
		'rmt_sections' => array(
			'title'  => __( 'Главная: секции', 'remont' ),
			'fields' => array(
				'services_title'  => array( 'text', __( 'Услуги: заголовок', 'remont' ) ),
				'featured_project' => array( 'project', __( 'Реализованный проект на главной', 'remont' ) ),
				'projects_kicker' => array( 'text', __( 'Портфолио: надзаголовок', 'remont' ) ),
				'projects_title'  => array( 'text', __( 'Портфолио: заголовок', 'remont' ) ),
				'projects_text'   => array( 'textarea', __( 'Портфолио: описание', 'remont' ) ),
				'calc_kicker'     => array( 'text', __( 'Калькулятор: надзаголовок', 'remont' ) ),
				'calc_title'      => array( 'text', __( 'Калькулятор: заголовок', 'remont' ) ),
				'calc_text'       => array( 'textarea', __( 'Калькулятор: описание', 'remont' ) ),
				'steps_kicker'    => array( 'text', __( 'Этапы: надзаголовок', 'remont' ) ),
				'steps_title'     => array( 'text', __( 'Этапы: заголовок', 'remont' ) ),
				'steps_text'      => array( 'textarea', __( 'Этапы: описание', 'remont' ) ),
				'steps'           => array( 'textarea', __( 'Этапы (Заголовок | Текст)', 'remont' ) ),
				'adv_title'       => array( 'text', __( 'Преимущества: заголовок', 'remont' ) ),
				'adv_text'        => array( 'textarea', __( 'Преимущества: описание', 'remont' ) ),
				'advantages'      => array( 'textarea', __( 'Преимущества (Заголовок | Текст)', 'remont' ) ),
				'quality_kicker'  => array( 'text', __( 'Контроль качества: надзаголовок', 'remont' ) ),
				'quality_title'   => array( 'text', __( 'Контроль качества: заголовок', 'remont' ) ),
				'quality_text'    => array( 'textarea', __( 'Контроль качества: текст', 'remont' ) ),
				'quality'         => array( 'textarea', __( 'Контроль качества: пункты (Заголовок | Подпись)', 'remont' ) ),
				'quality_image'   => array( 'image', __( 'Контроль качества: фото', 'remont' ) ),
				'faq_title'       => array( 'text', __( 'Вопросы: заголовок', 'remont' ) ),
				'form_title'      => array( 'text', __( 'Форма: заголовок', 'remont' ) ),
				'form_text'       => array( 'textarea', __( 'Форма: описание', 'remont' ) ),
				'form_steps'      => array( 'textarea', __( 'Форма: шаги (по строке)', 'remont' ) ),
				'cta_title'       => array( 'text', __( 'Финальный блок: заголовок', 'remont' ) ),
				'cta_text'        => array( 'textarea', __( 'Финальный блок: текст', 'remont' ) ),
				'cta_image'       => array( 'image', __( 'Финальный блок: фото', 'remont' ) ),
			),
		),
	);

	$sanitizers = array(
		'text'     => 'sanitize_text_field',
		'textarea' => 'sanitize_textarea_field',
		'email'    => 'sanitize_email',
		'url'      => 'esc_url_raw',
		'image'    => 'absint',
		'project'  => 'absint',
		'checkbox' => 'rest_sanitize_boolean',
	);

	$project_choices = array( 0 => __( '— первый проект без пометки «Демо» —', 'remont' ) );
	foreach ( get_posts( array( 'post_type' => 'project', 'posts_per_page' => 100, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
		$project_choices[ $p->ID ] = get_the_title( $p );
	}

	$priority = 10;
	foreach ( $sections as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title' => $section['title'],
				'panel' => 'rmt_panel',
			)
		);

		foreach ( $section['fields'] as $key => list( $type, $label ) ) {
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $defaults[ $key ],
					'sanitize_callback' => $sanitizers[ $type ],
				)
			);

			if ( 'image' === $type ) {
				$wp_customize->add_control(
					new WP_Customize_Media_Control(
						$wp_customize,
						$key,
						array(
							'label'     => $label,
							'section'   => $section_id,
							'mime_type' => 'image',
						)
					)
				);
				continue;
			}

			$wp_customize->add_control(
				$key,
				array(
					'label'    => $label,
					'section'  => $section_id,
					'type'     => 'project' === $type ? 'select' : $type,
					'choices'  => 'project' === $type ? $project_choices : array(),
					'priority' => $priority++,
				)
			);
		}
	}
}
