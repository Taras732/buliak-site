# БУЛЯК — налаштування магазину (АРХІВ, стара статична версія)

> ⚠️ **АРХІВНО.** Це опис першої версії (статичний GitHub Pages + Google Sheets), яку **відкинуто**. Актуальний сайт — WordPress на VPS, деплой описано в [DEPLOY.md](DEPLOY.md).

Сайт статичний (GitHub Pages), база — **Google Sheets**, логіка — **Apps Script**.
Все безкоштовно. Налаштування один раз ~15 хв.

## 1. Google Sheet + Apps Script (API)
1. [sheets.google.com](https://sheets.google.com) → новий аркуш «БУЛЯК».
2. **Розширення → Apps Script** → встав код із [buliak-api.gs](buliak-api.gs), збережи.
3. Заміни `ADMIN_PASS` на свій пароль.
4. (Опційно) Telegram-сповіщення: `BOT_TOKEN` (від @BotFather) + `CHAT_ID`.
5. **Розгорнути → Новий розгортання → Веб-додаток**: виконувати «від мене», доступ «Усі» → **скопіюй URL** (`.../exec`).

> Аркуші `Products` і `Orders` створяться автоматично.

## 2. Вписати URL у 3 файли
Встав скопійований `.../exec` URL:
| Файл | Рядок |
|---|---|
| [cart.js](cart.js) | `const ORDER_ENDPOINT = '...'` |
| [catalog.js](catalog.js) | `const API = '...'` |
| [admin.html](admin.html) | `const API = '...'` |

Закоміть і запуш — GitHub Pages оновиться.

## 3. Готово
- **Адмінка:** `taras732.github.io/buliak-site/admin.html` → пароль → замовлення + товари.
- **Товари:** додаєш в адмінці → одразу на сайті (каталог читає з таблиці).
- **Замовлення:** з сайту падають в аркуш `Orders` + (опц.) Telegram, видно в адмінці.

## Як працює модель ціни (плаваюча вага)
- Товари продаються **порціями з фіксованою ціною** (Сценарій А).
- Сума в кошику — орієнтовна; фінал по факту зважування.
- Деталі: `10_Projects/TS_Practice/08_Deliverables/Buliak_MVP_Concept_and_Questions.md` (у vault).

## Файли
| Файл | Призначення |
|---|---|
| `index.html` | сайт + кошик |
| `cart.js` | логіка кошика та оформлення |
| `catalog.js` | динамічний каталог із таблиці (fallback — статичний) |
| `admin.html` | адмінка (пароль) |
| `buliak-api.gs` | Apps Script — API (вставити в Google Apps Script) |
