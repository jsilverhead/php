Позволяет модифицировать контейнер зависимостей на этапе комплияции.

Это класс в Symfony, который реализует интерфейс `CompilerPassInterface`. Его задача - вмешиваться в процесс комплияции контейнера зависимостей при каждом запросе.

Он позволяет добавлять, изменять, удалять определения сервисов. Используется ДО того как конейтнер начнёт использоваться для создания экземпляров сервисов.

##### Применяется в случаях когда:
- Нужно динамически добавлять или изменять сервисы:
	- Например добавить коллекцию обработчиков событий
	- Зарегистрировать дополнительные валидаторы форм
- Нужно изменять определения существующих сервисов:
	- Например, добавлять middleware к определённому сервису
	- Или менять приоритет сервиса
- Нужно собирать информацию о зарегистрированных сервисах:
	- Например находить все сервисы с определённым тегом
	- Формировать список всех доступных команд консоли
- Нужна сложная логика конфигурации сервисов:
	- Если обычных настроек файла `services.php (yaml)` недостаточно

##### Как это работает?
1. Создаёшь класс, который реализует интерфейс `CompilerPassInterface`.
2. Определить в классе метод `process`, где можно получить доступ к контейнеру ContainerBuilder $container, передав его в аргумент.
3. Через $container можно получать доступ к:
		- Получению определения сервисов `$container->getDefinition('service_id)`
		- Добавлению определения сервисов `$container->register('new_service_id', NewService::class)`
		- Изменять определение сервисов `$container->addMethodCall('method_name', ['arg1', 'arg2'])`
		- Удалять определением сервисов `$container->removeDefinition('service_id')`
		- Получать сервисы, помеченные определённым тегом `$container->findTaggedServiceIds('tag_name)'`
4. Регистрация Complier Pass:
	- Регистрируется в ядре Symfony (как правило через bundle)
	- Для регистрации нужно использовать тег `container.compiler_pass`.

[x] Вообще это можно в Kernel классе настроить:
```php
$container->addCompilerPass(new CodeGeneratorCompilerPass());
```

Иными словами - Compiler Pass задаёт "стратегию" работы некоторых сервисов. Например:
- Стратегия верификации 0000
- Стратегия верификации Random Number

Пример CompilerPass:
```php
class CodeGeneratorCompilerPass implements CompilerPassInterface
{
	private const FOUR_RANDOM_DIGITS_STRATEGY = 'four_random_digits';

	private const FOUR_ZEROS_STRATEGY = 'four_zeros';

	public function process(ContainerBuilder $container): void
	{
		$strategy = $container->resolveEnvPlaceholders(
			$container->getparameter('confirmation_code_generator_strategy'), true)
		);
	match ($strategy) {
		self::FOUR_ZEROS_STRATEGY => $container->setDefinition(CodeGenerator::class,
		new Definition(FourZerosCodeGenerator::Class),
		),
		self::FOUR_RANDOM_DIGITS_STRATEGY => $container->setDefinition(CodeGenerator::class, new Definition(FourRandomDigitsCodeGenerator::class)
		),
		default => throw new InvalidArgumentException(
			sprintf(
			'Unsupported CONFIRMATION_CODE_GENERATOR_STRATEGY value: %s. Possible values: %s, %s'), $strategy, self::FOUR_ZEROS_STRATEGY, self::FOUR_RANDOM_DIGITS_STRATEGY
		),
	}
	}
}
```