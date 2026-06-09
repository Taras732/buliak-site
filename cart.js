/* БУЛЯК — кошик MVP (Сценарій А: порції + фіксована ціна) */
(function () {
  const KEY = 'buliak_cart';
  // Встав сюди URL веб-додатку Apps Script (.../exec) з order-webhook.gs.
  // Порожньо → fallback: клієнт копіює замовлення й шле в Telegram вручну.
  const ORDER_ENDPOINT = '';
  const TG = 'https://t.me/BULIAK_DELIVERY';

  let cart = load();

  function load() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } }
  function persist() { localStorage.setItem(KEY, JSON.stringify(cart)); }
  function count() { return cart.reduce((s, i) => s + i.qty, 0); }
  function total() { return cart.reduce((s, i) => s + i.price * i.qty, 0); }
  function money(n) { return n.toLocaleString('uk-UA') + ' ₴'; }

  function add(name, portion, price) {
    const ex = cart.find(i => i.name === name && i.price === price);
    if (ex) ex.qty++; else cart.push({ name, portion: portion || '', price: +price, qty: 1 });
    persist(); sync(); openCart(); toast(name + ' — у кошику');
  }
  function setQty(idx, d) {
    if (!cart[idx]) return;
    cart[idx].qty += d;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    persist(); sync();
  }
  function clear() { cart = []; persist(); sync(); }

  /* ---------- UI build ---------- */
  const el = document.createElement('div');
  el.innerHTML = `
  <button id="cartBtn" class="cart-btn" aria-label="Кошик">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg>
    <span id="cartCount" class="cart-count" hidden>0</span>
  </button>
  <div id="cartOverlay" class="cart-overlay"></div>
  <aside id="cartDrawer" class="cart-drawer" aria-label="Кошик">
    <div class="cart-head"><h3>Твій кошик</h3><button id="cartClose" class="cart-x" aria-label="Закрити">✕</button></div>
    <div id="cartItems" class="cart-items"></div>
    <div class="cart-foot">
      <div class="cart-note">Сума орієнтовна — фінал залежить від фактичної ваги при зважуванні. Менеджер підтвердить замовлення.</div>
      <div class="cart-total"><span>Орієнтовно</span><b id="cartTotal">0 ₴</b></div>
      <button id="cartCheckout" class="btn btn-primary cart-checkout">Оформити замовлення</button>
    </div>
  </aside>
  <div id="coOverlay" class="cart-overlay"></div>
  <div id="coModal" class="co-modal" role="dialog" aria-label="Оформлення">
    <div class="co-inner">
      <div class="cart-head"><h3 id="coTitle">Оформлення</h3><button id="coClose" class="cart-x" aria-label="Закрити">✕</button></div>
      <div id="coBody" class="co-body"></div>
    </div>
  </div>
  <div id="toast" class="toast"></div>`;
  document.body.appendChild(el);

  // вставити кнопку кошика в nav поряд із CTA
  const nav = document.querySelector('nav');
  const cartBtn = document.getElementById('cartBtn');
  if (nav) nav.appendChild(cartBtn);

  const $ = id => document.getElementById(id);
  const overlay = $('cartOverlay'), drawer = $('cartDrawer');
  const coOverlay = $('coOverlay'), coModal = $('coModal');

  function openCart() { overlay.classList.add('show'); drawer.classList.add('show'); }
  function closeCart() { overlay.classList.remove('show'); drawer.classList.remove('show'); }
  function openCo() { closeCart(); coOverlay.classList.add('show'); coModal.classList.add('show'); }
  function closeCo() { coOverlay.classList.remove('show'); coModal.classList.remove('show'); }

  cartBtn.onclick = openCart;
  $('cartClose').onclick = closeCart;
  overlay.onclick = closeCart;
  $('coClose').onclick = closeCo;
  coOverlay.onclick = closeCo;
  $('cartCheckout').onclick = () => { if (!cart.length) { toast('Кошик порожній'); return; } renderCheckout(); openCo(); };

  /* ---------- render ---------- */
  function sync() {
    const c = count();
    const badge = $('cartCount');
    badge.textContent = c; badge.hidden = c === 0;
    $('cartTotal').textContent = money(total());
    const box = $('cartItems');
    if (!cart.length) { box.innerHTML = '<div class="cart-empty">Кошик порожній 🛒<br><small>Обери щось смачне з меню</small></div>'; return; }
    box.innerHTML = cart.map((i, idx) => `
      <div class="ci-row">
        <div class="ci-info"><div class="ci-name">${i.name}</div><div class="ci-portion">${i.portion ? i.portion + ' · ' : ''}${money(i.price)}</div></div>
        <div class="ci-qty"><button data-q="${idx}" data-d="-1">−</button><span>${i.qty}</span><button data-q="${idx}" data-d="1">+</button></div>
        <div class="ci-sum">${money(i.price * i.qty)}</div>
      </div>`).join('');
    box.querySelectorAll('[data-q]').forEach(b => b.onclick = () => setQty(+b.dataset.q, +b.dataset.d));
  }

  function renderCheckout() {
    $('coBody').innerHTML = `
      <div class="co-sum">
        ${cart.map(i => `<div class="co-line"><span>${i.name} ×${i.qty}</span><b>${money(i.price * i.qty)}</b></div>`).join('')}
        <div class="co-line co-grand"><span>Орієнтовно</span><b>${money(total())}</b></div>
      </div>
      <form id="coForm" class="co-form">
        <label>Ім'я <input name="name" required placeholder="Як до вас звертатись"></label>
        <label>Телефон <input name="phone" required type="tel" placeholder="+380..."></label>
        <label>Доставка
          <select name="delivery">
            <option>Нова Пошта (відділення)</option>
            <option>Самовивіз (Зимна Вода)</option>
            <option>Доставка по місту</option>
          </select>
        </label>
        <label>Адреса / відділення <input name="address" placeholder="Місто, № відділення НП"></label>
        <label>Оплата
          <select name="pay">
            <option>Накладений платіж (при отриманні)</option>
            <option>Переказ на картку</option>
          </select>
        </label>
        <label>Коментар <textarea name="note" rows="2" placeholder="Побажання до замовлення"></textarea></label>
        <label class="co-check"><input type="checkbox" name="agree" required> Погоджуюсь, що вага товару орієнтовна (відхилення входить у ціну), а менеджер підтвердить фінальну суму.</label>
        <button type="submit" class="btn btn-primary co-submit">Підтвердити замовлення</button>
      </form>`;
    $('coForm').onsubmit = submitOrder;
  }

  function orderText(f) {
    const lines = cart.map(i => `• ${i.name}${i.portion ? ' (' + i.portion + ')' : ''} ×${i.qty} — ${money(i.price * i.qty)}`).join('\n');
    return `🥩 НОВЕ ЗАМОВЛЕННЯ — БУЛЯК\n\n${lines}\n\nОрієнтовна сума: ${money(total())}\n(фінал — по факту зважування)\n\n👤 ${f.name.value}\n📞 ${f.phone.value}\n🚚 ${f.delivery.value}${f.address.value ? ' — ' + f.address.value : ''}\n💳 ${f.pay.value}${f.note.value ? '\n📝 ' + f.note.value : ''}`;
  }

  async function submitOrder(e) {
    e.preventDefault();
    const f = e.target;
    const text = orderText(f);
    const payload = { items: cart, total: total(), name: f.name.value, phone: f.phone.value, delivery: f.delivery.value, address: f.address.value, pay: f.pay.value, note: f.note.value, text };
    if (ORDER_ENDPOINT) {
      try {
        // no-cors + без custom headers — щоб уникнути CORS preflight з Google Apps Script
        await fetch(ORDER_ENDPOINT, { method: 'POST', mode: 'no-cors', body: JSON.stringify(payload) });
        success(text, true);
      } catch (err) { success(text, false); }
    } else {
      success(text, false);
    }
    clear();
  }

  function success(text, sent) {
    $('coTitle').textContent = 'Замовлення сформовано ✓';
    $('coBody').innerHTML = `
      <div class="co-ok">
        <div class="co-ok-ic">🔥</div>
        <p>${sent ? 'Замовлення відправлено! Менеджер зв\'яжеться з тобою для підтвердження.' : 'Майже готово! Надішли замовлення нам у Telegram — менеджер підтвердить склад і суму.'}</p>
        <textarea id="coText" class="co-text" readonly rows="9">${text}</textarea>
        <div class="co-ok-btns">
          <button id="coCopy" class="btn btn-ghost">Скопіювати</button>
          <a class="btn btn-primary" href="${TG}" target="_blank" rel="noopener">Відкрити Telegram →</a>
        </div>
      </div>`;
    $('coCopy').onclick = () => { const t = $('coText'); t.select(); document.execCommand('copy'); toast('Скопійовано'); };
  }

  /* ---------- toast ---------- */
  let toastT;
  function toast(msg) {
    const t = $('toast'); t.textContent = msg; t.classList.add('show');
    clearTimeout(toastT); toastT = setTimeout(() => t.classList.remove('show'), 2200);
  }

  /* ---------- bind add buttons (делегування) ---------- */
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-cart-add]');
    if (!b) return;
    e.preventDefault();
    add(b.dataset.name, b.dataset.portion, b.dataset.price);
  });

  sync();
})();
