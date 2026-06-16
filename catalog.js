/* БУЛЯК — динамічний каталог із Google Sheets (через Apps Script).
   Якщо API порожній або недоступний — лишається статичний каталог із index.html (fallback). */
(function () {
  const API = ''; // ← встав URL Apps Script (.../exec). Порожньо → статичний каталог.

  // Категорії фіксовані (фото + опис). Товари тягнемо з таблиці й групуємо сюди.
  const CATS = [
    { name: 'BBQ на вогні', desc: 'Реберця, стейки та крильця, копчені на живому вогні. Готові до столу.', img: 'assets/gen/set_feast.png' },
    { name: 'Копченості', desc: 'Грудинка, балик, ребра — власне коптіння на буковій трісці.', img: 'assets/gen/smoked.png' },
    { name: 'Ковбаси', desc: 'Домашні ковбаски, кабаноси й ковбаса по-галицьки за родинними рецептами.', img: 'assets/gen/sausages.png' },
    { name: 'Сири та молочне', desc: 'Копчений сулугуні, чечіл, фермерські сири та домашнє молочко.', img: 'assets/gen/cheese.png' },
    { name: 'Перекус на ходу', desc: 'Фірмовий хот-дог, бургер і гриль-меню — швидко й ситно.', img: 'assets/gen/hotdog.png' },
    { name: 'Шашлик маринований', desc: 'Свинина, курка та реберця у фірмовому маринаді.', img: 'assets/gen/shashlik.png' }
  ];

  if (!API) return;

  const cb = '__bcat' + Math.floor(performance.now());
  window[cb] = function (res) {
    if (res && res.ok && res.products && res.products.length) render(res.products);
  };
  const s = document.createElement('script');
  s.src = API + '?action=products&callback=' + cb;
  s.onerror = function () { /* лишаємо статичний каталог */ };
  document.body.appendChild(s);

  function esc(t) { return String(t == null ? '' : t).replace(/"/g, '&quot;').replace(/</g, '&lt;'); }

  function render(products) {
    const cont = document.querySelector('.catalog');
    if (!cont) return;
    const byCat = {};
    products.forEach(p => { (byCat[p.category] = byCat[p.category] || []).push(p); });

    const cats = CATS.filter(c => byCat[c.name]);
    Object.keys(byCat).forEach(name => { if (!CATS.find(c => c.name === name)) cats.push({ name, desc: '', img: 'assets/gen/hero_bbq.png' }); });

    cont.innerHTML = cats.map(c => {
      const items = byCat[c.name] || [];
      const min = Math.min.apply(null, items.map(i => i.price));
      const img = (items[0] && items[0].photo) ? items[0].photo : c.img;
      const li = items.map(p =>
        `<li><div class="li-i"><span class="li-n">${esc(p.name)}</span><span class="li-p">${esc(p.portion)}</span></div>` +
        `<button class="li-add" data-cart-add data-name="${esc(p.name)}" data-portion="${esc(p.portion)}" data-price="${p.price}">${p.price} ₴ +</button></li>`
      ).join('');
      return `<article class="prod reveal"><div class="prod-img real" style="background-image:url('${esc(img)}')"></div>` +
        `<div class="prod-body"><h3>${esc(c.name)}</h3><p>${esc(c.desc)}</p>` +
        `<div class="price">від ${min} грн</div>` +
        `<div class="prod-toggle">Обрати в кошик <span class="arr">▾</span></div>` +
        `<div class="prod-details"><ul>${li}</ul></div></div></article>`;
    }).join('');

    cont.querySelectorAll('.prod-toggle').forEach(t =>
      t.addEventListener('click', () => t.closest('.prod').classList.toggle('open')));
  }
})();
