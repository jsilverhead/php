Symfony _охватывает_ жизненный цикл HTTP запрос-ответ.

Инициализация проекта:
```terminal
symfony new <name>
```
ИЛИ
```terminal
composer create-project <name>
```
Данная команда создаст все нужные директории, с которым можно впоследствии работать.

Также можно запустить демо-проект:
```terminal
symfony new <name> --demo
```

----
В app/src лучше строить MVC архитектуру. Отделяя доменную часть от инфраструктурной.

----
Создание сущности через make:
```terminal
bin/console make:entity <name>
```

Создание контроллера через make:
```terminal
bin/console make:controller <name>
```

----
После создания сущности необходимо сделать миграцию?
```terminal
bin/console make:migration
```

Команда создаст файл в папке migrations с версией миграции.
Завершить миграцию нужно командой:
```terminal
bin/console doctrine:migrations:migrate
```

----
Можно наполнить БД фейковыми данными через фикстуры. Для этого потребуется: doctrine-fixtures-bundle а также fzaninotto/faker

```terminal
composer require --dev doctrine/doctrine-fixtures-bundle
```

doctrine-fixtures-bundle - отправляет фейковые данные, позводяет выбирать в какой последовательности их генерировать при наличии связей.

```terminal
composer require fzaninotto/faker
```

faker - создаёт случайные данные.

----
При использовании composer периодически есть необходимость чистить кэш и обновлять composer.
```terminal
composer update
```

```terminal
bin/console cache:clear --no-warmup // no-warmup 
```

----
### Twig

Если нужно чтобы php отображал HTML, нужно поставить Twig:
```terminal
composer require twig
```

Сделать Controller -> extends AbstractController

В return добавить:
```php
return $this->render(
'lucky/number.html.twig',
['luckyNumber' => $luckyNumber])
```

Создать в папке templates шаблон lucky/number.html.twig с содержимым:
```twig
{# templates/lucky/number.html.twig #}
<h1>Твой счастливый номер:</h1>
<h3>{{luckyNumber}}</h3>
```

После запуска сервера - получим в результате страницу с кодом из шаблона.

Наличие тега `<body></body>` в шаблоне позволит видеть панель отладки.

----
### Маршруты как атрибуты:
Для использования маршрутов как атрибуты, необходимо создать файл attributes.yaml в папке config/routes/

Содержимое:
```yaml
# config/routes/attributes.yaml
controllers:
    resource:
        path: ../../src/Controller/
        namespace: App\Controller
    type: attribute

kernel:
    resource: App\Kernel
    type: attribute
```

Эта конфигурация сообщает Symfony искать маршруты, определенные как атрибуты, в классах, объявленных в пространстве имен `App\Controller`, и хранящихся в каталоге `src/Controller/`, которая следует стандарту PSR-4.

----
##### Как прописать роут в routes.yaml?
```yaml
blog_list:  // имя роута
    path: /blog  
        # значение контроллера ммеет формат 'controller_class::method_name'
    controller: App\Controller\BlogGController::list
    
    # если действие реализуется как метод __invoke() класса контроллера,
    # вы можете пропустить часть '::method_name':
    # controller: App\Controller\BlogController
```

Имя роута указывается на:
```php 
#[Routes(path: '/blog', name: 'blog_list')]
```

В Routes также можно указывать методы:
```Terminal
#[Routes(path: 'api/paths/{id}', methods: ['GET', 'HEAD'])]
```

`condition` - используется если нужно чтобы какой-то роут совпадал:

```php
#[Route(
        '/contact',
        name: 'contact',
        condition: "context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'",
        // выражения также могут включать в себя параметры конфигурации:
        // condition: "request.headers.get('User-Agent') matches '%app.allowed_browsers%'"
    )]
```

ИЛИ
```php
#[Route(
        '/posts/{id}',
        name: 'post_show',
        // выражения могут извлекать значения параметра маршрута, используя переменную "params"
        condition: "params['id'] < 1000"
    )]
```

`condition` - это выражение ExpressionLanguage и имеет следующие параметры в с Symfony:

`context` - экземпляр `RequestContext`, которая содержит более фундаментальную информацию о  сопоставляемом маршруте.

`request` - объект запроса Symfony, который представляет текущий запрос.

`params` - Массив совпадающих параметров маршрута для текущего маршрута.

А также функции:

`env(string $name)` - возвращает значение переменной, используя процессоры переменных окружения.

`service(string $alias)` - возвращает сервис условий маршрутизации.

Можно создать:
```php
#[AsRoutingConditionService(alias: 'route_checker')]
class RouteChecker
{
	public function check(Request $request):bool
	{
		//... условия
	}
}
```

Далее его можно использовать в condition:

```php
// Controller (using an alias):
#[Route(condition: "service('route_checker').check(request)")]
// Or without alias:
#[Route(condition: "service('Ap\\\Service\\\RouteChecker').check(request)")]
```

----
Для проверки всех роутов использовать команду:
```terminal
bin/console debug:router
```

ИЛИ

```terminal
bin/console debug:router app_lucky_number // Покажет информацию по роуту
```

`--show-aliases` - покажет все доступные псевдонимы роута

Для проверки соответствует ли URL контроллеру используется:
```terminal
bin/console router:match /lucky/number/8
```

----
### Параметры Routes
Есть роуты, где есть изменяемая переменная. Например slug или id. Переменные заключены в `{ }` типа `/blog/{slug}`

В роуте можно передавать несколько параметров, но каждый из них можно использовать лишь единожды. Например:
`{category}/blog/{page}`

----
### Валидация параметров
Если иметь два одинаковых роута с разными параметрами, например `blog/{slug}` и `blog/{page}` - симфони откроет тот роут, который найдёт первым.
Чтобы открыть конкретный необходимо использовать `requirements`:
```php
#[Route('path: 'blog{page}, name 'blog_list', requirements: ['page' => '\d+'])]
```

`\d` - регулярное выражение целого числа.

`requirements` часто использует регулярные выражения для валидации.

Требования можно внедрить в сам параметр:
```php
#[Route(path: 'blog/{page<\d+>}', name: 'blog_list')]
```
----
### Необязательные параметры
Если настроить роут формата `blog/{page}`, то переход по роуту `blog/` ниченр не сделает.
Чтобы он соответствовал какому-то значению по умолчанию, это прописывается в методе:
```php
#[Route(path: 'blog/{page}', name: 'blog_list', requirements: ['page' => '\d+'])]
public function list(int $page = 1): Response
{
	//...
}
```

Если есть желание включить  какое-то значение по умолчанию в сгенерированном роуте, то нужно использовать `!` в имени параметра:
`blog/{!page}`

Значения по умолчанию также могут быть встроены в сам параметр:
`{parameter_name?default_value}`

Например:
```php
#[Routes(method: 'blog/{page<\d+>?1}', name: 'blog_list')]
public function list(int $page): Response
{
	//...
}
```
----
### Параметры приоритетности
Symfony оценивает роуты в том порядке, в котором они были определы.
Можно установить параметр `priority` в атрибутах чтобы контролировать приоритеты.
```php
#[Routes(method: 'blog/list', name: 'blog_list', priority: 2)]
public function list(): Response
{
	//...
}
```

Значение по умолчанию, если не определено `0`.

----
### Конверсия параметров
Распространённой потребностью маршрутизации является конверсия значения, хранящегося в некотором параметре, в другое значение.

Для добавление конвертера параметров:
```terminal
composer require sensio/framework-extra-bundle
```

Работает вот так:
```php
#[Route(path: 'blog/{slug}', name: 'blog_show)]
public function show(BlogPost $post): Response
{
	// $post - объект, чей слаг соответствует параметру маршрутизации

	//...
}
```

В случае такого роута, param converter проверит в БД наличие объекта с данным параметром.
Если не найдёт, Symfony отдаст 404.

----
### Подкреплённые параметры исчисления
В качество параметров маршрута можно использовать Enum, посколько Symfony автоматически преобразует их в скалярные значения.
```php
#[Route(path: 'orders/list/{status}', name: 'list_order_by_status')]
public function list(OrderStatusEnum $status = OrderStatusEnum::paid): Response
{
	//...
}
```
----
### Специальные параметры:
В дополнение кастомным параметрам, маршруты могут иметь любые следующие параметры, созданные Symfony:

`_controller` - для определения того, какой контроллер и действие выполняется при совпадении маршрута.

`_format` - совпавшее значение испольщуется для установки 'request format' объекта `Request`. Используется для таких вещей, как установка `Content-Type`.

`_fragment` - для установки идентификатора фрагмента, что является последней необязательной частью url, которая начинается с символа `#` и используется для идентификации части документа.

`_locale` - для установки локали в запросе.

Пример:
```php
#[Route(
	path: '/articles/{_locale}/search.{_format}',
	locale: 'en'
	format: 'html'
	requirements: [
		'_locale' => 'en|fr',
		'_format' => 'html|xml'
		],
	)]
	public function search():Response
	{
	}
```
----
### Дополнительные параметры
В опцию роута `defaults` можно определить параметры, не включённые в конфигурацию роута. Полезно для передачи доп.аргументов

```php
#[Route(path: 'blog/{page}', name: 'blog_index', defaults: ['page' => 1, 'title' => 'Hello world'])]
publuc function inxed(int $page, string $title): Response
{
	//...
}
```

----
### Символы слеша в параметрах
Параметры роута могут содержать любые символы кроме слеша `/`, для получения слеша, например в токене, можно изменить требования параметра.
```php
#[Route(path: 'share/{token}', name: 'share', requirements: ['token' => '.+'])]
public function share($token): Response
{
	//...
}
```
----
### Псевдонимы роута
Позволяет иметь несколько имён для одного роута.
```yaml
#config/routes.yaml

nrew_route_name:
	alias: original_route_name
```
----
### Устаревание псевдонимов маршрута
Если какой-то псевдоним больше не стоит использовать, можно оъявить его устаревшим:
```yaml
new_route_name:
	alias: original_route_name
	deprecated:
		package: 'acme/package'
		version: '1.2'
```
----
### Группы и префиксы маршрутов
Роуты могут иметь общие опции, потому в Symfony есть общая конфигурация.
```php
#[Route(path: '/blog', requirements: ['_locale => 'en|es|fr', name: 'blog'])]
class BlogController extends AbstractController

#[Route(path: '/{_locale}', name: 'index')]
public function index(): Response
{
	//...
}

#[Route(path: '/{_locale}/posts/{slug}', name: 'show')]
public function show(): Response
{
	//...
}
```

Если маршрут с префиксом определяет пустой путь, то Symfony подставит в конце пути `\`. Чтобы этого не происходило, необходимо прописать `trailing_slash_on_root: false`:
```yaml
controllers:
	resorce: '../../src/Controller/'
	type: attribute
	prefix: '/blog'
	trailing_slash_on_roote: false
	# ...
```
----
### Получение имени и параметра маршрута
Обычно Request в  Symfony хранит всю конфигурацию роута. Эту информацию можно получить через объект Request:
```php
#[Route(path: '/blog', name: 'blog_list')]
public function list(Request $request): Response
{
	$routeName = $request->attributes->get('_route');
	$routeParameters = $request->attributes->get('_route_params');

	// Получит все доступные атрибуты:
	$allAttributes = $request->attributes->all();
}
```
----
### Как это делается у нас
У нас Request превращается в Payload (данные +  headers) путётм:
1. Получения данных, в зависимости от запроса GET/POST
2. Если запрос не соответствует GET/POST, тогда выйдет ошибка
3. Получение Headers из Request, разделение строки авторизации на слова: Bearer и сам токен.
4. Если токена врнутри нет, то accessToken = null.

Получение данных:
```php
public function getData(Request $request): Payload
{
	$data = match($request->getMethod()) {
		RequestEnum:POST => $this->deserializeRequest($request),
		RequestEnum:GET => $request->query->getAll(), // Получает данные из query
		default: throw new MethodIsNotAllowedException(allowed: [RequestEnum:POST, RequestEnum:GET]);
	}
}

public function deserializeRequest(Request $request): array
{
	$payload = $request->getContent(); // Получает данные из request

	if ('' = $payload) {
		return [];
	}
	
	try {
		$data = json_decode(payload: $payload, associative: true, flags: \JSON_THROW_ON_ERROR);
	} catch(JsonException) {
		throw new UnparsableRequestExcetpion();
	}

	if (\is_array($data)) {
		throw new UnparsableRequestExcetpion();
	}

	return $data;
}

public function getHeaders(Request $request): Header
{
	$authorization = $request->headers->get('Authorization'); // позволит получить хэдеры из реквеста

	if (null === $authorization) {
		return new Header(accessToken: null);
	}

	$split = exploder(' ', $authorization);

	if(2 !== count($split) || 'Bearer' !== $split[0] || '' === $split[1]) {
		throw new InvalidAuthorizationHeaderException();
	}

	return new Header(accessToken: split[1]);
}
```

У нас есть PayloadResolver, который является кастомным резолвером ValueResolverInteface.

В системе есть встроенные резолверы, также можно ставить и свои

Интефрейс определяет что делать с Request при вызове контроллера, подбирая резолверы, с учётом приоритезации.

У нас приоритет Payload Resolver отдаётся 2. 1 место у резолверов AdminResolver и UserResolver.

----
### Argument MetaData

Когда мы создаётм метод контроллера в формате `public function __invoke(User $user, Payload $payload)` - симфони определяет необходимую сигнатуру контроллера. Нужен резолвер, который даст информацию о метаданных и сформирует их в объект. Тип объекта определяется в ArgumentMetadata type.

```php
public function resolve(Request $request, ArgumentMetaData $metadata): iterable
{
	if (Admin::class !== $metadata->getType) {
		return [];
	}

	$admin = $this->security->getUser();

	if (!$admin instanceOf Admin) {
		throw new AccessIsDeniedException();
	}

	$failedPermissions = array_filter(
		$arguments->getAttributesOfType(AllowedPermission::class), static fn(AllowedPermission $allowedPermission): bool => null === $admin->role || false === $admin->role->can($allowedPermission->permissions),
	);

	if (count($failedPermissions) > 0) {
		throw new AccesIsDeniedException();
	}

	return [$admin];
}
```

Обычно отправляется вместе с запросом в формате какого-нибудь токена.

----
### Специальные маршруты
Можно создавать специальные роуты, которые редиректят на другие роуты. Зачем? А хрен его знает)
Например `RedirectController`:
```yaml
doc_shortcut:
	path: /doc
	controller: Symfony\Bundle\FrameworkBudle\Controller\RedirectController
	defaults:
		route: 'doc_page'
		page: 'index'
		version: 'current'
		permanent: true
		keepQueryParams: true
		keepRequestmethod: true
		ignoreAttributes: true

leagcy_doc:
	path: /legacy/doc
	controller: Symfony\Bundle\FrameworkBundle\RedirectController
	defaults:
		path: 'https://legacy.example.com/doc'
		permanent: true
```

Symfony также предоставляет утилиты для перенаправления внутри контроллеров.
##### Перенаправление URL с замыкающими слешами:
Исторически URL следлвали соглашению UNIX о добавлении замыкающих слешей для каталогов (`https://www.example.com/foo/`) и их удаления для ссылания на файлы (`https://www.example.com/foo/bar`).

Symfony следует этой логике для перенаправления между URL с и без замыкающего слеша (но только для запросов `GET` и `HEAD`).

|Тут должна была быть таблица, но там хуйня какая-то с кодировкой походу|

----
### Маршрутизация подкаталогов
Роут может конфигурировать опцию `host`, чтобы требовать, чтобы HTTP-хост запросов совпадал с конкретным значением и отзываться на конкретный роут в зависимости от хоста:
```php
#[Route(path: '/', name: 'mobile_homepage', host: 'm.example.com')]
public function mobileHomepage(): Response
{
	//...
}
#[Route(path: '/', name: 'homepage')]
public function homepage(): Response
{
	//...
}
```

Значение опции `host` может иметь параметры и эти параметры могут быть тоже валидированы с помощью requirements:
```php
#[Route(path: '/', name: 'mobile_homepage', host: '{subdomain}.example.com', defaults: ['subdomain' => 'm'], requirements: ['subdomain' => 'm|mobile'])]
public function mobileHomepage(): Response
{
	//...
}

#[Route(path: '/', name: 'homepage')]
public function homepage(): Response
{
	//...
}
```

При использовании маршрутизации субкаталогов, нужно устанавливать HTTP-заголовки `host` в функциональных тестах, иначе маршруты не будут совпадать.
```php
$crawler = $client->request
('GET',
 '/',
  [],
  [], 
  ['HTTP_HOST' => 'm.example.com']
  // или получить значение из какого-то параметра контейнера
  // ['HTTP_HOST' => 'm' . $client->getContainer()->getParameter('dmomain')]
  );
```

----
### Локализованные маршруты
Если приложение переводится на несколько языков, каждый маршрут может определять другой URL по каждой локали перевода.

```php
#[Route(path: [
	'en' => '/about-us',
	'nl' => '/over-ons'
	], name: 'about_us'
])]
public function about(): Response
{
	//...
}
```

В `path` можно передать массив путей, соответствующим локалям.

В международных приложениях добавлять ко всем роутам префикс локали:
```yaml
controllers:
	resource: '../../Controller'
	type: annotation
	prefix:
		en: '' # Default locale
		nl: '/nl'
```

Также распространённое требование -  использовть другой хост, в зависимости от локали:
```yaml
contollers:
	resource: '../../src/Controller'
	type: attribute
	host:
		en: 'www.example.com'
		nl: 'www.example.nl'
```
----
### Маршруты без состояния
Иногда, HTTP-ответ должен быть кеширован. Важно убедиться, что это может произойти.
Однако, каждый раз когда начинается сессия во время запроса. Symfony превращает ответ в частный некешируемый.

Чтобы объявить что сессия не должна быть использована при сопоставлении с запросом:
```
#Route[(path: '/', name: 'home_page', stateless: true)]
```

Если сессия используется. то  приложение сообщит о ней, основываясь на параметре `kernel.debug`:
- `enabled` - вызовет исключение UnexpectedSessionUsageException
- `disabled` - запишет лог предупреждения

----
### Генерирование URL
Системы маршрутизации двусторонни:
	1. Ассоциируют URL с контроллерами
	2. Генерируют URL для заданного мршрута

Генерирование URL позволяет не писать `<a href="...">` вручную в HTML-шаблонах.

Чтобы сгенерировать URL нужно указать имя маршрута и значения параметров.

Если контроллер расширяется от AbstractController, то можно использовать метод `generateUrl()`.

```php
#[Route(path: '/blog', name: 'blog_list')]
public function list(): Response
{
	// URL без аргументов
	$signupPage = $this->generateUrl('sign_up');

	// URL с аргументами
	$userProfilePage = $this->generateUrl('user_profile', ['username' => $user->getUserIdentidier()]);

	// генерация абсолютного URL
	$signupPage = $this->generateUrl('sigh_up', [], UrlGeneratorInterface::ABSOLUTE_URL)

	// генерация URL с локалью
	$signUpPageInDutch = $this->generateurl('sign_up', ['_locale' => 'nl'])
}
```

`Абсолютный URL` -> `ABSOLUTE_URL` -> URL, который содержит полный путь, начиная от протокола http(s).

`Относительный URL` -> `RELATIVE_PATH` -> содержит только часть пути и его интерпретация зависит от контекста.

<!> При генерации URL нельзя передавать в параметры объект. Нужно привести их в строку.
```php
$url = $this->generateUrl('user_profile', ['id' => $user->id->toRfc4122()]);
```
----
### Генерирование URL в сервисах
Есть сервис `router` в Symfony, который использует метод `generate()`.
При использовании автомонтирования сервисов, вам понадобится только добавить аргумент в конструктор сервис и типизировать его классом `UrlGeneratorInterface`.

```php
class SomeService
{
	public function __construct(
		private UrlGeneratorInteface $router
	) {}

	public function someMethod(): void
	{
		//...
		$signupPage = $this->router->generate('sign_up');
		$userProfilePage = $this->router->generate('user_profile', [
		'username' => $user->getUserIdentifier()]);

		// ... И так далее
	}
}
```
----
### Генерация URL в шаблонах:
Тут что-то будет, но позже.

----
### Генерация URL в командах
Работает также как и в сервисах. Исключение только что команды не выполняются в HTTP-контексте. Следовательно, генерация `ABSOLUTE_URL` - сформирует `http://localhost/` вместо реального URL.

Решением будет сконфигурировать `default_url`, чтобы определить контекст запроса.
```yaml
# config/packages/routing.yaml
framework:
	router:
		#...
		default_uri: 'https://example.org/my/path'
```

Внутри комманды:
```php
class SomeCommand extends Command
{
	public function __construct(private UrlGeneratorInterface $router)
	{
	}

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$signupPage = $this->router->generate('sign_up');

		$userProfilePage = $this->router->generate('user_profile', ['user' => $user->getIdentifier()]);

	// ... И так далее...
	}
}
```
----
### Проверка существования роута
```php
try {
$url = $this->router->generate($routeName, $params)
} catch (RouteNotFoundException $e) {
	// маршрут не определён...
}
```
----
### Форсирование HTTPS в сгенерированных URL
Сгенерированные URL использует ту же схему, что и ткущий запрос.
URL использую http по умолчанию.
```yaml
paramters:
	router.request_context.scheme: 'https'
	asset.request_context.secure: true
```

В контроллере:
```php
#[(Route(path: '/login', name: 'login', schemes: ['https']))]
public function login(): Response
{
	// ...
}
```

URL, сгенерированный для login всегда будет использовать HTTPS.

----
### Подписание URI
`Подписанный URI` - URI, содержащий хэш-значение, которое зависит от содержания URI.

URI - строка, которая однозначно идентифицирует ресурс. Он может указать на расположение ресурса, но это не обязательно.

Пример URI: `urn:isbn:0-395-36341-1`

URL - указывает на местоположение ресурса в сети.

Можно проверить целостность подписанного URI, повторно вычислив его хэш-значение и сравнив его с хэшем, включенным в URI.

В Symfony есть утилита для подписани URI: `UriSinger`, который можно использовать в сервисе или контроллере:
```php
class SomeService
{
	public function __construct(private UriSinger $uriSinger) {}

	public function someMethod(): void
	{
		$url = 'https://example.com/foo/bar?sort=desc';
		// добавляет параметр запроса под названием '_hash'
		$signedUrl = $this->uriSinger->sign($url); 
		// проверить подлинность URL
		$urlSignatureIsValid = $this->uriSinger->check($signedUrl);
		// если есть доступ к объекту request, то можно использовать следующий метод
		$uriSignatureIsValid = $this->uriSinger->checkRequest($request);
	}
}
```
Отличная практика - добавлять к подписи ограниченное время жизни.
```php
$url = 'https://example.com/foo/bar?sort=desc';

// Дата окончания подписи
$signedUrl = $this->uriSigner->sign($url, new DateTimeImmutable('2025-01-01'));

// Можно дать интервал для указания жизни подписи
$signedUrl = $this->uriSinger->sign($url, new DateTimeInterval('PT10S'));

// Или передать временную метку в секундах
$signedUrl = $this->uriSinger->sign($url, 4070908800);
```
----
# Контроллер
Контроллер - созданная php функция, которая смотрит на объект Request, создаёт и возвращает объект Response.
Ответ может быть JSON, XML,  HTML-страницей, файлом, редиректом, ошибкой 404 и так далее)

----
### Простой контроллер
```php
class Controller
{

#[Route(path: '/', name: 'main_page')]
public function __invoke(Request $request): Response
{
	return new Response('<html><body><h1>Page</h1></body></html>');
}
}
```

ivnoke в данном случае представляет Controller

Атрибут `#[Route(path: '/')]` - добавляет роут для класса (метода).

----
### Базовый класс контроллера и сервисы
В Symfony есть `AbstractController`, его можно использовать для получения доступа к дополнительным методам-помощникам.

----
#### Перенаправление
Перенаправить пользователя на другую страницу, используя методы:
```php
public function __invoke(): RedirectResponse
{
	return $this->redirectToRoute('homepage');

	return $this->redirectToRoute('homepage', [], 301);

	// перенаправление по пути с параметрами
	return $this->redirectToRoute('app_lucky_number', ['max' => 10]);

	// перенаправление с сохранением изначальных параметров запроса
	return $this->redirectToRoute('blog_show', $request->query->all());

	// перенаправляет на внешний сайт, НЕ ПРОВЕРЯЕТ МЕСТО НАЗНАЧЕНИЯ
	return $this->redirect('http://symfony.com/doc');
}
```
----
#### Отображение шаблонов
Метод `render()` отображает twig шаблон и помещает его в содержимое в Response.
```php
return $this->render('lucky/number.html.twig', ['number' => $number])
```
----
### Получение сервисов
В Symfony есть куча полезных объектов-сервисов для облегчения работы: шаблонищатор, отправка почты, запрос к БД и др.
```php
#[Route(path: '/', name: 'main_page')]
public function main(int $max, LoggerInterface $logger): Response
{
	$logger->info('Logging!');
}
```

<!> Вот тут странно, ведь сервисы лучше прокидывать через конструктор.

Чтобы узнать какие сервисы есть в Symfony:
```terminal
bin/console debug:autowiring
```

Если нужен контроль над точным значение аргумента, можно использовать `#[Autowire]`:
```php
public function number(int $max, #[Autowire(service: 'monolog.logger.request')] LoggerInterface $logger, #[Autowire('%kernel.project.dir%')] string $projectDir): Response
{
	//...
}
```

`#[Autowire]` - используется для получение (injection) данных об аргументе из контейнера. Используется в конструкторах, сеттерах и других методах, где требуется автоматические внедрение зависимостей.

Можно указать какой сервис мы используем, или оставить как есть и symfony сможет найти необходимый сервис.

----
### Генерирование контроллеров
Можно использовать Symfony Maker (необходимо устаноить), чтобы быстро генерировать контроллеры.
```terminal
bin/console make:controller BrandnewController
```
Можно сделать crud:
```
bin/console make:crud Product // Product - entity
```
----
Управление ошибками и страницами 404
```php
public function index(): Response
{
	$product = $this->EntityRepository->find();
	
	if (!$product) {
	throw $this->createNotFoundException("Product doesn't exist.");
	}

	//...
}
```

`createNotFoundException()` - сокращение для создания `NotFoundException()`, который вызовет 404 внутри Symfony.

```php
// Сгенерирует ошибку с кодом 500
throw new Exception();
```
----
### Объект запроса в качестве аргумента контроллера
В случае, если нам нужно получить как-то данные из запроса, например заголовок запроса или получить данные к файлу - всё то хранится в `Request` запроса.

Просто добавляем Request в качестве аргумента к контроллеру.
```php
#[Route(path: '/', name: 'main_page')]
public function __invoke(Request $request): Response
{
	$page = $request->query->get('page', 1);
	
	//...
}
```
----
### Автоматическое сопоставление запроса
Можно автоматические сопоставить полезную нагрузку запроса и/или параметры запроса с аргументами действий контроллера с помощью атрибутов.

<!> Шо бля?

----
#### Индивидуальное сопоставление параметров запроса
Представь, что пользователь дёргает роут по ссылке: https://example.com/dashboard?firstName=John&lastName=Smith&age=27
Можно достать параметры из квери, путём `MapQueryParameter`:
```php
public function __invoke(
#[MapQueryParameter] string $firstName,
#[MapQueryParameter] string $lastName,
#[MapQueryParameter] int $age,
): Response
{
	//...
}
```

`MapQueryParameter` может принимать необязательный аргумент под названием `filter`. Можно валидировать фильтр путётм константы `Validate Filters`:
```php
public function __invoke(
#[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\w+$/'])] string $firstName,
#[MapQueryParameter] string $lastName,
#[MapQueryParameter(filter: \FILTER_VALIDATE_INT)] int $age
): Response
{
	///...
}
```
----
#### Сопоставление всей строки запроса
Можно отобразить всю строку запроса в объект DTO для валидации:
```php
class UserDto
{
public function __construct(
#[Assert\NotBlank]
public string $firstName,

#[Assert\NotBlank]
public string $lastName,

#[Assert\GreaterThan(18)]
public int $age,
) {}
}
```

И в контроллере можно использовать `MapQueryString`:
```php
public function __invoke(
#[MapQueryString]
UserDto $user
): Response
{
	//...
}
```

Можно настроить группы валидации и HTTP-статус при неудачной валидации:
```php

public function __invoke(
#[MapQueryString(
validationGroups: ['strict', 'edit'],
validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY
)] UserDto $userDto
)
```

Код по умолчанию при неудачной проверке - 404

----
`MapQueryString` - Берёт строку запроса целиком и формирует её в массив.

Если строка запроса name=John&age=30&city=New%20York,  то MapQueryString создаст массив ['name' => 'John', 'age' => '30', 'city' => 'New York'].

`MapQueryParameter` - Берёт параметры запрос по-отдельности.

----
#### Сопоставление полезной нагрузки запроса (Payload)
В запросы типа PUT и POST данные попадают не через query, а через Payload (полезная нагрузка) в виде json:
```json
{
"firstName": "John",
"lastName": "Smith",
"age": 29
}
```

Для этого можно использовать `MapRequestPayload`:
```php
public function __invoke(
#[MapRequestPayload]
UserDto $userDto
): Response
{
	//...
}
```

Можно настроить контекст сериализации:
```php
public function __invoke(
#[MapRequestPayload(
serializationContext: ['...'], resolver: AppResolverUserDtoResolver
)] UserDto $userDto
)
{
	//...
}
```

Также для Payload можно настроить группы валидации статус-код ошибки при неверной валидации:
```php
public function __invoke(
#[MapRequestPayload(
acceptFormat: 'json',
validationGroups: ['strict', 'read'],
validationFailedStatusCode: Response::HTTP_NOT_FOUND
)] UserDto $userDto
): Response
{
	//...
}
```
----
### Управление сессией
В сессии пользователя можно хранить специальные сообщения, так называемые flash-сообщения. По совсему дизайны эти сообщения предназначены для однократного использования:
Они исчезают из сессии автоматически как только их извлекают.
Потому flash-сообщения удобны для хранения пользовательских уведомлений.

Например при обработки отправки формы:
```php
public function submit(Request $request):Response
{
	if ($form->isSubmitted() && $form->isValid) {
		//обработка формы

		$this->addFlash('notice', 'Your form submited');
		// эквивалентно $request->getSession()->getFlashBag()->add()

		return $this->redirectToRoute(/* ... */);
	}

	return $this->render(/* ... */);
}
```

`FlashBag` - часть сессии Symfony, которая хранит временные сообщения, которые отображаются лишь раз после перенаправления.

`'notice'` - ключ или имя для сообщения. Используется для идентификации типа сообщения.

<!> Походу используется для twig шаблонов, чтобы давать обратную связь на действия пользователя.

----
### Объект Request и Response
Symfony будет отдавать `Request` любому аргументу контроллера, где он указан.
```php
public function index(Request $request): Response
{
	$request->isXmlHttpRequest(); // является ли request ajax-овским
	$request->getPreferedLanguage(['en', 'fr']);

	$request->query->get('page'); // извлекает данные из query
	$request->request->get('page'); // излвлекает данные из payload

	$request->server->get('HTTP_HOST'); //извлекает переменные SERVE

	$request->files->get('foo'); // извлекает UploadedFile

	$request->cookie->get('PHPSESSID'); // извлекает значение cookie

	$request->headers->get('host'); // чтение заголовка запроса
}
```

Класс Request имеет много встроенных свойств и методов, которые возвращают необходимую информацию при запросе.

`Response` тоже имеет публичное свойство `headers`. Этот объект имеет тип ResponseHeaderBag - предоставляет методы для получение и установки заголовка ответа. Имена заголовков нормализированы.
Имя `Content-Type` эквивалентно `content-type` или `content_type`.

В Symfony контроллер должен возвращать Response.
```php
// простой респонс
$response = new Response('Hello'.$name, Response::HTTP_OK);

//респонс с заголовком и статус кодом 200
$response = new Response('<style> ... </style>');
$response->headers->set('Content-Type', 'text/css');
```
----
#### Доступ к значениям конфигурации
Чтобы получить значение любого параметра конфигурации из контроллера, используйте метода-помощник `getParameter()`:
```php
$contents = $this->getParameter('kernel.project_dir')./'contents';
```

`getParameter()` предоставляет доступ к параметрам окружения, доступ к настройкам БД, доступ к путям.

Работает только внутри контейнера.

----
#### Возвращение JSON ответа
Если нужно вернуть JSON из контроллера, то нужно указать вовзрвщаемый тип JsonResponse и использовать метод-помощник `json()`.
```php
public function __invoke(Request $reuqest): JsonResponse
{
	// также устанавливает нужный content-type
	retrun $this->json(['username' => 'jane.doe']);

	// определяет три опциональных аргумента
	return $this->json($data, $headers = [], $context = []);
}
```

Если подключён сервис сериализации - она будет возвращать данные json. В противном случае придётся использовать метод `json_encode`.

----
#### Потоковые ответы файлов
Можем использовать метод-помощник `file()`, чтобы обслуживать файл внутри контроллера.
```php
public function download(): BinaryFileResponse
{
	return $this->file('/path/to/some_file.pdf');
}
```

У `file()` есть аргументы для конфигурации поведения:
```php
public function download(): BinaryFileResponse
{
	$file = new File('/path/to/some_file.pdf');

	// можно переименовать файл
	return $this->file($file, 'new_name.pdf');

	// открывает файл в браузере вместо того чтобы скачивать его
	return $this->file('invoice_3241.pdf', 'my_invoice.pdf', ResponseHeaderBag::DISPOSITION_INLINE);
}
```
----
#### Отправка ранних подсказок
Ранние подсказки указывают браузеру на необходимость начать загрузку некоторых ресурсов ещё до того, как приложение отправляет содержание ответа.
Повышает производительность, потому что браузер может предварительно получать ресурсы, которые потребуются для отправки полного ответа.

Ранние подсказки можно отправлять с помощью `sendEarlyHints()`:
```php
#[Route(path: '/', name: 'main_page')]
public function __invoke(Request $request): Response
{
	$response = $this->sendEarlyHints([
	new Link(rel: 'preconnect', href: 'https://fonts.gogle.com'),
	new Link(href: 'style.css')->withAttribute('as', 'stylesheet'),
	new Link(href: '/script.js')->withAttribute('as', 'script'),
	]);

	// подготовить содержание ответа
	return $this->render('homepage/index.html.twig', response: $response);
}
```

Технические - ранние подсказки это информационный HTTP-ответ со статус-кодом 103. Метод `sendEarlyHints()` также возвращает объект `Response` с этим статус-кодом и немедленно отправляет его заголовки.

Таки образом браузер может заранее начать загрузку ресурсов.

----
## Расширение разрешения аргумента действия
ArgumentResolver распознаёт Request. Можно расширить функциональность работы с аргументами через ValueResolverInterface, создав на основе его класс resolver.

В Kernel есть следующие функциональности:
- `RequestAttributeValueResolver` - пытается найти атрибут запрос, совпадающий с именем аргумента
- `RequestValueResolver` - внедряет текущий Request при типизации Request, или классе, расширяющем Request
- `ServiceValueResolver` - внедряет сервис при типизации валидным классом сервиса или интерфейсом. Работает как автомонтирование.
- `SessionValueResolver` - внедняет сконфигурированный класс сессии расширяющий SessionInterface при типизации SessionInterface или классом, расширяющим SessionInterface
- `DefaultValueResolver` - при наличии, установить значение аргумента по умолчанию, если аргумент необязательный
- `VariadicValueResolver` - верифицирует является ли данные запроса массивом. И добавит их в список аргументов. Последний аргумент будет содержать все значения этого массива.
----
# Конфигурация Symfony
Приложение настраивается с помощью файлов конфига в папке /config:
- `routes.yaml `- определяет конфиругацию маршрутизации
- `services.yaml` - настраивает службы контейнера служб
- `bundles.php` - включает/отключает пакет в вашем приложении

В папке config/packages - хранятся конфигурация каждого пакета, установленного в вашем приложении

Пакеты - добавляют готовые к использованию функции в проект.

При использовании `Symfony Flex` данные в bundles и config/packages обновляются во время установки пакета.

----
### Форматы и конфигурации
Symfony позволяет выбирать формат конфигурации из yaml, xml, php.

- YAML -  Не для всех IDE, простой и понятный
- XML - Излишне многословная конфигурация, но подходит почти для всех IDE
- PHP - позволяет создать динамическую конфигурацию с помозью массивов или ConfigBuilder
----
#### Импорт файлов конфигурации
Symfony загружает файлы конфигурации с помощью компонента Config, который предоставляет расширенные функции, такие как импорт других файлов конфигурации, даже если он использует другой формат:
```php
return static function (ContainerConfigurator $container): void {
	$container->import('legacy_config.php');

	// можно импортировать несколько файлов из папки
	$container->import('/etc/myapp/*.yaml');

	// третий аргумент говорит о игнорировании ошибок 'ignore_errors'
	// not-found молча отбрасывает ошибки, если файл не был найден
	$container->import('my_config_file.php', null, 'not_found');

	// true молча отбрасывает все ошибки, включая неверный код и 'not found'
	$containder->import('my_config_file.php', null, true);
}
```
----
#### Параметры конфигурации
Иногда одно и тоже значение конфигурации используется в нескольких файлах конфигурации.
Вместо того чтобы повторять его, его можно определить как `параметр`, что похоже на повторно используемое значение конфигурации.
`parameters` ключ определяет параметры в конфиге services.
```php
#services.php
return static function (ContainerConfigurator $container):void {
	$container->parameters()->set('app.admin_mail', 'something@example.com')

	->set('app.enable_v2_protocol', true)

	->set('app.supported_locales', ['en', 'es', 'fr'])

	->set('app.some_parameters', 'This is a bell char: \x07')

	->set('app.some_constant', GLOBAL_CONSTANT)

	->set('app.another_constant', BlogPost::MAX_ITEMS)

	->set('app.some_enum', PostState::Published);
};
```

После определения этих параметров на них можно ссылаться из любого другого файла:
```php
#some_package.php
return static function (Container Configuration $container):void {
	$container->extension('some_package', [
	// такой вариант
	'email_address' => param('app.admin_email'), 
	// или такой, и symfony найдёт параметр и заменит
	'email_address' => '%app.admin_email%'])
};
```

Если в значении какого-либо параметра есть `%` - тогда эту строку нужно экранировать другим `%`, чтобы synfony не считал его ссылкой на имя параметра.
```php
return static function (ContainerConfigurator $container):void
{
	$container->parameters()->set('url_pattern', 'http://symfony.com/?foo=%%s&amp;bar=%%d');
};
```

Параметры не помогут построить динамические пути в импортах.
`$container->import('&kernel.project_dir%/somefile.yaml')` - НЕ БУДЕТ РАБОТАЦ

<!> По соглашению: имена параметров, которые начинаются с `.` (`.mailer.-transport`) - доступны только во время компиляции контейнера.

Можно проверить что параметр не является пустым:
```php
// если параметр будет null, '' или [] - выдаст ошибку
$container->parameterCannotBeEmpty('app.private_key', 'Забыл установить значение для private_key?')
```
----
#### Конфигурационные среды
Если необходимо чтобы приложение в разное время вело себя по-разному:
- Регистрировать и предоставлять удобные инструменты отладки при разработке
- После развёртывания в рабочей среде приложение было оптимизировано и регистрировало только ошибки.

Файл в config/packages - используется для настройки служб приложения, можно менять конфигруацию в зависимости от окружения (например).

Есть три среды:
- `dev` - для местного етстирования
- `test` - для автотестов
- `prod` - для клиента

При запуске приложения Symfony запускает файлы в следующем порядке:
1. Файлы в `config/packages/*.<extension>`
2.  Файлы в `config/packages/<env_name>/*.<>extension
3. `config/services.<extension>`
4. `config/services_<env_name>.<extension>`

Окружения имеют большую базу общей конфигурации, которая помещается в файлы непосредственно в `config/packages/` каталоге.

<!> Также можно определить все настройки окружения В ОДНОМ файле конфигурации:
```php
run static function(ContainerConfigurator $container, WebpackEncoreConfig $webpackEncore): void
{
	$webpackEncore->outputPath('%kernel.project_dir%/public/build')
	->strictMode(true)
	->cache(false);

	if ('prod' === $container->env()) {
		$webpackEncore->cache(true);
	}

	if('test' === $container->env()) {
		$webpackEncore->strictMode(false);
	}
};
```

<!> О порядке загрузки файтлов конфигурации можно узнать при изучении `configureContainer()`.

----
#### Выбор активной среды
Приложения Symfony поставляются с файлом `.env`, который находится в корневом каталоге проекта.
В файле `.env` или `.env.local` (предпочтительнее) чтобы запустить приложение в проде:
```env
APP_ENV=prod
```

Это значение используется как и для веб, так и для консольных команд. Но его можно также указать перед запуском команды:
```terminal
APP_ENV=prod php bin/console <command_name>
```
----
#### Создание нового окружения
Кроме стандартных окружений, можно также определить кастомные. Как создать свою среду:
1. Создать каталог конфигурации с тем же именем, что и среда (`config/packages/staging`).
2. Добавить необходимые файлы конфигурации в папку окружения (`config/packages/staging`), чтобы определить поведение новой среды. Symfony сначала загружает файлы, поэтому вам нужно только настроить различия в этих файлах.
3. Выбрать `staging` окружение с помощью `.env` файла: `APP_ENV=staging`

<!> Среды часто похожи, можно использовать символьные ссылки между `config/packages/<environment-name>` каталогами для повторного использования одной и той же конфигурации.


----
### Конфигурация на основе переменных окружения
Использование env vars является обычной практикой для:
- Настройки параметров, которые за висят от того, где запущено приложение
- Настройки параметров, которые могут динамически изменяться на проде

В других случаев можно просто использовать параметры конфигурации.

Синтаксис переменных окружения: `%env(ENV_VAR_NAME)%`

```php
return static function(ContainerConfigurator $container):void {
	$container->extenstion('framework', ['secret' => '%env(APP_SECRET)$']);
	// именя env vars всегда в верхнем регистре
}
```

Доступ к этим переменным можно получить через `$_ENV` и `$_SERVER`:
```php
$dataBaseUrl = $_ENV['DATABASE_URL'];
$env = $_SERVER['APP_ENV'];
```

Значение env vars может быть только `string`, но Symfony включает в себя некоторые процессоры env vars для преобразования их содержимого (например строки в число).

Как определить значение env vars? Варианты:
- Добавить значение в файл .env
- Зашифровать значение в секрет
- Установить значение как реальную переменную среды в нашей оболочке или на веб-сервере

<!> Если приложение попытается использовать env var, которая не определена, то получим исключение.