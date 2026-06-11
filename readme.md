# YevhenB Payments with Mono for WooCommerce

Lightweight payment gateway for WooCommerce that connects to the Monobank acquiring API for online payments, automatic order updates, and payment status tracking.

> This plugin is not affiliated with, endorsed by, or officially supported by Monobank, mono, Universal Bank, or WooCommerce.

---

## Features

✅ Payment gateway for WooCommerce  
✅ Invoice creation through the Monobank acquiring API  
✅ Automatic order status updates  
✅ Webhook support with signature verification  
✅ Payment history panel with manual payment status check  
✅ Multilingual support  
✅ Ukrainian localization included  

Designed to be lightweight and easy to configure.

---

## Installation

1. Upload plugin to:

```text
/wp-content/plugins/
```

2. Activate plugin

3. Open:

```text
WooCommerce → Settings → Payments
```

4. Enable:

```text
YevhenB Payments with Mono
```

5. Enter your Monobank API token

6. Save settings

---

## Requirements

| Requirement | Version |
|---|---:|
| WordPress | 6.0+ |
| WooCommerce | Required |
| PHP | 7.4+ |

---

## Webhook

Current webhook endpoint:

```text
https://your-site.com/smpl-mono-webhook
```

The endpoint is intentionally kept unchanged for compatibility with the existing working integration.

---

## External services

This plugin connects to the Monobank acquiring API to create payment invoices, check invoice status, and verify webhook signatures.

When a customer chooses this payment method during checkout, the plugin sends payment data to Monobank to create a payment invoice. This may include:

- order amount;
- currency;
- WooCommerce order ID as a payment reference;
- redirect URL;
- webhook URL;
- merchant API token configured by the store owner.

When the store administrator manually checks payment status, or when the scheduled fallback status check runs, the plugin sends the Monobank invoice ID to Monobank to retrieve the current invoice status.

When Monobank sends a webhook to the store, the plugin requests Monobank's public key from the Monobank API to verify the webhook signature.

This service is provided by Monobank / Universal Bank.

Service information and documents:  
https://monobank.ua/en/documents

API documentation:  
https://monobank.ua/api-docs/acquiring

Developer API documentation:  
https://api.monobank.ua/docs/acquiring.html

Security information:  
https://monobank.ua/en/security

---

## Localization

Current languages:

- 🇺🇦 Ukrainian
- 🇺🇸 English

Translation-ready using:

```text
gettext
.po
.mo
```

---

## Development notes

Some internal identifiers are intentionally kept unchanged for compatibility:

```text
Gateway ID: smpl_mono
Webhook endpoint: /smpl-mono-webhook
Meta keys: _mono_invoice_id, _mono_status, _mono_updated
```

---

## Roadmap

### Free

- [x] Payment gateway for WooCommerce
- [x] Monobank acquiring API integration
- [x] Webhooks
- [x] Payment history panel
- [x] Manual payment status check
- [x] Localization
- [ ] Better admin table
- [ ] Test mode

### Pro / Future ideas

- [ ] Telegram notifications
- [ ] CSV export
- [ ] Payment analytics
- [ ] Advanced logs
- [ ] Custom order statuses

---

## Contributing

Issues, suggestions, and pull requests are welcome.

---

## License

GPL v2 or later

https://www.gnu.org/licenses/gpl-2.0.html

---

# Українська версія

# YevhenB Payments with Mono for WooCommerce

Легкий платіжний шлюз для WooCommerce, який підключається до Monobank acquiring API для онлайн-оплат, автоматичного оновлення замовлень і відстеження статусу платежів.

> Цей плагін не є офіційним плагіном Monobank, mono, Universal Bank або WooCommerce, не афілійований з ними та не підтримується ними офіційно.

---

## Можливості

✅ Платіжний шлюз для WooCommerce  
✅ Створення інвойсів через Monobank acquiring API  
✅ Автоматичне оновлення статусу замовлення  
✅ Підтримка webhook з перевіркою підпису  
✅ Панель історії платежів із ручною перевіркою статусу  
✅ Підтримка багатомовності  
✅ Українська локалізація включена  

Плагін створений як легке та просте в налаштуванні рішення.

---

## Встановлення

1. Завантажте плагін у:

```text
/wp-content/plugins/
```

2. Активуйте плагін

3. Відкрийте:

```text
WooCommerce → Налаштування → Платежі
```

4. Увімкніть:

```text
YevhenB Payments with Mono
```

5. Введіть Monobank API token

6. Збережіть налаштування

---

## Вимоги

| Вимога | Версія |
|---|---:|
| WordPress | 6.0+ |
| WooCommerce | Обовʼязково |
| PHP | 7.4+ |

---

## Webhook

Поточний webhook endpoint:

```text
https://your-site.com/smpl-mono-webhook
```

Endpoint навмисно залишено без змін для сумісності з уже робочою інтеграцією.

---

## Зовнішні сервіси

Цей плагін підключається до Monobank acquiring API для створення платіжних інвойсів, перевірки статусу інвойсів і перевірки підписів webhook-запитів.

Коли покупець обирає цей метод оплати під час checkout, плагін надсилає до Monobank платіжні дані для створення інвойсу. Це може включати:

- суму замовлення;
- валюту;
- ID замовлення WooCommerce як payment reference;
- redirect URL;
- webhook URL;
- merchant API token, налаштований власником магазину.

Коли адміністратор магазину вручну перевіряє статус платежу або коли спрацьовує запланована fallback-перевірка статусу, плагін надсилає Monobank invoice ID до Monobank для отримання поточного статусу інвойсу.

Коли Monobank надсилає webhook на сайт, плагін запитує публічний ключ Monobank API для перевірки підпису webhook-запиту.

Сервіс надається Monobank / Universal Bank.

Інформація про сервіс і документи:  
https://monobank.ua/en/documents

API documentation:  
https://monobank.ua/api-docs/acquiring

Developer API documentation:  
https://api.monobank.ua/docs/acquiring.html

Security information:  
https://monobank.ua/en/security

---

## Локалізація

Поточні мови:

- 🇺🇦 Українська
- 🇺🇸 Англійська

Плагін підготовлений до перекладу через:

```text
gettext
.po
.mo
```

---

## Примітки для розробки

Деякі внутрішні ідентифікатори навмисно залишені без змін для сумісності:

```text
Gateway ID: smpl_mono
Webhook endpoint: /smpl-mono-webhook
Meta keys: _mono_invoice_id, _mono_status, _mono_updated
```

---

## Roadmap

### Free

- [x] Платіжний шлюз для WooCommerce
- [x] Інтеграція з Monobank acquiring API
- [x] Webhooks
- [x] Панель історії платежів
- [x] Ручна перевірка статусу платежу
- [x] Локалізація
- [ ] Покращена адмін-таблиця
- [ ] Тестовий режим

### Pro / майбутні ідеї

- [ ] Telegram-сповіщення
- [ ] CSV export
- [ ] Payment analytics
- [ ] Advanced logs
- [ ] Custom order statuses

---

## Contributing

Issues, suggestions, and pull requests are welcome.

---

## Ліцензія

GPL v2 or later

https://www.gnu.org/licenses/gpl-2.0.html
