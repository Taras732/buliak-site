/* БУЛЯК — плавна поява секцій і карток при прокрутці */
(function () {
  if (!('IntersectionObserver' in window)) return;
  function run() {
    var sel = '.home .sec, .home .why-item, .woocommerce ul.products li.product, .buliak-footer .fcols > div';
    var els = document.querySelectorAll(sel);
    if (!els.length) return;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    els.forEach(function (el, i) {
      el.classList.add('reveal-init');
      el.style.transitionDelay = (Math.min(i % 6, 5) * 60) + 'ms';
      io.observe(el);
    });
  }
  if (document.readyState !== 'loading') run();
  else document.addEventListener('DOMContentLoaded', run);
})();
