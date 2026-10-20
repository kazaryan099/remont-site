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
			syncToLead();
		};

		/* ---------- Связь с формой заявки (в обе стороны) ---------- */
		var lead = document.querySelector('[data-lead-form]');
		var leadType = lead && lead.querySelector('[data-lead-type]');
		var leadArea = lead && lead.querySelector('[data-lead-area]');
		var leadSummary = lead && lead.querySelector('[data-calc-summary]');
		var leadAttached = lead && lead.querySelector('[data-calc-attached]');

		var summaryText = function () {
			return [
				'Тариф «' + last.type.label + '»',
				last.area + ' м²',
				'стоимость ' + money(last.total) + (s.demo ? ' (демо-ставки)' : '')
			].join('; ');
		};

		// Расчёт прикладывается к заявке, только если в форме выбран тариф калькулятора.
		var updateAttached = function () {
			if (!lead) { return; }
			var linked = leadType && find(s.types, leadType.value).key === leadType.value;
			leadSummary.value = linked ? summaryText() : '';
			leadAttached.textContent = linked ? 'К заявке приложен расчёт: ' + summaryText() : '';
			leadAttached.hidden = !linked;
		};

		// Калькулятор → форма: тариф и площадь подставляются сразу при любом изменении.
		function syncToLead() {
			if (!lead || !last) { return; }
			if (leadType && leadType.querySelector('option[value="' + last.type.key + '"]')) {
				leadType.value = last.type.key;
			}
			if (leadArea && document.activeElement !== leadArea) {
				leadArea.value = last.area;
			}
			updateAttached();
		}

		// Форма → калькулятор: выбор тарифа или площади в форме пересчитывает калькулятор.
		if (leadType) {
			leadType.addEventListener('change', function () {
				var radio = form.querySelector('input[name="calc_type"][value="' + leadType.value + '"]');
				if (radio) {
					radio.checked = true;
					calc();
				} else {
					updateAttached(); // «Пока не знаю» — расчёт не прикладываем.
				}
			});
		}
		if (leadArea) {
			leadArea.setAttribute('max', s.maxArea);
			var fromLeadArea = function (final) {
				var v = parseFloat(String(leadArea.value).replace(',', '.'));
				if (!isFinite(v)) { return; }
				area.value = range.value = clampArea(v);
				if (final) { leadArea.value = area.value; }
				calc();
			};
			leadArea.addEventListener('input', function () { fromLeadArea(false); });
			leadArea.addEventListener('change', function () { fromLeadArea(true); });
		}
		if (lead) {
			// После успешной отправки форма очищается — снова подставляем текущий расчёт.
			lead.addEventListener('reset', function () { setTimeout(syncToLead, 0); });
		}

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

		// «Передать в заявку»: форма уже заполнена — подставляем ещё раз и прокручиваем к ней.
		root.querySelector('[data-calc-send]').addEventListener('click', function (e) {
			if (!lead || !last) { return; }
			e.preventDefault();
			if (leadArea) { leadArea.value = last.area; }
			syncToLead();

			lead.scrollIntoView({ behavior: 'smooth', block: 'start' });
			var name = lead.querySelector('input[name="name"]');
			if (name) {
				setTimeout(function () { name.focus({ preventScroll: true }); }, 450);
			}
		});
	});
})();
