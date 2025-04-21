Настройка обновление схемы БД через доктрину.
```xml
#config без миграций:
<configuration default="false" name="Setup clean DB" type="PhpLocalRunConfigurationType" factoryName="PHP Console" path="$PROJECT_DIR$/app/bin/console" scriptParameters="doctrine:schema:update --force --no-interaction --env=test">
```

----
Symfony предоставляет все инструменты, необходимые для использования БД в приложении благодаря Doctrine.

Doctrine поддерживает реляционные базы данных, так и noSQL.

----
### Установка Doctrine
```terminal
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle
```
----
### Настройка БД
Информация о подключении к БД находится в .env файле:
```env
DATABASEURL="postgresql://db_user:db_password@127.0.0.1:5432/db_name"
```

Если имя пользователя или пароль обладают спец-символами, то нужно будет закодировать их через RFC3986. Можно использовать urlencode. Но в таком случае нужно удалить `resolve:` префикс в docntrine.yaml, чтобы избежать ошибок.

Когда параметры подключения настроены, Doctrine может создать БД:
```terminal
bin/console doctrine:database:create
```

<!> Узнать больше о командах doctrine можно через команду:
```terminal
bin/console list doctrine
```
----
#### Создание класса сущности
```bash
bin/console make:entity <name>
```
Команда создаёт php class-сущность.

<!> Начиная с MakerBundle v1.57.0 можно создать сущность с Uuid вместо цифрового id:
```bash
bin/console make:entity --with-uuid
```

#### Создание схемы
Для быстрого теста сущностей и БД необходимо использовать:
```bash
php bin/console doctrine:schema:update --force
```

----
### Миграции
Для сохранения сущности в БД и создание под неё таблицу необходимо сделать миграцию.
Миграцию можно сделать, путём команды:
```bash
bin/console make:migration
```

<!> постфикс `--formatted` создаёт аккуратный файл миграции.

Команда создаёт SQL команду, чтобы залить её в БД необходимо запустить миграцию:
```terminal
bin/console doctrine:migrations:migrate
```

Эту команду следует запускать на проде, чтобы поддержить БД в актуальном состоянии.

----
### Миграция и добавление дополнительных полей
Можно добавить entity снова, если хочется добавить какие-то поля. Или же отредактировать класс сущности.
После этого нужно снова сделать:
```terminal
bin/console make:migration

bin/console doctrine:migrations:migrate
```

`DoctrineMigrationsBundle` - управляет версиями миграций.

<!> Можно добавить свойство вручную, но при этом сгенерировать сеттеры и геттеры через консоль:
```terminal
bin/console make:entity --regenerate
```
Если вношу какие-то изменения и хочу чтобы пересобрались методы, то добавить:
```terminal
bin/console make:entity --overwrite
```
----
### Сохранение объектов в БД
```terminal
bin/console make:controller ProductController
```

Внутри контроллера можно создать объект и сохранить его:
```php
class MakeProduct
{

public function __construct(private EntityManagerInterface $entityManager) {}


#[Route(path:'/product/make', name: 'make_product')]
public function __invoke(): Response 
{
	$product = new Product();
	$product->setName('Keyboard');
	$product->setPrice(1999);
	$product->setDescription('Ergonomic and stylish!');

	$this->entityManager->persist();
	$this->entityManager->flush();

	return new Response('Saved product with id: ' . $product->getId());
}
}
```

При переходи по роуту, мы создаём объект. Можно проверить его наличие через pgadmin, или через команду:
```terminal
bin/console dbal:run-sql 'SELECT * FROM product'
```

`Entity Manager` - отвечает за сохранение объектов в БД и за извлечение их из БД.

`persist()` - говорить `Doctrine` что необходимо отслеживать/управлять следующим объектом.

`flush()` - при вызове `Doctrine` просматривает все объекты, которым он управляет и определяет нужно ли их сохранить в БД. В случае примера он использует команду `INSERT` для ввода.

[x] Если flush не удалётся, то получаем исключение `ORMException`.

----
### Проверка объектов
Есть встроенный в Symfony валидатор объектов, который выполняет базовые задачи валидации.
Но для этого нужно настроить `auto_mapping`.

```php
class CreateProduct
{
public function __construct(private ValidatorInterface $validator) {}

#[Route(path: '/create/product/', name: 'create_product')]
public function __invoke(): Response
	{
		$product = new Product();

		// здесь мы обновляем данные о продукте

		$errors = $this->validator->validate($product);

		if (count($errors) > 0) {
			return new Response($errors, 400);
		}

		return new Response();
	}
}
```
----
#### Извлечение объектов из БД
Контроллер чтобы извлечь новый продукт:
```php
class GetProduct
{
	public function __construct(private EntityManagerInterface $entityManager) {}

	#[Route(path: '/get/product', name: 'get_product', methods: ['GET'])]
	public function __invoke(Reuqest $request): Response
	{
		$id = $request->query->get('id');

		if (null === $id) {
			throw new Exception('id required');
		}

		$product = $this->entityManager->getRepository(Product::class)->find($id);
	}

		if (null === $product) {
			throw new Exception('Product not found');
		}

	return new Response($product);
}
```

Также можно использовать репозиторий вместо EntityManager:
```php
class GetProduct
{
	public function __construct(private ProductRepository $repository) {}

	#[Rounte(path:'/product/get/{id}', name: 'get_product', methods: ['GET'])]
	public function __invoke(int $id): Response
	{
		$product = $this->repository->find($id);

		renurn new Response('Found this:' . $product->name);
	}
}
```

Используя репозиторий, у нас открывается кипа вспомогательных методов:
```php
$product = $this->repository->find($id);

#By Name
$product = $this->repository->findOneBy(['name' => 'keyboard']);
#By price
$product = $this->repository->findOneBy([
'name' => 'keyboard',
'price' => 1999
]);

#Multiple products by condition
$product = $this->repository->findOneBy([
'name' => 'keyboard',
'price' => 'ASC'
]);

#All products
$products = $this->repository->findAll();

```

Можно также добавить кастомные методы для сложных запросов. У нас мы вообще репозитории пишем вручную).

----
### Автоматическое извлечение объектов (EntityValueResolver)
Можно установить `EntityValueResolver` для автоматического выполнения запроса.
Где можно упростить запись до:
```php
class GetProductController extends AbstractController
{
	#[Route(path:'/get/product/{id}', name: 'get_product', methods: ['GET'])]
	public function show(Product $product) {
		//...
	}
}
```

<!> Можно отключить EntityValueResolver в `MapEntity`:
```php
public function show(
#[CurrentUser] 
#[MapEntity(disabled:true)])
User $user): Response
	{
	//...
	}
```
----
#### Извлекать автоматически
Если поля роута совпадают со свойствами сущности, то объекты извлекутся автоматически:
```php
// Найдёт по ID - Primary Key
#[Route(path:'/get/product/{id}')]
public function __invoke(Product $product): Response
{}

// Найдёт через findBy
#[Route(path:'/get/product/{slug}')]
public function __invoke(Product $product): Response
{}
```

Автоматическое извлечение работает в следующих ситуациях:
- Если есть {id} в методе.
- Если совпадают свойства объекта ({slug})

Такое поведение включено по умолчанию на всех контроллерах. Эту функцию можно ограничить. Нужно установить параметр `doctine.orm.controller_resolver.auto_mapping` на `false`;

Если параметр отключён, его можно включить в частных случаях путём:
```php
#[Route(
path:'/get/product/{slug}'
name: 'get_product'
)]
public function show(
#[MapEntity](mapping: ['slug' => 'slug'])
Product $product
): Response
{
	//...
}
```
----
#### Извлечение через выражение
Можно через expression language написать извлечение:
```php
#[Route(path:'posts/{product_id}')]
public function show(
#[MapEntity(expr: 'repository.find(product_id)')]
Product $product
): Response
{
	//...
}
```
В данном случае `product_id` заменит `id` в качестве первичного ключа.

Можно также через извлечение получать список сущностей:
```php
#[Route(path:'/list/{author_id}')]
public function show(
#[MapEntity(expr: 'repository.findBy({"author": author_id}, {}, 10)')]
iterable $posts
): Response
{
	//...
}
```

Можно использовать также со множеством аргументов:
```php
#[Route(path:'/get/{id}/comments/{comment_id}')]
public function(
#[MapEntity(expr: 'repository.find(comment_id)')]
Comment $comment
): Response
{
	//...
}
```

Также можно расширить запрос с помощью обращения к request:
```php
#[Route(path:'/product/{id}/comments/')]
public function show(
#[MapEntity(expr: 'repository.findOneBy({"product": id, {"createdAt": request.query.get("sort", "DESC")}))]
Comment $comment):Response
{
	//...
}
```
----
### Параметры MapEntity
Для управления поведением атрибута, доступен ряд опций для MapEntity:
- `id` - если доступна опция id, то узнаёт объект по первичному ключу:
```php
#[Route(path:'/getProduct/{product_id}')]
public function get(
#[MapEntity(id: 'product_id')]
Product $product
): Response
{
	//...
}
```

- `mapping` - настраивает свойства и значения для findOneBy() - ключ это имя заполнителя роута, значение - имя свойства в doctrine:
```php
#[Route(path:'/product/{category}/{slug}/comments/{comment_slug}')]
public function get(
#[MapEntity(mapping: ['category' => 'category', 'slug' => 'slug'])]
Product $product
#[MapEntity(mapping: ['comment_slug' => 'slug])]
Comment $comment
): Response
{
	//...
}
```

- `exclude` - настраивает свойства, которые должны использоваться в findOneBy, исключая одно или несколько совйств чтобы они не использовались:
```php
#[Route(path:'/product/{slug}/{date}')]
public function get(
#[MapEntity(exclude: ['date'])]
Product $product
DateTime $date
): Response
{
	//если я правильно понял, то date в ссылке игнорируется в маппинге объекта Product и подтягивается отдельно в объект DateTime.
}
```

- `stripNull` - если true, то при findOneBy() использовании любые значения null не будут использоваться для запроса.
  
<!> Нужно найти инфу об этом больше.
  
- `objectManager` - по умолчанию EntityValueResolver используется в маппинг, но его можно настроить:
```php
#[Route(path:'/prodcut/{id}')]
public function get(
#(MapEntity(objectManager: 'foo'))
Product $product
): Response
{
	//...
}
```

- `evictCache` - если стоит true, то mapper всегда достаёт данные из БД, а не из кеша.
- `disabled` - если стоит true, то исключает попытку EntityValueResolver заменить аргумент.
- `message` - Необязательное пользовательское сообщение, отображаемое при NotFoundHttpException, но только в среде разработке. Не работает на проде.
```php
#[Route(path: '/product/{product_id}')]
public function get(
#[MapEntity(id: 'product_id', message: 'Увы и ах, не найдено ничего!')]
Product $product
): Response
{
	//...
}
```
----
### Обновление объекта
После извлечения объекта из БД - можно с ним взаимодействовать, как и с обычным объектом php:
```php
class UpdateProduct
{
	public function __construct(
	private readonly EntityManagerInterface $entityManager,
	private readonly ProductRespository $productRepository,
	) {}

	#[Route(path:'/product/update', name: 'update_product', methods: ['POST'])]
	public function update(Request $request): Response
	{
		$id = $request->getPayload()->get('id');
		$newName = $request->getPayload()->get('name');

		$productToUpdate = $this->ProductRepository->find($id);

		if (null === $product) {
			throw new Exception('Product not found');
		}

		$productToUpdate->setName($name);
		$this->entityManager->flush();

		return $this->redirectToRoute('product_show', ['id' => $product->getId()])
	}
}
```
----
### Удаление объекта:
Тут всё просто, вызываем `remove()` в EntityManager:
```php
$this->entityManager->remove($entity);
$this->entityManager->flush();
```
----
### Запрос объектов: репозиторий
В случае если нужны более сложные запросы, чем find или findOneBy.

Версия с SQL запросом:
```php
class ProductRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::construct($registry, Product::class);
	}

	public function listByCeilingPrice(int $ceiling) {
		$entityManager = $this->getEntityManager();

		$query = $entityManager->createQuery(
		'SELECT p 
		FROM App/Entity/Product 
		WHERE p.price <= :ceiling 
		ORDER BY p.price ASC'
		)->setParameter('ceiling', $ceiling);

		return $query->getResult;
	}
}
```

```php
class ProductRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::construct($registry, Product::Class);
	}

	public function getByPriceCeiling(int $ceiling) {
		$products = $this->createQueryBuilder('p')
		->where('p.rice <= :ceiling')
		->setParameter('ceiling', $ceiling, Types:INTEGER)
		->getQuery()
		->getResult();

		return $products;
	}
}
```

Можно также использовать прямой SQL запрос:
```php
class ProductRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
	parent::construct($registry, Product::class);
	}
	
	public function getByPriceCeiling(int $ceiling) {
		$connection = $this->getEntityManager()->getConnection();

		$sql = 'SELECT * FROM product p WHERE p.price <= :ceiling ORDER BY p.price ASC';

		$result = $connection->executeQuery($sql, ['price' => $price]);

		return $result->fetchAllAssociative();
	}
}
```