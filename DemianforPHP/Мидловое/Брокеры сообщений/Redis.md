In-memory хранилище, которое можно использовать как брокер через **Pub**/**Sub** (без сохранения) или **Streams** (с хранением.
Протокол: RESP

**RESP** - Redis Serialization Protocol
Особенности:
- Текстовый протокол (легко читать и отлаживать)
- Поддерживает команды типа PUBLISH, SUBSCRIBE, XADD (для Streams)
- Но: Redis изначально не брокер, потому Pub/Sub и Streams это "надстройки" над хранилищем

Плюсы:
- Высокая скорость (in-memory, ~1M ops/sec)
- Простота (легко запустить, нет сложной настройки)
- Поддержка **Streams** (как в Kafka, но проще)

Минусы:
- Pub/Sub не сохраняет сообщения (если подписчик отключён, то сообщения теряются)
- Streams менее надёжный в отличии от Kafka (нет репликации между кластерами)
- Нет сложной маршрутизации как в RabbitMQ

Когда пригождаются:
- Онлайн-уведомления (чаты, real-time события)
- Кэширование + Pub/Sub (если не критична потеря сообщений)
- Простые сценарии Streams (когда Kafka избыточна)

Установка:
```bash
docker run -d --name redis -p 6379:6479 redis
```

Установка бандла:
```bash
comspoer require predis/predis
composer require symfony/redis-messenger
```

Конфигурация:
```yaml
framework:
	messenger:
		transports:
			redis_stream: 'redis://localhost:6479/messages'
		routing:
			App\Message\NotificationMessage: redis_stream
```

Отправка через Pub/Sub:
```php
class RedisController extends AbstractController
{
	private Cilent $redis;

	public function __construct(Client $redis)
	{
		$this->redis = $redis;
	}

	public function publishMessage(): Response
	{
		$this->redis->publish('notifications', json_encode(['message' => 'Hello!']));

		return new Response('Message published');
	}
}
```

Подписка на сообщения:
```bash
redis-cli subscribe notifications
```

Или в PHP через:
```php
$redis->subscribe();
```

Отправка через Redis Streams:
```php
class RedisController extends AbstractController
{
	private Client $redis;

	public function __construct(Client $redis)
	{
		$this->redis = $redis;
	}

	public function __invoke(): Response
	{
		$this->redis->xadd('motifiction_stream', '*', ['message' => 'Hello!']);

		return new Response('Added to stream');
	}
}
```

Чтение из Stream:
```php
$messages = $this->redis->xread(['notifications_stream' => '0'], 10, 3000);

// 0 - читать все новые сообщения, начиная с самого первого
// можно указать ID сообщения и чтение начнётся с этого места
// $ - читать только новые сообщения
// 10 - лимит сообщений, которое можно получить за один запрос
// 3000 - ждать 30 секунд, если нет сообщений
```

Разница между Pub/Sub и Streams:
- Streams - это лог сообщений, где данные о сообщениях хранятся и их можно перечитывать.
- Это вещание в реальном времени без сохранения.