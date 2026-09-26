function(options) {
    // Inline head script for local_nevertranslate; see classes/local/techniques.php::guard_script().
    // Deliberately plain ES5 with no dependencies: it is emitted verbatim into the head so that it
    // runs before the page content (and any translator) exists.
    var html = document.documentElement;
    var originallang = html.getAttribute('lang');
    var checkpending = false;
    var gaveup = false;

    // Page content can shadow document properties with named elements (e.g. <img name="body">),
    // so take the real accessors from the prototype before any content exists.
    var bodygetter = Object.getOwnPropertyDescriptor(Document.prototype, 'body').get;
    var queryselector = Document.prototype.querySelector;
    var getbody = function() {
        return bodygetter.call(document);
    };

    var hasclass = function(element, name) {
        return (' ' + (element.getAttribute('class') || '') + ' ').indexOf(' ' + name + ' ') !== -1;
    };

    var enforce = function() {
        if (options.htmltranslate && html.getAttribute('translate') !== 'no') {
            html.setAttribute('translate', 'no');
        }
        if (options.htmlclass && !hasclass(html, 'notranslate')) {
            html.classList.add('notranslate');
        }
        var body = getbody();
        if (body) {
            if (options.bodytranslate && body.getAttribute('translate') !== 'no') {
                body.setAttribute('translate', 'no');
            }
            if (options.bodyclass && !hasclass(body, 'notranslate')) {
                body.classList.add('notranslate');
            }
        }
    };

    // Any translate value other than "no" (including the empty string) means "yes" and re-enables
    // translation for that subtree, e.g. in HTML pasted into the editor.
    var neutralise = function(element) {
        if (element.nodeType !== 1) {
            return;
        }
        if (element.hasAttribute('translate') && element.getAttribute('translate').toLowerCase() !== 'no') {
            element.setAttribute('translate', 'no');
        }
        var overrides = element.querySelectorAll('[translate]');
        for (var i = 0; i < overrides.length; i++) {
            if (overrides[i].getAttribute('translate').toLowerCase() !== 'no') {
                overrides[i].setAttribute('translate', 'no');
            }
        }
    };

    var istranslated = function() {
        if (hasclass(html, 'translated-ltr') || hasclass(html, 'translated-rtl')) {
            // Google Translate (Chrome built-in translation and the website widget).
            return true;
        }
        if (originallang !== null && html.getAttribute('lang') !== originallang) {
            // Firefox and Google both rewrite the page language to the target language.
            return true;
        }
        // Microsoft Edge's built-in translator.
        return queryselector.call(document, '[_msttexthash], [_mstmutation]') !== null;
    };

    var revert = function() {
        // At most one attempt per page view.
        gaveup = true;
        // Reloading a form submission would ask the user to resubmit it.
        if (options.post) {
            return;
        }
        var expired = '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
        // The Google Translate website widget re-applies its translation from this cookie.
        document.cookie = 'googtrans' + expired;
        document.cookie = 'googtrans' + expired + '; domain=' + location.hostname;
        document.cookie = 'googtrans' + expired + '; domain=.' + location.hostname;
        var key = options.reloadkey + ':' + location.pathname + location.search;
        try {
            var last = parseInt(window.sessionStorage.getItem(key) || '0', 10);
            if (new Date().getTime() - last < 30000) {
                // The translation came straight back after our reload (e.g. the browser is set to
                // always translate): give up for this page rather than reload in a loop.
                window.sessionStorage.removeItem(key);
                return;
            }
            window.sessionStorage.setItem(key, String(new Date().getTime()));
        } catch (e) {
            // Without storage there is no way to prevent a reload loop, so do not reload.
            return;
        }
        window.location.reload();
    };

    var check = function() {
        checkpending = false;
        if (options.guard) {
            enforce();
        }
        if (options.revert && !gaveup && istranslated()) {
            revert();
        }
    };

    var schedulecheck = function() {
        if (!checkpending) {
            checkpending = true;
            window.setTimeout(check, 100);
        }
    };

    if (typeof window.MutationObserver !== 'function') {
        return;
    }

    new window.MutationObserver(function(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var mutation = mutations[i];
            if (mutation.type === 'childList') {
                if (options.guard) {
                    for (var j = 0; j < mutation.addedNodes.length; j++) {
                        neutralise(mutation.addedNodes[j]);
                    }
                }
            } else if (mutation.attributeName === 'translate' && options.guard) {
                neutralise(mutation.target);
            }
        }
        schedulecheck();
    }).observe(html, {
        attributes: true,
        attributeFilter: ['class', 'translate', 'lang', '_msttexthash', '_mstmutation'],
        childList: true,
        subtree: true,
    });

    if (options.guard) {
        enforce();
    }
    document.addEventListener('DOMContentLoaded', check);
}
