/**
 * Тема Remont: шапка, меню, слайдеры, просмотр фото, отправка заявки.
 * Без зависимостей. Всё работает и без JS — скрипт только улучшает поведение.
 */
(function () {
	'use strict';

	var data = window.rmtData || { ajaxUrl: '', i18n: {} };

	/* ---------- Тень шапки при прокрутке ---------- */
	var header = document.querySelector('[data-header]');
	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 8);
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	/* ---------- Подменю: кнопки-стрелки ---------- */
	document.addEventListener('click', function (e) {
		var toggle = e.target.closest('.submenu-toggle');
		if (!toggle) {
			return;
		}
		var li = toggle.closest('li');
		var open = !li.classList.contains('is-open');
		li.parentElement.querySelectorAll(':scope > li.is-open').forEach(function (other) {
			if (other !== li) {
				other.classList.remove('is-open');
				var t = other.querySelector('.submenu-toggle');
				if (t) { t.setAttribute('aria-expanded', 'false'); }
			}
		});
		li.classList.toggle('is-open', open);
		toggle.setAttribute('aria-expanded', String(open));
	});

	// Закрываем открытое подменю при клике вне его и по Esc.
	document.addEventListener('click', function (e) {
		if (e.target.closest('.main-nav')) {
			return;
		}
		document.querySelectorAll('.main-nav li.is-open').forEach(function (li) {
			li.classList.remove('is-open');
			var t = li.querySelector('.submenu-toggle');
			if (t) { t.setAttribute('aria-expanded', 'false'); }
		});
	});

	/* ---------- Мобильное меню ---------- */
	var menu = document.querySelector('[data-menu]');
	var opener = document.querySelector('[data-menu-open]');
	var lastFocus = null;

	function focusables(root) {
		return Array.prototype.filter.call(
			root.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])'),
			function (el) { return el.offsetParent !== null; }
		);
	}

	function openMenu() {
		lastFocus = document.activeElement;
		menu.hidden = false;
		document.body.classList.add('menu-open');
		opener.setAttribute('aria-expanded', 'true');
		var first = menu.querySelector('[data-menu-close]');
		if (first) { first.focus(); }
	}

	function closeMenu(restoreFocus) {
		menu.hidden = true;
		document.body.classList.remove('menu-open');
		opener.setAttribute('aria-expanded', 'false');
		if (restoreFocus !== false && lastFocus) { lastFocus.focus(); }
	}

	if (menu && opener) {
		opener.addEventListener('click', openMenu);
		menu.addEventListener('click', function (e) {
			var closer = e.target.closest('[data-menu-close]');
			var link = e.target.closest('a[href]');
			if (closer) {
				closeMenu(!link);
			} else if (link && link.getAttribute('href').indexOf('#') !== -1) {
				closeMenu(false); // Переход к якорю на этой же странице.
			}
		});
		menu.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				closeMenu();
				return;
			}
			if (e.key !== 'Tab') {
				return;
			}
			var items = focusables(menu);
			if (!items.length) { return; }
			var first = items[0];
			var last = items[items.length - 1];
			if (e.shiftKey && document.activeElement === first) {
				e.preventDefault();
				last.focus();
			} else if (!e.shiftKey && document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		});
		window.matchMedia('(min-width: 1100px)').addEventListener('change', function (mq) {
			if (mq.matches && !menu.hidden) { closeMenu(false); }
		});
	}

	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		document.querySelectorAll('.main-nav li.is-open').forEach(function (li) {
			li.classList.remove('is-open');
			var t = li.querySelector('.submenu-toggle');
			if (t) { t.setAttribute('aria-expanded', 'false'); t.focus(); }
		});
	});

	/* ---------- Горизонтальные ленты ---------- */
	document.querySelectorAll('[data-slider]').forEach(function (slider) {
		var track = slider.querySelector('.slider__track');
		var prev = slider.querySelector('[data-slider-prev]');
		var next = slider.querySelector('[data-slider-next]');
		if (!track || !prev || !next) { return; }

		var step = function () {
			var item = track.querySelector('.slider__item');
			var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
			return item ? item.getBoundingClientRect().width + gap : track.clientWidth;
		};
		var update = function () {
			var max = track.scrollWidth - track.clientWidth - 2;
			prev.disabled = track.scrollLeft <= 2;
			next.disabled = track.scrollLeft >= max;
			slider.querySelector('.slider__nav').hidden = max <= 0;
		};
		prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
		next.addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });
		track.addEventListener('scroll', update, { passive: true });
		track.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { e.preventDefault(); track.scrollBy({ left: step(), behavior: 'smooth' }); }
			if (e.key === 'ArrowLeft') { e.preventDefault(); track.scrollBy({ left: -step(), behavior: 'smooth' }); }
		});
		window.addEventListener('resize', update);
		update();
	});

	/* ---------- Просмотр фото галереи ---------- */
	var gallery = document.querySelector('[data-gallery]');
	var box = document.querySelector('[data-lightbox]');
	if (gallery && box && typeof box.showModal === 'function') {
		var links = Array.prototype.slice.call(gallery.querySelectorAll('[data-gallery-item]'));
		var img = box.querySelector('[data-lightbox-img]');
		var caption = box.querySelector('[data-lightbox-caption]');
		var index = 0;

		var show = function (i) {
			index = (i + links.length) % links.length;
			var link = links[index];
			img.src = link.getAttribute('href');
			img.alt = link.dataset.caption || '';
			caption.textContent = (link.dataset.caption || '') + ' · ' + (index + 1) + ' / ' + links.length;
		};

		links.forEach(function (link, i) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				show(i);
				box.showModal();
			});
		});
		box.querySelector('[data-lightbox-close]').addEventListener('click', function () { box.close(); });
		box.querySelector('[data-lightbox-prev]').addEventListener('click', function () { show(index - 1); });
		box.querySelector('[data-lightbox-next]').addEventListener('click', function () { show(index + 1); });
		box.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') { show(index - 1); }
			if (e.key === 'ArrowRight') { show(index + 1); }
		});
		box.addEventListener('click', function (e) {
			if (e.target === box) { box.close(); } // Клик по фону.
		});
		box.addEventListener('close', function () {
			var link = links[index];
			if (link) { link.focus(); }
		});
	}

	/* ---------- Форма заявки ---------- */
	document.querySelectorAll('[data-lead-form]').forEach(function (form) {
		var status = form.querySelector('[data-form-status]');
		var submit = form.querySelector('[data-submit]');

		var setStatus = function (text, ok) {
			status.hidden = !text;
			status.textContent = text || '';
			status.classList.toggle('is-success', !!ok);
			status.classList.toggle('is-error', !ok && !!text);
		};

		var clearErrors = function () {
			form.querySelectorAll('[data-error-for]').forEach(function (el) {
				el.hidden = true;
				el.textContent = '';
			});
			form.querySelectorAll('[aria-invalid]').forEach(function (el) { el.removeAttribute('aria-invalid'); });
		};

		var showErrors = function (errors) {
			var first = null;
			Object.keys(errors || {}).forEach(function (name) {
				var msg = form.querySelector('[data-error-for="' + name + '"]');
				var field = form.elements[name];
				if (msg) {
					msg.textContent = errors[name];
					msg.hidden = false;
				}
				if (field && field.setAttribute) {
					field.setAttribute('aria-invalid', 'true');
					first = first || field;
				}
			});
			if (first) { first.focus(); }
		};

		// Быстрая проверка в браузере; окончательная — на сервере.
		var localErrors = function () {
			var errors = {};
			var name = form.elements.name.value.trim();
			var digits = form.elements.phone.value.replace(/\D+/g, '');
			if (name.length < 2) { errors.name = 'Укажите имя: от 2 до 60 букв.'; }
			if (digits.length < 10 || digits.length > 15) { errors.phone = 'Проверьте номер телефона: нужно 10–15 цифр.'; }
			if (!form.elements.repair_type.value) { errors.repair_type = 'Выберите тип ремонта.'; }
			return errors;
		};

		form.addEventListener('submit', function (e) {
			if (!window.fetch || !window.FormData || !data.ajaxUrl) {
				return; // Обычная отправка формы.
			}
			e.preventDefault();
			clearErrors();

			var errors = localErrors();
			if (Object.keys(errors).length) {
				setStatus('Проверьте поля формы.', false);
				showErrors(errors);
				return;
			}

			submit.disabled = true;
			submit.setAttribute('aria-busy', 'true');
			var label = submit.textContent;
			submit.textContent = data.i18n.sending || '…';
			setStatus('', false);

			fetch(data.ajaxUrl, {
				method: 'POST',
				body: new FormData(form),
				credentials: 'same-origin',
				headers: { 'Accept': 'application/json' }
			})
				.then(function (res) {
					return res.json().catch(function () { return { success: false, data: {} }; });
				})
				.then(function (json) {
					var payload = json.data || {};
					if (json.success) {
						form.reset();
						var attached = form.querySelector('[data-calc-attached]');
						if (attached) { attached.hidden = true; }
						setStatus(payload.message, true);
						status.setAttribute('tabindex', '-1');
						status.focus();
					} else {
						setStatus(payload.message || data.i18n.error, false);
						showErrors(payload.errors);
					}
				})
				.catch(function () {
					setStatus(data.i18n.error, false);
				})
				.then(function () {
					submit.disabled = false;
					submit.removeAttribute('aria-busy');
					submit.textContent = label;
				});
		});
	});
})();
