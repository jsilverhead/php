Это AWS Simple Queue Service - облачная очередь сообщений от AWS.

Он позволяет:
- Отправлять, хранить и получать сообщения между компонентами системы.
- Работать в облаке без необходимости развёртывать свои брокеры.
- Масштабировать автоматически (не нужно настраивать кластеры)

### Отличие SQS от брокеров:
- Тип сервиса: облачный
- Модель: очереди FIFO/Standard
- Гарантии порядка: только в FIFO
- Хранение: до 14 дней (настраиваемое)
- Производительность: до 3000 msg/sec
- Управление: полностью managed

### Типы очередей:
#### FIFO
- Гарантия порядка (сообщения приходят строго в порядке отправки)
- Ровно одна доставка (дубли исключены)
- Ограничение в 3000 msg/sec на очередь

Примеры использования:
- Обработка финансовых транзакций (там где важен порядок)
- Задачи, где дублирование недопустимо

#### Standard
- Высокая пропускная способность (неограниченное количество сообщений в секунуду)
- Гарантия доставки хотя бы один раз (возмодны дубли)
- Нет гарантии порядка (сообщения идут в разной последовательности)

Примеры использования:
- Обработка логов
- Уведомления, которые можно обработать в любом порядке

### Как работать с SQS:
Отправка сообщения (AWS SDK):
```php
$client = new SqsClient([
'region' => 'us-east-1',
'version' => 'latest'
]);

$result = $client->sendMessage([
'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/123456789012/my-queue',
'MessageBody' => json_encode(['event' => 'payment', 'id' => 123])
])
```

Получение сообщения:
```php
$client = new Sqslient(
	[
	'region' => 'us-east-1',
	'version' => 'latest'
	]
);

$result = $client->receiveMessage(
	[
	'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/123456789012/my-queue',
	'MaxNumberOfMessages' => 1
	]
);

foreach($result->get('Messages') as $message) {
	$body = json_encode($message['Body'], true);
	echo 'Обработка сообщения: ' . $body['event'] . "\n";

	//Тут обрабатываем сообщение

	$client->deleteMessage(
		[
		'QueueUrl' => 'https://sqs.us-east-1.amazonaws.com/123456789012/my-queue',
		'ReceiptHandle' => $message['ReceiptHandle'],
		]
	);
}
)
```

### Плюсы и минусы SQS:
Плюсы:
- Полностью managed - не нужно настраивать кластеры и следить за диском
- Интеграция с AWS сервисами (Lambda, SNS, EC2)
- Автомасштабирование - справится с любым объёмом сообщений
- Дешевле чем Kafka/RabbitMQ (на небольших нагрузках)

Минусы:
- Нет сложной маршрутизации (типа Exchange)
- Ограничения в FIFO - 3000msg/sec
- Нет долгого хранения (14 дней макс)
- Привязка к AWS - сложно перенести на другой облачный провайдер

### Когда выбирать SQS:
- Когда используется AWS и хочется меньше dev-ops настроек.
- Нужна простая очередь без сложной маршрутизации.
- Малый/средний трафик (для высокийх нагрузок лучше Kafka)

### Альтернативы:
- Azure
- Google Cloud: Pub/Sub