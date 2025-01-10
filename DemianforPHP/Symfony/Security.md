Symfony предоставляет инструменты защиты приложения в SecurityBundle: защищенные куки сеансов, CSRF.

Установка SecurityBundle:
```terminal
composer require symfony/security-bundle
```

Если установлен symfony-flex, то создастся файл конфигуациии security.yaml:
```yaml
#secrurity.yaml

security:
	password_hashers:
	Symfony\Component\Security\Core\User\PasswordAuthenticatedInterface:'auto'

	providers:
		use_in_memory: { memory: null }
	firewalls:
		dev: 
			pattern: ^/(_(profiler|wdt)|css|images|js)/
			security: false
		main:
			lazy: true
			provider: users_in_memory
	
	access_control:
	# - { path: ^/admin, roles: ROLE_ADMIN }
	# - { path: ^/user, roles: ROLE_USER }
```

- `password_hashers` - то, как хэшировать пароли, в данном случае Symfony сам подберёт автоматически вариант
- `providers` - откуда брать информацию о пользователе (провайдеры пользователей) - `{ memory: null }` - говорит об отсутствии пользователей в памяти.
- `firewalls` - определяет брендмауеры firewall, защищающие различные части приложения
- `pattern: ^/(_(profiler|wdt)|css|images|js)/` - regexp - какие адреса должны обрабатываться этим брендмауером, в данном случае ресурсы Symfony для обработки и статические файлы (css, изображения, javascript)
- `security:false` - отключает безопасность для данных адресов на dev окружении.
- `main` - главный брендамауер
- `lazy:true` - откладывается инициализацию брендмауера, пока он не понадобится
- `provider: users_in_memory` - для авторизации пользователя используется провайдер user_in_memory
- `access_control` - определяет правила контроля доступа, настраивая доступ к различным точка приложения через роуты `/admin` и `/user` .

----
### Пользователь
Разрешения в Symfony всегда связаны с объектом пользователя. Чтобы защитить часть приложения, необходимо сделать класс User, который реализует UserInterface.
```terminal
bin/console make:user
```
Помимо создания сущности пользователя, он такэже создаст конфиг секьюрити:
```php
return static function (SecurityConfig $security): void
{
	$security
	->provider('app_user_provider')
	->entity()
	->class(User:class)
	->property('id');
}
```
Это настройка позволяет как (пере)загружать пользователей из БД, на основе идентификатора. Конфигурация в примере использует Doctrine для загрузки User сущности, используя id как идентификатор.

Как это сделано у нас:
```php
private static function (ContainerConfigurator $container, SecurityConfig $security):void
{
	$services = $container->services(); // подключаем ServiceLocator, который используется для определения сервисов.
	$services->defaults()->autowire(); // Symfony будет автоматически создавать зависимости для сервисов, если они не указаны явно

	$services->set(BearerAuthenticator::class); // регистрируем сервис, Symfony автоматически создаст экземпляр класса и его зависимости. Используется для авторизации пользователя по токену Bearer.

	$services->set(AdminResolver::class)->tag('controller.argument_value_resolver', ['priority' => 1])->autowire(); // крепим сервис и помечем их текгом, что означает что резовлеры будут использоваться для аргументов в контроллере, приоритет=1 означает самый высокий 

	$services->set(UserResolver::class)->tag('controller.argument_value_resolver', ['proirity' => 1])->autowire();

	switch($container->env()) { // условия, где в зависимости от окружения меняется формат хэширования пароля
		case: 'test':
			$security
			->passwordHasher(Credential::class)
			->algorithm('plaintext'); // если окружение = test, то алгоримт простой текст

			break;

		default:
			$security
			->passwordhasher(Credential::class)
			->algorith('bcypt')
			->cost(10); // если любое другое окружение, то адгоритм bcypt, cost это сложность вычисления хэша

	$security
	->firewall('main')
	->lazy(true)
	->pattern('^/')
	->customAuthenticators([BearerAuthenticator::class]); // установка настройки firewall с именем main с отложенным инициализированием, где ко всем ссылкам применяется брендмауер с кастомной аутентификацией.
	}
}
```
----
### Регистрация пользователя - хэширование пароля
Многие системы требуют, чтобы пользователь входил в систему с паролем. Для этого используется функциональность хэширования и проверки пароля.
В Symfony есть PasswordAuthenticatedUserIntefrace, который должен расширять класс User.

Затем настроить какой хёш-код пароля должен использоваться для этого класса.
```php
return static function(SecurityConfig $security): void
{
	$security
	->passwordHasher(PasswordAuthenticatedUserInterface::class)
	->algorithm('auto');
}
```
Далее использовать UserPasswordHasherInterface для хэширования пароля перед сохранением в БД.
```php
class RegistrationController extends AbstractController
{
	public function __construct(private UserPasswordHasherInterface $hasher) {}

	public function __invoke(Request $request): Response
		{
		$data = json_decode($request->getContent(), true);

		$user = new User();

		$user->name = $data['name'];
		$user->email = $data['email'];

		$hashedPassword = $this->hasher->hashPassword($data['password']);

		$user->password = $hashedPassword;

		$this->entityManager->flush()

		return new Response();
		}
}
```

<!> Команда make:registration-form создаст контроллер регистрации и добавить функцию проверки email:
```terminal
composer require symfonycasts/verify-email-bundle
bin/console make:registration-form
```

Можно вручную хэшировать пароль через команду в терминале:
```terminal
bin/console security:hash-password
```
----
### Брендмауэр
Определяет какие части приложения защищены и как проходить аутентификацию (форма входа, api-токен).
Только один брендауэр активен для каждого запроса. В security.yaml обычно указывается паттерн ссылок, на которых распространяется правило брендмауэра. Отсутствие `pattern` настройки в конфиге означает что брендмауэр применяется ко всем роутам.

`dev` брендмаудэр - поддельный брендмауэр: гарантирует что случайно не будем заблокирован инструмент разработки Symfony, которые находятся по url-адресам типа `/_profiler` и `/_wdt`.

<!> Можно в паттерн добавлять массив более простых регулярных выражений:
```php
$security->firewall('main')->pattern(['^/_profiler', '^/_wdt', '^/css'])->security(false);
```
Брендмауэр может иметь много режимов аутентифиуации, то есть задать вопрос "Кто вы?" разными способами.

<!> Анонимный lazy режим предотвращает запуск сеанса, если не требуется авторизация. Это важно для сохранения кэшируемости запросов.

[x] Если не видно панель инструментов, нужно установить профайлер:
```terminal
composer require --dev symfony/profiler-pack
```
----
#### Получение конфигурации брендмауэра для запроса
Если нужно получить конфигурацию брендмауера, соответствующую запросу - можно использовать $security службой:
```php
class GetSecurity extends AbstractController
{
	public function __construct(
	private Security $security
	) {}

	public function __invoke(Request $request): Response
	{
	$security = $this->security->getFirewallConfig($request)?->getName();

	// ...
	}
}
```
----
#### Аутентификация пользователей
Аутентификация пытается найти соответствующего пользователя для посетителя веб-страницы. Традиционно это делалось с помощью формы входа или базового HTTP в браузере. В SecurityBundle поставляется несколько встроенных аутентификаторов:
- Форма входа
- JSON-логин
- HTTP-базовый
- Ссылка для входа
- Клиентские сертификаты X.509
- Удалённые пользователи
- Пользовательские аутентификаторы

----
##### Форма входа
Большинство веб-сайтов имеют форму входа где пользователь проходит аутентификацию с помощью идентификатора.
Это функциональность обеспечена встроенным `FormLoginAuthentificator`.

Вы можете выполнить следующую команду, чтобы создать форму входа:
```terminal
bin/console make:security:form-login
```

Это команда создаст контроллер и шаблон, а также обновит конфигурацию безопасности.
Можно также сделать это вручную:
```terminal
bin/console make:controller Login
```
Получаем:
```php
class LoginController extends AbstractController
{
	#[Route(path: '/login', name: 'login')]
	public function index(): Response
	{
		return $this->render('login/index.html.twig', ['controller_name' => 'LoginController']);
	}
}
```
Потом добавить контроллер в конфиг:
```php

return static function (SecurityConfig $security):void
{
	$mainFirewall = $security->fireWall('main');
	$mainFirewall->formLogin()->loginPath('login')->checkPath('login');
}
```
`loginPath` и `checkPath` поддерживают имена и роуты.

[x] - При отсутствии авторизации будет отправлять пользователей на `loginPath`.

Отредактировать контроллер входа, чтобы отобразить форму входа.
```php
class Login extends AbstractController
{
	public function __construct(AuthentificationUtils $auth) {}

	public function __invoke(): Response
	{
		$error = $this->auth->getLastAuthenticationError();

		$lastUsername $this->auth->getLastUsername();

		return $this->render('/login/index.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
	}
}
```
Обновить шаблон:
```html
{# templates/login/index.html.twig #}
{% extends 'base.html.twig' %}

{# ... #}

{% block. body %}
	{% if error %}
		<div>{{error.essageKey|trans(error.messageData, 'security')}}</div>
	{% endif %}

	<form action="{{ path('login') }}" method="post">
		<label for="username">Email:</label>
		<input type="text" id="username" name="_username" value="{{ last_username }}" required>
		<label for="password">Password:</label>
		<input type="text" id="password" name="_password" required>
		<button type="submit">login</button>
	</form>
{% endblock %}
```

<!> Переменная, переданная в шаблон является `AuthenticationException`, она может содержать конфиденциальную информацию об ошибке аутентификации. Потому никогда нельзя использовать error.message а использовать messageKey.

Форма может выглядеть как угодно, но она должна соответствовать следующим соглашениям:
- `<form>` отправляет запрос POST на метод `login`, потому что он был настроен в checkPath в security.yaml.
- Поля имени содержит имя "_username", а пароль "_password".

[x] Данная форма входа не защищена от атак CSRF. Нужно защитить форму входа.

Весь процесс аутентификации:
1. Пользователь пытается получить доступ к защищённому ресурсу (например к `/admin`)
2. Брендмауер инициирует процесс аутентификации, перенаправляя на форму входа (`/loing`)
3. Страница `/login` отображает форму входа через маршрут и контроллер, созданные в примере.
4. Пользователь отправляет форму входа в систему `/login`
5. Система безопасности (`FormAuthentiticator`) перехватывает запрос, проверяет предотавленные пользователем учётные данные, аутентифицирует пользователя, если они верны. И отправляет обратно на форму логина, если они неверны.

[x] Можно настроить ответы на успешную или неудачную попытку входа.

----
### Защита CSRF в формах входа
Для защиты CSRF в формах, нужно добавить технику скрытых токенов CSRF. Чтобы его использовать, нужно настроить некоторые параметры:

Включить CSRF в форме логина:
```php
#sceurity.php

return static function(SecurityConfig):void
{
	//...
	$mainFirewall = $secuirty->firewall('main');
	$mainFirewall->formLogin()->enableCsrf(true);
}
```
Использовать `csrf_token()` метод в шаблоне twig:
```html
#index.html.twig

{# ... #}
<form action="{{ path('login') }}" method="post">
	{# поля для логина #}
	<input type="hidden" name="_csrf_token" value="{{ csrf_token('authenticate')}}">
	<button type="submit">login</button>
</form>
```
Таким образом мы защитим форму от CSRF атак.

<!> Можно изменить имя поля, установив csrf_parameter и изменить идентификатор токена, установив csrf_token_id в конфигурации.

----
### JSON-логин
Приложения предоставляют API, защищённый с помощью токенов. Приложения могут использовать ендпоинт, который предоставляет токен на основе имени пользователя и пароля.

Включить аутентификатор с помощью `json_login` настройки:
```php
#security.php
return static function (SecurityConfig $security):void {
	$mainFirewall = $security->firewall('main');
	$mainFirewall->jsonLogin()->checkPath('api_login');
	};
```

[x] Поддерживает checkPath - url адреса и имена маршрутов. Аутентификатор запускается, когда клиент запрашивает check_path.

Нужно сделать контроллер с этим путём
```php
class JsonLogin extends AbstractController
{
	#[Route(path: '/api/login/, name: 'api_login', methods: [Request::Method_POST])]
	public function __invoke(#[CurrentUser] ?User $user): Response
	{
		if (null === $user) {
			return $this->json([
			'message' => 'Missing credentials'
			], Response::HTTP_UNAUTHORIZED);
		}

		$token = // сделай токен сам

		return $this->json([
		'user' = $userName->getIdentifier,
		'token' => $token,
		])
	}
}
```

<!> Какая-то хуйня не рабочая, для работы JSON авторизации нужен свой аутентификатор.

<!> Токен можно сгенерировать через `LexikJWTAuthenticationBundle` например

----
### HTTP-Аутентификация
Это стандартизированный фреймворк HTTP-аутентификации. Он запрашивает учётные данные (имя пользователя, пароль) с помощью диалога в браузере, а HTTP basic authentificator Symfony проверяет эти данные.

   ```php
   return static function (SecurityConfig $secruity):void
   {
	   $mainFirewall = $security->firewall('main');
	   $mainFirewall->httpBasic()->realm('Secured Area');
   }
```

Каждый раз когда юзер заходит без авторизации, симфони его отправит на базовую HTTP-аутентификацию (Используя заголовок `WWW-Authenticate`). Аутентификатор проверяет данные и авторизирует пользователя.

[x] Нельзя просто так взять и выйти из системы, используя HTTP аутентификатор, браузер запомнит данные и будет их отправлять при каждом запросе.

----
#### Ссылка для входа
Это метод аутетинфикации без пароля. Пользователь получит кратковременную ссылку, которая его авторизует на сайте.

<!> ТУТ НУЖНО БОЛЬШЕ ИНФЫ

----
#### Токены доступа
Часто используются в контексте API: пользователь получает токен от сервера авторизации, который его авторизует.

<!> ТУТ НУЖНО БОЛЬШЕ ИНФЫ

----
#### Клиентские сертификаты X.509
При использовании клиентских сертификатов, сервер выполняет аутентификацию самостоятельно. Аутентификатор X.509 предоставляемый Symfony извлекат адрес почты из "различительного имени" клиентского сертификата.
Затем использует этот адрес в качестве идентификатора пользователя в поставщике пользователя.

I. Настрой веб-сервер
```nginx
server {
	# ...
	ssl_cleint_certificate /path/to/my-custom-CA.pem;

	# включить валидацию сертификатов
	ssl_verify_client optional;
	ssl_verify_depth 1;

	location / {
		# Передать SSL_CLIENT_S_DN в приложение
		fastcgi_param SSL_CLEINT_S_DN $ssl_client_s_dn
	}
}
```

II. Включить внутри конфига:
```php
return static function(SecurityConfig $config):void
{
	$mainFirewall = $secirty->firewall('main');
	$mainFirewall->x509->provider('your_user_provider');
}
```

По умолчанию Symfony извлекает данные из сертификата двумя разными способами:
1. Сначала пробудет `SSL_CLIENT_S_DN_Email` параметр сервера, который предоставляется (?) Apache?
2. Если не установлен (при использовании nginx) - используется `SSL_CLIENT_S_DN` и сопоставление значения следующее за `emailAddress`.

Можно настроить имя некоторых параметров под x509 ключом.

----
#### Удалённые пользователи
Помимо аутентификации клиенсткого сертификата, есть ещё модули веб-сервера, которые предварительно аутентифицируют пользователя (например kerberos). Удалённый аутентификатор пользователя обеспечивает базовую интеграцию для этих служб.

Эти модули асто выставляют аутентифицированного пользователя в `REMOTE_USER` переменной окружения и использует это значение в качестве идентификатора пользователя.

Включить удалённую аутентификацию пользователя можно с помощью `remote_user` ключа:
```php
return static function(SecurityConfig $security):void
{
	$mainFirewall = $security->firewall('main');
	$mainFirewall->remoteUser()->provider('your_user_provider')
}
```
----
### Ограничение попыток входа:
Тут всё просто, используем RateLimiter:
```terminal
composer require symfony/rate-limiter
```


```php
return static function(SecurityConfig $security):void
{
	$mainFirewall = $security->firewall('main');
	$mainFirewall->loginThrottling()->maxAttepts(3)->interval('15 minutes');
	// два последних опциональны
}
```

<!> Значение interval должно быть числом и значением времени после: day, hours, minutes, seconds.

RateLimiter держит в кэше симфони предыдущие попытки входа. Можно также организовать собственное хранилище.

<!> RateLimiter - не единственный метод для ограничения подбора пароля. Есть и другие методы защиты.

Если нужен более сложный алгоритм ограничения, то можно создать свой RateLimiter используя `RateLimiterInterface` и нужно задать лимитеру id сервиса.
```php
return static function(FrameworkConfig $framework, ContainerBuilder $container, SecurityConfig $security):void
{
	$frameWork->rateLimiter()->limiter('username_ip_login')->policy('token_bucket')->limit(5)->rate()->interval('5minutes');

	$framework->rateLimiter()->limiter('ip_login')->policy('sliding_window')->limit(50)->interval('15 minutes');

	$container->register('app.login_rate_limiter', MyRateLimiter::class)->setArguments([
	new Reference('limiter.ip_login'),
	new Reference('limiter.username_ip_login')
	param('kernel.secret')
	]);

	$security->firewall('main')->loginThrottling()->limiter('app.login_rate_limiter');
}
```
----
### Поведение при успешной и неудачной аутентификации
Чтобы настроить успешный или неудачный запрос аутентификации, можно задать пользовательские обработки успеха и неудачи, реализуя `AuthenticationSuccessHandlerInterface` или `AuthentificationFailureHanlderInterface`.

#### Войти программно
Можно авторизовать пользователя, используя `login()` метод:
```php
class SecurityController
{
	public function someAction(Security $security): Response
	{
		$user = ...

		// авторизация через текущий firewall
		$security->login($user);

		// если аутентификаторов несколько, то можно уточнить какой через имя
		$security->login($user, 'form_login');

		// или через идентификатор
		$security->login($user, ExampleAuthenticator::class);
		
		// или в другом фаерволле 
		$security->login($user, 'from_login', 'other_firewall');

		// с использованием беджей
		$security->login($user, 'form_login', 'other_firewall', [(new RememberMeBadge())->enable()], ['referer' => 'https://oauth.example.com']]);

		// с добавлением логики редиректа
		$redirectResponse = $security->login($user);
		return $redirectResponse;
	}
}
```

`бейдж` - особый идентификатор, которые цепляется к паспорту пользователя, это дополнительная запись даёт больше информации о пользователе или его роли.

#### Выход из системы
Для выхода из системы используется метод `logout()`.
```php
return static function (SecurityConfig $security):void {
$mainFirewall = $security->firewall('main');
$mainFirewall->logout()->path('/logout');
// при желании редиректа после разлогина написать ->target('<route>');
}
```

Symfony отменит авторизацию пользователей, перешелших по роуту и если настроен target, то направит его туда.

<!> При необходимости указать путь выхода из системы, можно использовать `_logout_<firewallname>` имя маршрута (например, `_logout_main`).

Если проект не использует Symfony Flex, то нужно убедиться что импортирован загрузчик маршрута выхода из системы в роуты:
```php
return static function (SecurityConfig $security):void
{
	$routes->import('security.route_loader.logout', 'service');
}
```

#### Выйти программно
Можно программно выйти из системы, используя `logout()` метод:
```php
class SecurityController
{
	public function someAction(Secuirty $security): Response
	{
	// разлогиниться в текущем фаерволле
		$response = $security->logout();

	// можно отключить csrf logout
		$response = $scurity->logout(false);
	}
}
```

Если пользователь вне firewall'а, то в таком случае он получит logicException.

`SCRF` - защита от атаки, когда вредоносный сайт использует "чужие" cookie чтобы отправлять авторизированные запросы для кражи данных/денег.
Защитой от такой атаки является `CSRF` токен. Скрытый токен, который отправляет сервер и проверяет в последующем совпадение токенов.
Если не совпадает - не пропустит.

#### Настройка выхода из системы
Если нужно внедрить дополнительную логику при разлогинивании сстемы или настроить что происходит при выходе, зарегистрируй `EventSubscriber`:
```php
class LogoutSubscriber implements EventSubscriberInterface
{
	public function __construct(private UrlGeneratorInterface $urlGenerator)
	{
	}

	public static function getSubscribedEvents(): array
	{
	return [LogoutEvent::class => 'logout']
	}

	public function onLogout(LogoutEvent $event):void
	{
		// оплучить токен сессии пользователя
		$token = $event->getToken();

		// получить текущий запрос
		$request = $event->getReuqest();

		// получить текущий ответ, если настроен другим listener'ом
		$response = $event->getReponse();

		// настроить кастомный редирект
		$response = new RedirectResponsse($this->urlGenerator->geterate('homepage'), RedirectResponse::HTTP_SEE_OTHER);
		$event->setResponse($response);
	}
}
```

#### Настройка роута для выхода из системы
Другой способ - настроить path как имя маршрута, на случай если хочется сделать динамический URI (например при работе с локалями).
```php
return function (RoutingConfigurator $routes): void {
	$routes->add('app_logout', [
	'en' => '/logout',
	'fr' => '/deconnexion',
	])
	->methods(['GET']);
};
```
Затем нужно передать имя маршрута в path. Например:
```php
return static function (SecurityConfig $security): void {
	$firewall = $security->fireWall('main');
	$firewWall->logout()->path('app_logout');
};
```

----
### Извлечение объекта пользователя
После аутентификации можно получить данные о пользователе через `getUser()` метод:
```php
class ProfileController extends AbstractController
{
	public function __invoke(): Response
	{
		// сперва нужно проверить что пользователь авторизован
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

		// возвращает объект "User"
		$user = $this->getUser();

		return new Response('Wll hi there ' . $user->getName());
	}
}
```
----
### Извлечение пользователя из сервиса
Если нужно получить авторизованного пользователя из сервиса, то можно воспользоваться Security:
```php
class ExampleService
{
	public function __construct(private Secuirty $security) {}

	public function someMethod():void
	{
		$user = $this->seciruty->getUser();

		//...
	}
}
```
----
### Извлечь пользователя из шаблона
В шаблонах используется команда `app.user`:
```twig
{% if is_granted('IS_AUTHENTICATED_FULLTY')%}
<p>Email: {{ app.user.email }}</p>
{% endif %}
```
----
### Контроль доступа (авторизация)
Необходимо настроить доступ пользователя к разным возможнотям и роутам приложения.
Процесс авторизации имеет 2 различные стороны:
1. При авторизации пользователь получит определённую роль (`ROLE_AMIN`).
2. Теперь для роута требуется идентификатор роли, например атрибут в роуте.

#### Роли
Когда пользователь входит в систему, Symfony вызывает `getRoles()` из вашего объекта `User`, чтобы определить роли пользователя.
Роли в `User` представляют собой массив. У каждого пользователя ест как минимум одна роль: ROLE_USER:

```php
class User
{
	#[ORM\Column(type:'json')]
	private array $roles = [];

	public function getRoles():array
	{
		$roles = $this->roles;
		$roles[] = 'ROLE_USER';

		return array_unique($roles);
	}
}
```

Можно самостоятельно определить какие роли получит автоматически пользователь. Единственное правило, что роль должна начинаться с префикса `ROLE_`.

Далее эти роли можно будет использовать для доступа к разным роутам приложения.

##### Иерархические роли
Вместо того чтобы назначать множество ролей, можно сделать насладеование ролей, создав иерархию:
```php
return static function (SecurityConfig $security): void
{
	$secuirty->roleHierarchy('ROLE_ADMIN',['ROLE_USER']);
	$secuirty->roleHierarchy('ROLE_SUPER_ADMIN', ['ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'])
}
```

[x] Для наследования ролей лучше не использовать `$user->gerRoles()`:
```php
// например в контроллере для проверки наличия роли в иерархии, getRoles ничего не знает об иерархии
	$hasAccess = in_array('ROLE_ADMIN', $user->getRoles());
```
----
#### Добавить код для запрета доступа
Есть два способа запрета доступа:
1. access_control в security.yaml позволяет защитить весь шаблон URL
2. В контроллере (`$this->denyAccessUnlessGranted('ROLE_ADMIN')`)

#### Защита шаблонов URL (access_control)
Самый простой способ защиты приложения - защитить шаблон URL в `security.yaml`. Например, чтобы запросить `ROLE_ADMIN` для всех URL, которые начинаются с `/admin`:
```php
return static function (SecurityConfig $security):void
{
	$mainFirewall = $security->firewall('main');

	// рекомендует роль админа для всех роутов /admin
	$security->accessControl()->path('^/admin')->roles(['ROLE_ADMIN']);

	// рекомендует 2 роли для всех роутов /admin
	$security->acessControl()->path('^/admin')->roles(['ROLE_ADMIN', 'IS_AUTHENTICATED_FULLY']);

	// роут может быть регулярным выражением, например post или comment
	$security->accessControl()->path('^/api/(post|comment)/\d+$')->roles(['ROLE_USER'];)
};
```

Можно определить количество URL-шаблонов сколько нужно, каждный из них является регулярным выражением, но только один будет соответствовать запросу: Symfony начинает с начала списка и останавливается при первом совпадении:
```php
return static function (SecurityConfig $security): void
{
	$security->accessControl()->path('^/admin/users')->roles(['ROLE_SUPER_ADMIN']);

	$security->accessControl()->path('^/admin')->roles(['ROLE_ADMIN']);
}
```

Добавление `^` - означают что будут учитываться пути, которые соответствуют началу шаблона.
Путь без `^`, такой как `/admin` может соответствовать `/admin/foo`.

Каждый accessControl может сопоставляться с IP адресом, именем хоста и HTTP методами.