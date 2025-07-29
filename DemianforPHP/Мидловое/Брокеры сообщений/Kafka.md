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

### Запуск в docker-compose
Требует запуска двух сервисов - Kafka и Zookeeper
```yaml
version: '3.8'

services:
	zookeeper:
		image: confluentinc/cp-zookeper:7.4.0
		ports:
			- "2181:2181"
		environment:
			ZOOKEPER_CLIENT_PORT: 2181

	kafka:
		image: confuentinc/cp-kafka:7.4.0
		ports:
			- "9092:9092"
		environment:
			KAFKA_BROKER_ID: 1
			KAFKA_ZOOKEPER_CONNECT: zookeper:2181
			KAFKA_LISTENER_SECURITY_PROTOCOL_MAP: PLAINTEXT:PLAINTEXT, PLAINTEXT_INTERNAL:PLAINTEXT
			KAFKA_ADVERTISED_LISTENERS: PLAINTEXT://localhost:9092,PLAINTEXT_INTERNAL://kafka:29092
			KAFKA_OFFSETS_TOPIC_REPLICATION_FACTOR: 1
		depends_on:
			- zookeeper
```

Проверить работу через Kafkacat

##### По переменных окружения:
- `KAFKA_BROKER_ID` - уникальный идентификатор брокера в кластере
Аналог в конфиге `broker.id`
- `KAFKA_ZOOKEEPER_CONNECT` - указывает на адрес Zookeeper, который управляет метаданными Kafka - топики, партции, брокеры
- `KAFKA_LISTENER_SECURITY_PROTOCOL_MAP` - определяет протоколы безопасности для листенеров. Формат `ИМЯ_ЛИСТЕНЕРА:ПРОТОКОЛ,ИМЯ_ЛИСТЕНЕРА2:ПРОТОКОЛ`.
	Варианты протоколов:
	- PLAINTEXT - без шифрования, для тестов
	- SSL - с шифрованием
	- SASL_PLAINTEXT - аутентификация без шифрования
	- SASL_SSL - аутентификация + шифрование
- `KAFKA-ADVERTISED_LISTENERS` - адреса, которые Kafka сообщает клиентам для подключения. В моём случае `localhost:9092` - для клиентов на хосте (вне docker) и `kafka:29092` для клиентов внутри Docker-сети. Если Kafka в кластере, то нужно указать реальные DNS\IP адреса.