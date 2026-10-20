/* Выбор фотографий галереи проекта через медиатеку WordPress. */
(function ($) {
	'use strict';

	$(document).on('click', '.rmt-gallery-pick', function (e) {
		e.preventDefault();
		var target = $(this).data('target');
		var $input = $('#' + target);
		var $preview = $('.rmt-gallery-preview[data-target="' + target + '"]');

		var frame = wp.media({
			title: 'Фотографии проекта',
			button: { text: 'Использовать' },
			library: { type: 'image' },
			multiple: 'add'
		});

		frame.on('open', function () {
			var selection = frame.state().get('selection');
			($input.val() || '').split(',').filter(Boolean).forEach(function (id) {
				var att = wp.media.attachment(id);
				att.fetch();
				selection.add(att);
			});
		});

		frame.on('select', function () {
			var ids = [];
			$preview.empty();
			frame.state().get('selection').each(function (att) {
				ids.push(att.id);
				var sizes = att.get('sizes') || {};
				var url = (sizes.thumbnail || sizes.full || {}).url || att.get('url');
				$preview.append($('<img>', { src: url, alt: '' }));
			});
			$input.val(ids.join(','));
		});

		frame.open();
	});

	$(document).on('click', '.rmt-gallery-clear', function (e) {
		e.preventDefault();
		var target = $(this).data('target');
		$('#' + target).val('');
		$('.rmt-gallery-preview[data-target="' + target + '"]').empty();
	});
})(jQuery);
