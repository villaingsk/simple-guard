(function () {
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof turnstile === 'undefined') return;
        var widgets = document.querySelectorAll('.cf-turnstile');
        widgets.forEach(function (w) {
            var sitekey = window.SG_TURNSTILE ? window.SG_TURNSTILE.sitekey : w.getAttribute('data-sitekey');
            if (!sitekey) return;
            // render visible widget
            try {
                turnstile.render(w, { sitekey: sitekey });
            } catch (e) {
                console.error('Turnstile render error', e);
            }
        });
    });
})();