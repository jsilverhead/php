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

! При генерации URL нельзя передавать в параметры объект. Нужно привести их в строку.
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
----