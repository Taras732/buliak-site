# БУЛЯК — деплой і архітектура (для розробника)

> Джерело правди по «що і як ми деплоїмо». Секретів тут немає — лише посилання, де вони лежать.

## Архітектура (актуальна, WordPress)
- **WordPress + WooCommerce**, тема **Astra + child `buliak-astra`**.
- Хостинг: **ADM.tools VPS**, Ubuntu 24.04, IP `173.242.48.76`. Docker-стек `~/docker/buliak/` (під root).
- Контейнери: `buliak-wp` (wordpress), `buliak-db` (mariadb:11), `buliak-caddy` (reverse-proxy, авто-HTTPS Let's Encrypt).
- Домен: **https://buliak.com** (Namecheap NS → A-записи `@`+`www` на IP).
- Доступ: **Win/Claude → node-auto → VPS** (2 хопи; прямого Win→VPS ключа немає). SSH-ключ node-auto доданий у VPS `authorized_keys`.

## Що в репо (= що деплоїмо)
| Шлях | Що це |
|---|---|
| `wp-theme/buliak-astra/` | child-тема: `functions.php` (футер, checkout-поля, enqueue), `style.css`, `front-page.php`, `woocommerce/` overrides |
| `wp-theme/mu-plugins/buliak-shop.php` | магазин/UI: грід, картки, банер, cookie-нотіс, кошик-AJAX |
| `wp-theme/mu-plugins/buliak-checkout.php` | checkout + Нова Пошта (API) + Telegram-бот замовлень |
| `wp-theme/mu-plugins/buliak-admin.php` | адмінка замовлень (статуси, вага, історія) |
| `deploy/` | `docker-compose.yml`, `Caddyfile`, `backup.sh`, `import_photos.php` |
| `scripts/` | разові міграції (seed, reprice, content/catalog/legal fixes) |

## Що НЕ в репо (живе на VPS)
- **БД** (товари, сторінки, замовлення, налаштування). Бекап: **cron щодня 03:00** (`deploy/backup.sh` — дамп БД + tar `wp-content`, 7 ротацій).
- **Завантаження** `wp-content/uploads/` (фото товарів).
- **Секрети** (НП-ключ, TG-токен, LiqPay) — у **wp-options БД**, не в коді. DB-пароль — у `~/docker/buliak/.env` на VPS (gitignored).

## Як деплоїмо (workflow)
Правило: правити **локально** в `D:/Dev/buliak-site` → `git commit` (`feat/fix/style/docs(buliak):`) → **`git push` того ж дня** → деплой на VPS. Кожна зміна = коміт = відкатна (`git revert` / `git checkout <sha> -- file`).

### 1. Файл теми / mu-plugin
```bash
# Win → node-auto → VPS, потім у контейнер + права www-data
scp <file> node-auto:/tmp/
ssh node-auto "scp /tmp/<file> root@173.242.48.76:/tmp/ && ssh root@173.242.48.76 '
  docker cp /tmp/<file> buliak-wp:/var/www/html/wp-content/<DEST>
  docker exec -u root buliak-wp chown www-data:www-data /var/www/html/wp-content/<DEST>'"
```
`<DEST>`: тема → `themes/buliak-astra/<file>`, mu-plugin → `mu-plugins/<file>`.

### 2. Разовий скрипт (міграція БД/контенту)
wp-cli у контейнері НЕМАЄ. One-off через stdin (`wp eval-file -`, бо контейнер бачить лише volumes, не `/tmp`):
```bash
DBPW=$(grep ^DB_PASS ~/docker/buliak/.env | cut -d= -f2)
cat /tmp/script.php | docker run --rm -i --user 33:33 --volumes-from buliak-wp --network buliak_internal \
  -e WORDPRESS_DB_HOST=buliak-db -e WORDPRESS_DB_NAME=buliak -e WORDPRESS_DB_USER=buliak \
  -e WORDPRESS_DB_PASSWORD="$DBPW" wordpress:cli wp eval-file -
```

### 3. Фото товарів
Покласти `BLK-XX.jpg` (за SKU) → запустити `deploy/import_photos.php` (`media_handle_sideload` + `set_post_thumbnail`).

## Верифікація (рендер-loop)
UI перевіряти **скріном** (headless Chrome / puppeteer на node-auto), не наосліп. Mobile 390–430px. CSS Astra кешується → мобільні фікси виносити **inline у `wp_head` priority 999** (`buliak-shop.php`), не у `style.css`.

## Відновлення «якщо зламалось»
1. **Код:** `git clone https://github.com/Taras732/buliak-site` → деплой за кроками вище.
2. **БД + фото:** з VPS-бекапу `~/docker/buliak/backups/` (cron 03:00) — `gunzip` дамп → `docker exec -i buliak-db mariadb ...`, розпакувати tar у `wp-content`.
3. **Секрети** (НП-ключ, TG-токен) у бекапі БД є; якщо відновлюєш з чистого — ввести заново в адмінці.

---
_Стара статична версія (GitHub Pages + Google Sheets) — у `SETUP.md`, архівна, не використовується._
