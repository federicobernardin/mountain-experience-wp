(function () {
	'use strict';

	function getFieldTitle(field) {
		var heading = field.querySelector(':scope > h4, :scope > .sf-field-heading');
		var label = field.querySelector(':scope > label');

		if (heading && heading.textContent.trim()) {
			return heading.textContent.trim();
		}

		if (label && label.textContent.trim()) {
			return label.textContent.trim();
		}

		return '';
	}

	function moveChildrenToContent(field, content) {
		while (field.firstChild) {
			content.appendChild(field.firstChild);
		}
	}

	function initAccordion() {
		var forms = document.querySelectorAll('.searchandfilter');

		forms.forEach(function (form) {
			var fields = form.querySelectorAll(':scope > ul > li');
			var firstOpened = false;

			fields.forEach(function (field) {
				if (
					field.classList.contains('sf-field-submit') ||
					field.classList.contains('sf-field-reset')
				) {
					field.classList.add('me-filter-action');
					return;
				}

				if (field.classList.contains('me-filter-field')) {
					return;
				}

				var title = getFieldTitle(field);

				if (!title) {
					return;
				}

				field.classList.add('me-filter-field');

				var button = document.createElement('button');
				button.type = 'button';
				button.className = 'me-filter-toggle';
				button.textContent = title;
				button.setAttribute('aria-expanded', 'false');

				var content = document.createElement('div');
				content.className = 'me-filter-content';

				moveChildrenToContent(field, content);

				field.appendChild(button);
				field.appendChild(content);

				if (!firstOpened) {
					field.classList.add('is-open');
					button.setAttribute('aria-expanded', 'true');
					firstOpened = true;
				}

				button.addEventListener('click', function () {
					var isOpen = field.classList.toggle('is-open');
					button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
				});
			});
		});
	}

	document.addEventListener('DOMContentLoaded', initAccordion);

	document.addEventListener('sf:ajaxfinish', function () {
		initAccordion();
	});
})();