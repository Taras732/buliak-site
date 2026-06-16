/* БУЛЯК — зручний степер кількості (± кнопки) */
(function () {
  function build(q) {
    if (q.querySelector('.qty-btn')) return;
    var input = q.querySelector('input.qty');
    if (!input) return;
    var minus = document.createElement('button');
    minus.type = 'button'; minus.className = 'qty-btn qty-minus'; minus.textContent = '−';
    var plus = document.createElement('button');
    plus.type = 'button'; plus.className = 'qty-btn qty-plus'; plus.textContent = '+';
    q.insertBefore(minus, input);
    q.appendChild(plus);
    var step = parseFloat(input.getAttribute('step')) || 1;
    var min = parseFloat(input.getAttribute('min')) || 1;
    minus.addEventListener('click', function () {
      var v = parseFloat(input.value) || min;
      if (v - step >= min) { input.value = v - step; fire(input); }
    });
    plus.addEventListener('click', function () {
      var v = parseFloat(input.value) || min;
      var max = parseFloat(input.getAttribute('max'));
      if (!max || v + step <= max) { input.value = v + step; fire(input); }
    });
  }
  function fire(input) {
    input.dispatchEvent(new Event('change', { bubbles: true }));
    input.dispatchEvent(new Event('input', { bubbles: true }));
  }
  function init() { document.querySelectorAll('.quantity').forEach(build); }
  if (document.readyState !== 'loading') init(); else document.addEventListener('DOMContentLoaded', init);
  // після AJAX-оновлень кошика
  document.body.addEventListener('updated_cart_totals', init);
  document.body.addEventListener('updated_wc_div', init);
})();
