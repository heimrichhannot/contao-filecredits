var FileCredits = {
    /**
     * Toggle filecredit pages
     *
     * @param {object} el    The DOM element
     * @param {string} id    The ID of the target element
     * @param {string} field The field name
     */
    toggleFileCreditPages: function(el, id, field) {
        el.blur();
        var item = $(id);

        if (item) {
            if (!el.value) {
                el.value = 1;
                el.checked = 'checked';
                item.setStyle('display', 'block');
                new Request.Contao({field: el}).get({
                    'action': 'toggleFileCreditPages',
                    'id': id,
                    'field': field,
                    'state': 1,
                    'value': el.value,
                    'REQUEST_TOKEN': Contao.request_token,
                });
            } else {
                el.value = '';
                el.checked = '';
                item.setStyle('display', 'none');
                new Request.Contao({field: el}).get({
                    'action': 'toggleFileCreditPages',
                    'id': id,
                    'field': field,
                    'value': el.value,
                    'state': 0,
                    'REQUEST_TOKEN': Contao.request_token,
                });
            }
            return;
        }

        new Request.Contao({
            field: el,
            evalScripts: false,
            onRequest: AjaxRequest.displayBox(Contao.lang.loading + ' …'),
            onSuccess: function(txt, json) {
                var div = new Element('div', {
                    'id': id,
                    'html': txt,
                    'styles': {
                        'display': 'block',
                    },
                }).inject($(el).getParent('div', 'after'));

                // Execute scripts after the DOM has been updated
                if (json.javascript) {

                    // Use Asset.javascript() instead of document.write() to load a
                    // JavaScript file and re-execude the code after it has been loaded
                    document.write = function(str) {
                        var src = '';
                        str.replace(/<script src="([^"]+)"/i, function(all, match) {
                            src = match;
                        });
                        src && Asset.javascript(src, {
                            onLoad: function() {
                                Browser.exec(json.javascript);
                            },
                        });
                    };

                    Browser.exec(json.javascript);
                }

                el.value = 1;
                el.checked = 'checked';

                // Update the referer ID
                div.getElements('a').each(function(el) {
                    el.href = el.href.replace(/&ref=[a-f0-9]+/, '&ref=' + Contao.referer_id);
                });

                AjaxRequest.hideBox();

                // HOOK
                window.fireEvent('subpalette'); // Backwards compatibility
                window.fireEvent('ajax_change');
            },
        }).get({
            'action': 'toggleFileCreditPages',
            'id': id,
            'field': field,
            'load': 1,
            'value': el.value,
            'state': 1,
            'REQUEST_TOKEN': Contao.request_token,
        });
    },
};