
/**
 * Document management for Contao Open Source CMS
 *
 * Copyright (C) 2014-2021 Rhyme
 *
 * @package    Document_Management
 * @link       http://rhyme.digital
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Class DocMan
 *
 * Provide methods to handle Ajax requests.
 * @copyright  Rhyme 2021


 */

var DocMan =
{

    /**
	 * Feature/unfeature an element
	 *
	 * @param {object} el The DOM element
	 * @param {string} id The ID of the target element
	 *
	 * @returns {boolean}
	 */
	toggleFeatured: function(el, id) {
		el.blur();

		var image = $(el).getFirst('img'),
			featured = (image.src.indexOf('featured_') == -1);

		// Send the request
		if (!featured) {
			image.src = image.src.replace('featured_.gif', 'featured.gif');
			new Request.Contao().post({'action':'toggleFeaturedDoc', 'id':id, 'state':1, 'REQUEST_TOKEN':Contao.request_token});
		} else {
			image.src = image.src.replace('featured.gif', 'featured_.gif');
			new Request.Contao().post({'action':'toggleFeaturedDoc', 'id':id, 'state':0, 'REQUEST_TOKEN':Contao.request_token});
		}

		return false;
	},


	/**
	 * Document wizard
	 *
	 * @param {object} el      The DOM element
	 * @param {string} command The command name
	 * @param {string} id      The ID of the target element
	 */
	documentWizard: function (el, command, id) {
		var table = $(id),
			tbody = table.getElement('tbody'),
			parent = $(el).getParent('tr'),
			rows = tbody.getChildren(),
			tabindex = tbody.get('data-tabindex'),
			input, select, childs, a, i, j;

		Backend.getScrollOffset();

		switch (command) {
			case 'copy':
				var tr = new Element('tr');
				childs = parent.getChildren();
				for (i = 0; i < childs.length; i++) {
					var next = childs[i].clone(true).inject(tr, 'bottom');
					if (select = childs[i].getFirst('select')) {
						next.getFirst('select').value = select.value;
					}
				}
				tr.inject(parent, 'after');
				tr.getElement('.chzn-container').destroy();
				new Chosen(tr.getElement('select.tl_select'));
				window.Stylect ? Stylect.convertSelects() : null;
				break;
			case 'up':
				if (tr === parent.getPrevious('tr')) {
					parent.inject(tr, 'before');
				} else {
					parent.inject(tbody, 'bottom');
				}
				break;
			case 'down':
				if (tr === parent.getNext('tr')) {
					parent.inject(tr, 'after');
				} else {
					parent.inject(tbody, 'top');
				}
				break;
			case 'delete':
				if (rows.length > 1) {
					parent.destroy();
				}
				break;
		}

		rows = tbody.getChildren();

		for (i = 0; i < rows.length; i++) {
			childs = rows[i].getChildren();
			for (j = 0; j < childs.length; j++) {
				if (a = childs[j].getFirst('a.chzn-single')) {
					a.set('tabindex', tabindex++);
				}
				if (select = childs[j].getFirst('select')) {
					select.name = select.name.replace(/\[[0-9]+]/g, '[' + i + ']');
				}
				if (input = childs[j].getFirst('input[type="checkbox"]')) {
					input.set('tabindex', tabindex++);
					input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']');
				}
				if (input = childs[j].getFirst('input[type="text"]')) {
					input.set('tabindex', tabindex++);
					input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']');
				}
			}
		}

		new Sortables(tbody, {
			constrain: true,
			opacity: 0.6,
			handle: '.drag-handle'
		});
	}
	
};