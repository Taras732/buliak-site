/* ============================================================
   БУЛЯК — API (Google Sheets як база даних)
   Обслуговує: замовлення з сайту, каталог товарів, адмінку.
   ------------------------------------------------------------
   НАЛАШТУВАННЯ (один раз, ~10 хв):
   1. sheets.google.com → новий аркуш "БУЛЯК".
   2. Розширення → Apps Script → встав цей код, збережи.
   3. Заміни ADMIN_PASS на свій пароль адмінки.
   4. (Опційно Telegram) BOT_TOKEN від @BotFather + CHAT_ID
      (напиши боту, відкрий api.telegram.org/bot<TOKEN>/getUpdates).
   5. Розгорнути → Новий розгортання → "Веб-додаток":
      виконувати "від мене", доступ "Усі" → скопіюй URL (.../exec).
   6. Встав цей URL у трьох файлах:
        cart.js     → ORDER_ENDPOINT
        catalog.js  → API
        admin.html  → API (вгорі скрипта)
   Аркуші "Products" і "Orders" створяться самі при першому зверненні.
   ============================================================ */

const ADMIN_PASS = 'zmini_mene_123';   // ← ПАРОЛЬ адмінки
const BOT_TOKEN  = 'PUT_BOT_TOKEN';    // ← опційно
const CHAT_ID    = 'PUT_CHAT_ID';      // ← опційно

const P_HEAD = ['id', 'category', 'name', 'portion', 'price', 'photo', 'active'];
const O_HEAD = ['date', 'name', 'phone', 'delivery', 'address', 'pay', 'total', 'note', 'items', 'status'];

function doGet(e) {
  const p = e.parameter, cb = p.callback;
  let out;
  try {
    if (p.action === 'products') out = { ok: true, products: getProducts() };
    else if (p.action === 'orders') out = (p.key === ADMIN_PASS) ? { ok: true, orders: getOrders() } : { ok: false, error: 'auth' };
    else if (p.action === 'auth') out = { ok: p.key === ADMIN_PASS };
    else out = { ok: false, error: 'unknown action' };
  } catch (err) { out = { ok: false, error: String(err) }; }
  return reply(out, cb);
}

function doPost(e) {
  let out;
  try {
    const d = JSON.parse(e.postData.contents);
    if (d.action === 'order') {
      saveOrder(d);
      if (BOT_TOKEN !== 'PUT_BOT_TOKEN') sendTelegram(d.text || JSON.stringify(d));
      out = { ok: true };
    } else if (d.key !== ADMIN_PASS) {
      out = { ok: false, error: 'auth' };
    } else if (d.action === 'saveProduct') { saveProduct(d.product); out = { ok: true }; }
    else if (d.action === 'deleteProduct') { deleteProduct(d.id); out = { ok: true }; }
    else if (d.action === 'setStatus') { setStatus(d.row, d.status); out = { ok: true }; }
    else out = { ok: false, error: 'unknown action' };
  } catch (err) { out = { ok: false, error: String(err) }; }
  return reply(out, null);
}

function reply(obj, cb) {
  const s = JSON.stringify(obj);
  if (cb) return ContentService.createTextOutput(cb + '(' + s + ')').setMimeType(ContentService.MimeType.JAVASCRIPT);
  return ContentService.createTextOutput(s).setMimeType(ContentService.MimeType.JSON);
}

function sheet(name, head) {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let sh = ss.getSheetByName(name);
  if (!sh) { sh = ss.insertSheet(name); sh.appendRow(head); }
  return sh;
}

/* ---------- Товари ---------- */
function getProducts() {
  const v = sheet('Products', P_HEAD).getDataRange().getValues(); v.shift();
  return v.filter(r => r[0] !== '' && String(r[6]).toLowerCase() !== 'ні' && r[6] !== false)
    .map(r => ({ id: String(r[0]), category: r[1], name: r[2], portion: r[3], price: Number(r[4]) || 0, photo: r[5] }));
}
function saveProduct(p) {
  const sh = sheet('Products', P_HEAD), v = sh.getDataRange().getValues();
  for (let i = 1; i < v.length; i++) {
    if (String(v[i][0]) === String(p.id) && p.id) {
      sh.getRange(i + 1, 1, 1, 7).setValues([[p.id, p.category, p.name, p.portion, p.price, p.photo, p.active || 'так']]);
      return;
    }
  }
  sh.appendRow([p.id || String(Date.now()), p.category, p.name, p.portion, p.price, p.photo, p.active || 'так']);
}
function deleteProduct(id) {
  const sh = sheet('Products', P_HEAD), v = sh.getDataRange().getValues();
  for (let i = v.length - 1; i >= 1; i--) if (String(v[i][0]) === String(id)) sh.deleteRow(i + 1);
}

/* ---------- Замовлення ---------- */
function getOrders() {
  const v = sheet('Orders', O_HEAD).getDataRange().getValues(); v.shift();
  return v.map((r, i) => ({ row: i + 2, date: r[0], name: r[1], phone: r[2], delivery: r[3], address: r[4], pay: r[5], total: r[6], note: r[7], items: r[8], status: r[9] || 'Нове' })).reverse();
}
function saveOrder(d) {
  sheet('Orders', O_HEAD).appendRow([new Date(), d.name, d.phone, d.delivery, d.address, d.pay, d.total, d.note,
    (d.items || []).map(i => i.name + ' x' + i.qty).join('; '), 'Нове']);
}
function setStatus(row, status) { sheet('Orders', O_HEAD).getRange(row, 10).setValue(status); }

function sendTelegram(text) {
  UrlFetchApp.fetch('https://api.telegram.org/bot' + BOT_TOKEN + '/sendMessage', {
    method: 'post', contentType: 'application/json', muteHttpExceptions: true,
    payload: JSON.stringify({ chat_id: CHAT_ID, text: text, disable_web_page_preview: true })
  });
}
