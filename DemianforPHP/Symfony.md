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

