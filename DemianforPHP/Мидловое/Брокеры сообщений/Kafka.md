Распределённый лог-ориентированный брокер (сообщения хранятся в виде логов)

Модели обмена: Pub/Sub с хранением истории
Протокол: Свой бинарный протокол поверх TCP

**Бинарный протокол**
Создан для максимальной производительности
Особенности:
- Данные предоставляются в бинарном виде
- Свой формат, оптимизированный под Kafka
- Клиенты должен знать спецификацию протокола

Плюсы:
- Высокая пропускная способность (миллионы сообщений в секунду)
- Хранение истории (сообщения хранятся долго, их можно перечитывать)
- Горизонтальное масштабирование (партиции, репликация)
- Поддержка потоковой обработки (Kafka Streams, Flink)

Миунсы:
- Сложнеее в настройке и администрировании (Zookeeper, брокеры, партиции)
- Нет гибкой маршрутизации как в RabbitMQ.
- Избыточен для простых сценариев.

Когда пригождается:
- Обработка больших потоков данных (логи, метрики, события).
- Стримминг данных (аналитика в реальном времени)
- Сценарии, где важна история сообщений (аудит, повторная обработка)

----
Протокол в рамках брокера - набор правил, по которым клиент и сервер обмениваются сообщениями.
Он определяет:
1. Формат сообщений (как структурированы данные)
2. Способ доставки (синхронный/асинхронный, подтверждения)
3. Авторизацию и безопасность (TLS, логины/пароли)
4. Семантику операций (как создать очередь, как подписаться)

Нужен для:
- Клиенты на разных языках могли работать с брокером
- Обеспечивать совместимость между реализациями брокеров

Установка:
```bash
comspoer require enqueue/rdkafka
```

Конфигурация:
```yaml
enqueue:
	transport:
		dsn: '%env(KAFKA_DSN)%'
		global:
			group.id: 'my_group',
			metadata.broker.kist: '%env(KAFKA_BROKER_LIST)%'
	client: ~
```

Создание Producer:
```php
class KafkaController extends AbstractController
{
	private RdKafkaContext $kafkaContext;

	public function __construct(RdKafkaContext $kafkaContext) {
		$this->kafkaContext = $kafkaContext;
	}

	public function __invoke(): Response
	{
		$topic = $this->kafkaContext->createTopic('notifications');
		$message = $this->kafkaContexnt->createMessage('Hello!');
		$this->kafkaContext->createProducer()->send($topic, $message);

		return new Response('Message sent');
	}
}
```

Создание Consumer:
```php
class KafkaNotificationHandler
{
	public function __invoke(Message $message, Context $context): void
	{
	$context->acknowledge($message);
	}
}
```

Запуск Consumer:
```bash
php bin/console enqueue:consume -vvv
```
