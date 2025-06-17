Классический брокер сообщений на основе обменников и очередей.

Модели обмена: Pub/Sub, очереди, RPC
Протокол: AMQP (также поддерживает MQTT, STOMP)

**AMQP** - Advanced Messaging Queuing Protocol, годен для финансовых операций (надёжность, гарантия доставки)
Особенности AMQP:
- Поддержка сложных сценариев (очереди, обменники, routing)
- Подтверждения (ack), транзакции и персистентность

**MQTT** - Message Queuing Telemetry Transport
Создан для IoT (малый трафик, работа на слабых устройствах)
Особенности MQTT:
- Лёгкий (минимум заголовков)
- Pub/Sub с топиками
- Нет сложного routing, только топики

**STOMP** - Simple Text Oriented Messaging Protocol
Создан для простоты (текстовый протокол как HTTP)
Особенности STOMP:
- Команды в виде текста (SEND, SUBSCRIBE)
- Нет встроенных гарантий доставки (можно реализовать поверх)

**Подтверждения получения (ack)**:
Ack (acknowledgment)
- Механизм, при котором потребитель (consumer) явно подтверждает что обработал сообщение чтобы брокер знал когда сообщение можно удалить из очереди
- Виды ack:
	- ack - сообщение обработано, можно удалять
	- nack - сообщение НЕ обработано (вернуть в очередь)
	- reject - отклонить (аналог nack)

DLQ (Dead Letter Queue)
- Очередь "мёртвых" сообщений
- Очередь, куда попадают сообщения, которые:
	- Не были обработаны после N попыток
	- Истёк их TTL
	- Были явно отклонены (reject, nack)
- Позволяет не терять сообщения и анализировать ошибки

Плюсы:
- Гибкость: поддерживает разные паттерны (очереди, Pub/Sub, routing)
- Надёжность:  подтверждение (ack), персистентность, реаликация очередей.
- Удобное управление: веб-интерфейс, CLI, HTTP API
- Подходит для сложных маршрутизаций (например разные обработчики для разных типов сообщений)

Минусы:
- Менее производительный, чем Kafka при высоких нагрузках (десятки тысяч сообщений в секунду)
- нет встроенной долгосрочной истории сообщений (в отличии от Kafka)

Когда пригождается:
- Транзакционные задачаи (например, обработка платежей)
- Сложная марщрутизация (разные подписчики на разные события)
- Системы, где важна надёжность доставки (ack, retry, DLQ)

Установка:
```bash
docker run -d --name rabbitmq -p 5672:5672 -p 15672:15672 rabbitmq:management
```

(Доступ к админке: `http://localhost:15672`, логин/пароль: `guest`/`guest`).

Установка бандла в Symfony:
```bash
composer require php-amplib/rabbitmq-bundle
```

Конфигурация:
```yaml
old_sound_rabbit_mq:
	connections:
		default:
			host: '%env(RABBITMQ_HOST)%'
			port: '%env(RABBITMQ_PORT)%'
			user: '%env(RABBITMQ_USER)%'
			password: '%env(RABBITMQ_PASSWORD)%'
	producers:
		email_notification:
			connection: default
			exchange_options: { name: 'notifications', type: direct }
	consumers:
		email_consumer:
			connection: default
			exchange_options: { name: 'notifications', type: direct }
			queue_options: { name: 'email_queue' }
			callback: App\MessageHandler\EmailNotificationHandler
```

Создание producer (отправка сообщений):
```php
class NotificationController extends AbstractController
{
	private ProfucerInterface $emailProducer;
	
	public function __construct(ProducerInterface $emailProducer)
	{
	$this->emilProducer = $emailProducer;
	}

	public function __invoke(): Response
	{
		$this->emailProducer->publish(json_encode(['email' => 'user@example.com', 'message' => 'Hello!']))

	return new Response('Message sent!');
	}
}
```

Создание Consumer (обработка сообщений):
```php
class EmailNotificationHandler
{
	public function __construct(private MailerInteface $mailer)
	{
	}

	public function execute(AMQMessage $msg): void
	{
		$data = json_decode($msg->body, true);

		$email = (new Email())
		->from('noreply@example.com')
		->to($data['email'])
		->subject('New Notification')
		->text($data['message']);

		$this->mailer->send($email);

		echo 'Email sent';
	}
}
```

Запуск consumer:
```bash
php bin/console rabbitmq:consumer email_consumer -vvv
```