- Письма доставляются через transport. Из коробки модно отправлять письма через SMTP, настроив DSN в .env файле (`user`, `pass`, `port`).
```env
MAILER_DSN=smtp://user:pass@smtp.example.com:port
```

```php
return static function(FrameWorkConfig $framework): void
{
$mailer = $framework->mailer();
$mailer->dsn(env('MAILER_DSN'));
}
```

В случае с EmailSender используется сторонний транспорт:
```php
return static function(FrameWorkConfig $framework): void
{
$messenger = $framework->messenger();
$messenger->transport('email')->dsn(env('MESSENGER_TRANSPORT_EMAIL_DSN'))->seriazlier(EmailMessageSerializer);
}
```

Пример такого транспорта из env:
```env
MESSENGER_TRANSPORT_EMAIL_DSN=https://message-queue.api.cloud.yandex.net/b1gt0i9vn96lma004jks/dj6000000003po4k07tk/test
```