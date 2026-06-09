/* ============================================================
   БУЛЯК — приймач замовлень із сайту → Telegram (+ лог у Sheet)
   ------------------------------------------------------------
   ЯК ПІДКЛЮЧИТИ (5 хв, патерн як team_form):
   1. sheets.google.com → новий аркуш (буде лог замовлень).
   2. Розширення → Apps Script → встав цей код.
   3. Створи бота: Telegram @BotFather → /newbot → скопіюй TOKEN.
   4. Дізнайся CHAT_ID: напиши боту будь-що, відкрий
      https://api.telegram.org/bot<TOKEN>/getUpdates → візьми "chat":{"id":...}
      (для групи — додай бота в групу, ID буде з мінусом).
   5. Встав BOT_TOKEN і CHAT_ID нижче.
   6. Розгорнути → Новий розгортання → тип "Веб-додаток":
      виконувати від "Я", доступ "Усі" → скопіюй URL (.../exec).
   7. Встав цей URL у cart.js → const ORDER_ENDPOINT = '...'.
   Готово — замовлення з сайту падають у Telegram і в аркуш.
   ============================================================ */

const BOT_TOKEN = 'PUT_BOT_TOKEN_HERE';
const CHAT_ID   = 'PUT_CHAT_ID_HERE';

function doPost(e) {
  try {
    const d = JSON.parse(e.postData.contents);
    sendTelegram(d.text || formatOrder(d));
    logToSheet(d);
    return json({ ok: true });
  } catch (err) {
    return json({ ok: false, error: String(err) });
  }
}

function sendTelegram(text) {
  UrlFetchApp.fetch('https://api.telegram.org/bot' + BOT_TOKEN + '/sendMessage', {
    method: 'post',
    contentType: 'application/json',
    muteHttpExceptions: true,
    payload: JSON.stringify({ chat_id: CHAT_ID, text: text, disable_web_page_preview: true })
  });
}

function logToSheet(d) {
  try {
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const sh = ss.getSheetByName('Orders') || ss.insertSheet('Orders');
    if (sh.getLastRow() === 0) {
      sh.appendRow(['Дата', 'Ім\'я', 'Телефон', 'Доставка', 'Адреса', 'Оплата', 'Сума', 'Коментар', 'Позиції']);
    }
    sh.appendRow([new Date(), d.name, d.phone, d.delivery, d.address, d.pay, d.total, d.note,
      (d.items || []).map(i => i.name + ' x' + i.qty).join('; ')]);
  } catch (err) { /* лог не критичний */ }
}

function formatOrder(d) {
  const lines = (d.items || []).map(i => '• ' + i.name + ' x' + i.qty + ' — ' + (i.price * i.qty) + ' грн').join('\n');
  return '🥩 НОВЕ ЗАМОВЛЕННЯ — БУЛЯК\n\n' + lines +
    '\n\nОрієнтовна сума: ' + d.total + ' грн' +
    '\n\n👤 ' + d.name + '\n📞 ' + d.phone + '\n🚚 ' + d.delivery +
    (d.address ? ' — ' + d.address : '') + '\n💳 ' + d.pay + (d.note ? '\n📝 ' + d.note : '');
}

function json(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
