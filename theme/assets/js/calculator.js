/**
 * Калькулятор предварительной стоимости ремонта.
 * Формула: площадь × ставка тарифа = фиксированная стоимость.
 * Тарифы, ставки и состав работ приходят из настроек темы (JSON внутри блока калькулятора).
 */
(function () {
	'use strict';

	var fmt = new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 0 });
	var money = function (v) { return fmt.format(v) + ' ₽'; };
	var find = function (list, key) {
		for (var i = 0; i < list.length; i++) {
			if (list[i].key === key) { return list[i]; }
		}
		return list[0];
	};
	var checkIcon = '<svg class="icon icon--check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	document.querySelectorAll('[data-calc]').forEach(function (root) {
		var settingsEl = root.querySelector('[data-calc-settings]');
		if (!settingsEl) { return; }
		var s = JSON.parse(settingsEl.textContent);
		var form = root.querySelector('.calc__form');
		var area = root.querySelector('[data-calc-area]');
		var range = root.querySelector('[data-calc-range]');
		var includes = root.querySelector('[data-calc-includes]');
		var out = {
			total: root.querySelector('[data-calc-total]'),
			tariff: root.querySelector('[data-calc-tariff]'),
			area: root.querySelector('[data-calc-area-out]'),
			rate: root.querySelector('[data-calc-rate]'),
			includesTariff: root.querySelector('[data-calc-includes-tariff]')
		};
		var last = null;
		var shownType = null;

		var clampArea = function (v) {
			v = parseFloat(String(v).replace(',', '.'));
			if (!isFinite(v)) { return s.minArea; }
			return Math.min(s.maxArea, Math.max(s.minArea, Math.round(v)));
		};

		var renderIncludes = function (type) {
			if (shownType === type.key) { return; }
			shownType = type.key;
			includes.innerHTML = '';
			type.includes.forEach(function (line) {
				var li = document.createElement('li');
				var span = document.createElement('span');
				span.textContent = line; // Текст из настроек — только как текст, без HTML.
				li.innerHTML = checkIcon;
				li.appendChild(span);
				includes.appendChild(li);
			});
			out.includesTariff.textContent = type.label + (type.subtitle ? ' · ' + type.subtitle : '');
		};

		var calc = function () {
			var input = form.querySelector('input[name="calc_type"]:checked');
			var type = find(s.types, input ? input.value : '');
			var a = clampArea(area.value);
			var total = Math.round(a * type.rate);

			out.total.textContent = money(total);
			out.tariff.textContent = type.label;
			out.area.textContent = a + ' м²';
			out.rate.textContent = money(type.rate) + ' / м²';
			renderIncludes(type);

			last = { type: type, area: a, total: total };
		};

		// Ползунок и поле площади синхронизированы.
		range.addEventListener('input', function () {
			area.value = range.value;
			calc();
		});
		area.addEventListener('input', function () {
			if (area.value !== '') { range.value = clampArea(area.value); }
			calc();
		});
		area.addEventListener('change', function () {
			area.value = clampArea(area.value);
			range.value = area.value;
			calc();
		});
		form.addEventListener('change', calc);
		calc();

		// «Передать в заявку»: заполняем форму и прокручиваем к ней.
		root.querySelector('[data-calc-send]').addEventListener('click', function (e) {
			var lead = document.querySelector('[data-lead-form]');
			if (!lead || !last) { return; }
			e.preventDefault();
			var summary = [
				'Тариф «' + last.type.label + '»',
				last.area + ' м²',
				'стоимость ' + money(last.total) + (s.demo ? ' (демо-ставки)' : '')
			].join('; ');

			var typeSelect = lead.querySelector('[data-lead-type]');
			if (typeSelect && typeSelect.querySelector('option[value="' + last.type.key + '"]')) {
				typeSelect.value = last.type.key;
			}
			var areaInput = lead.querySelector('[data-lead-area]');
			if (areaInput) { areaInput.value = last.area; }
			var hidden = lead.querySelector('[data-calc-summary]');
			if (hidden) { hidden.value = summary; }
			var attached = lead.querySelector('[data-calc-attached]');
			if (attached) {
				attached.textContent = 'К заявке приложен расчёт: ' + summary;
				attached.hidden = false;
			}

			lead.scrollIntoView({ behavior: 'smooth', block: 'start' });
			var name = lead.querySelector('input[name="name"]');
			if (name) {
				setTimeout(function () { name.focus({ preventScroll: true }); }, 450);
			}
		});
	});
})();
