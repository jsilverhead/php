Два варианта:
1. Через Mercure
2. Через Redis


### Mercure

Mercure - используется для отправки сообщений от сервера к клиенту.

```terminal
composer require mercure ## Или symfony/mercure
```
#### Запуска хаба Mercure

Mercure использует хаб, выделенный сервер, который отрабатывает постоянные соедениня с SSE клиентами. Cерверные ивенты хранятся на хабе и отправляются через хаб.

APP -> HUB -> SSE -> CLIENT

На проде необходимо установить хаб самостоятельно. Официальный open-source хаб, основанный на Caddy можно скачать с https://mercure.rocks, также предоставляются там Docker-image, Helm Chart для Кубера.

При помощи интеграции Docker можно запустить Mercure через:
```terminal
docker compose up
```

#### Настройка
Предпочтительным способом настройки mercure являются env файлы.

[x] После установки MercureBundle в .env файл будет добавлен рецепт для включения доступных переменных окружения.

[x] Также при использовании Dcoker с Symfony Local Web Server или API Platform distribution, соответствующие переменные среды были автоматически установлены.

В противном случае нужно в .env добавить:
- MERCURE_URL - для локального использования
- MERCURE_PUBLIC_URL - для публичного использования

Клиент должен предоставить токен JSON Web Token в Mercure Hub, чтобы подписываться на события и публиковать обновления.

Секретный ключ для токена должен быть таким же, какой используется Hub для проверки JWT.
Этот ключ должен быть сохранён в MERCURE_JWT_SECRET в .env.
Mercure будет его использовать для генерации и подписки необходимых JWT.

Также есть доп.конфигурация:

- secret - ключ, используемый для подписи JWT. Должен использоваться ключ того же размера, что и выходной хэш (например HS256)
- publish - список тем, в которых разрешена публикация при создании JWT (применяется только при наличии secret)
- subscribe - список тем, на которые можно подписаться при создании JWT (применяется только при наличии secret)
- algorithm - алгоритм, используемый для подписки JWT (можно использовать только при наличии secret)
- provider - идентификатор службы, вызываемой для предоставления JWT (все остальные параметры будут проигнорированы)
- factory - идентификатор службы, вызываемой для создания JWT (все остальные параметры кроме subscribe и publish будут игнорироваться)
- value - необработанный JWT для использования (все остальные параметры будут проигнорированы)

Настройка mercure:
```php
# config/packages/mercure.php

$container->loadFromExtension('mercure', [
	'hubs' => [
		'default' => [
			'url' => '%env(string:MERCURE_URL)%',
			'public_url' => '$env(string:MERCURE_PUBLIC_URL)%'
			'jwt' => [
				'secret' => '%env(string:MERCURE_JWT_SECRET)%',
				'publish' => ['https://example.com/foo1', 'https://example.com/foo2'],
				'subscribe' => ['https://example.com/bar1', 'https://example.com/bar2'],
				'algorithm' => 'hmac.sha256',
				'provider' => 'My\Provider',
				'factory' => 'My\Factory',
				'value' => 'my.jwt',
			],
		],
	],
]);
```

<!> Чтобы клиент мог публиковать данные, payload JWT должна сожержать следующую структуру:
```json
{
	"mercure": {
		"publish": ["*"]
	}
}
```

#### Использование

##### Update:
Компонент Mercure  предоставляет Update value object, предоставляющий обновление публикаций.
Он также предоставляет Publisher услугу для отправки обновлений в Hub.

Службу Publisher можно внедрить с помощью автовайринга, в любую другую службу, включая контроллеры.

```php
# src/Controller/PublishController.php

class PublishController extends AbstractController
{
	public function publish(HubInterface $hub): Response
	{
		$update = new Update(
		'https://example.com/books/1',
		json_encode(['status' => 'OutOfStock'])
		);

		$hub->publish($update);

		return new Response('published!');
	}
}
```

Первый параметр, который передаётся в Update это тема, которая обновляется. Эта тема должна быть IRI (Internaltional Resource Identifier, RFC 3987) - уникальный идентификатор ресурса, который диспатчится.

Обычно этот параметр содержит оригинальный URL ресурса, передаваемый клиенту, но также это может быть IRI строка.

Второй параметр конструктора это контент, который обновляется. Это может быть что угодно и храниться в любом формате.
Тем не менее для сериализации рекомендуется использовать форматы: json-ld, atom, html, xml.
##### Обнаружение:
Протокол Mercure идёт в связке с механизмом обнаружения. Чтобы использовать его, приложение Symfony должно предоставить URL Mecrure в заголовке HTTP (Link).

Можно создавать Link заголовки с помощью `Discovery` класса, внутри которого есть компонент `weblink`:
```php
# src/Controller/DiscoverController.php

class DiscoverController extends AbstractController
{
public function discover(Request $request, Discovery $discrovery): JsonResponse
	{
		// Link: <https://hub.example.com/.well-known/mercure>; rek="mercure"
		$discovery->addLink($request);

		return $this->json([
		'@id' => '/book/1',
		'avilability' => 'https://schema.org/InStock'
		]);
	}
}
```

Затем этот заголовок можно проанализировать на стороне клиента.

##### Авторизация
Mercure позволяет отправлять обновления только авторизованным клиентам. Чтобы сделать это, нужно отметить обновление как частное, установив третий параметр конструктора Update на true.
```php
# src/Controller/PublishController.php

class PublishController extends AbstractController
{
public function publish(HubInterface $hub): Response
{
	$update = new Update(
	'https://example.com/book/1',
	json_encode(['status' => 'OutOfStock']),
	true
	);

	$hub->publish($update);

	return new Response('private update published!');
}
}
```

Чтобы подписаться на частные обновления, подписчики должны предоставить Hub JWT, содержащий селектор тем, соответствующий теме обновления.

Для предоставления этого JWT подписчик может использовать cookie-файл или Authorization HTTP заголовок.

Файлы cookie могут быть установлены автоматически Symfony путём передачи соответстующих параметров в mercure.

##### Программное создание JWT
Вместо того чтобы напрямую сохранять JWT в конфигурации, можно создать поставщика токенов, который используется интерфейсом HubInterface:
```php
# src/Mercure/MyTokenProvider.php

class MyTokenProvider implements TokenProviderInterface
{
	public function getJwt(): string
	{
		return 'the-JWT';
	}
}
```

А в конфиге указать:
```php
# config/packages/mercure.php

$container->loadFromExtension('mercure', [
	'hubs' => [
		'default' => [
			'url' => 'https://mercure-hub.example.com/.well-known/mercure',
			'jwt' => [
				'provider' => MyJwtProvider::class,
			]
		]
	]
])
```

[x] Это особенно полезно, при использовании токенов, имеющих срок действия и обновляются программно.

##### Web-API
При создании web-API удобно иметь возможность мнгновенно отправлять новые версии ресурсов на все подключенные устройства и обновлять их представления.

API Platfrom может использовать компонент Mercure для автоматической отправки обновлений каждый раз при создании, изменении или удалении ресурса api.

Сперва установи библиотеку:

```
composer require api
```

Затем создай сущность, чтобы получить полнофункциональный API гипермедиа и автоматическую трансляцию обновлений через Mercure hub.
```php
# src/Entity/Book.php

#[ApiResouce(mercure:true)]
#[ORM\Entity]
class Book

#[ORM\Id]
#[ORM\Column]
public string $name = '';

#[ORM\Column]
public string $status = '';
```

<?> тут нужно ещё почитать всякого! <?>
##### Тестирование:

```php
class MessageControllerTest extends TestCase
{
	$hub = new MockHub('https://internal/.wll-known/mercure', new StaticTokenProvider('foo'), function(Update $update): string {
	// $this->assertTrue($update->isPrivate());

	return 'id';
	});

	$controller = new MessageController($hub);

	// ...
}
```

----
### Redis

Также для работы с SSE ивентами необходимо 