SOLID - аббревиатура принципов, которым необходимо следовать при разработке.

S - Single Responsibility Principle - принцип единой ответственности, когда отдельный класс / метод должен выполнять только одну задачу или задачу только одного актора.

O - Open Close Priniciple - принцип, при которой код должен быть открыт для расширения, но закрыт для изменений. То есть необходимо разрабатывать более гибкий, расширяемый код.

L - Liskov Principle - принцип Барбары Лисков при котором классы должны наследоваться от родительских без ошибок:
```php
# Неправильно
class Bird {
	public function fly(){...}
}

class Penguin extends Bird {
	public function fly() {
		throw new Exception('can not fly');
	}
}

# Правильно

class Bird {}

class FlyingBird extends Bird {
	public function fly(){...}
}

class Penguin extends Bird {
	// не имеет функции fly
}
```

I - Interface Segregation Principle - принцип при котором необходимо делить интерфейсы на более мелкие, чтобы классы которые реализуют интерфейс не требовали методов, которые им не нужны.

D - Dependency Inversion Principle - принцип при котором зависимости должны быть построены на абстракциях, а не на конкретных классах.

```php
# неправильно
class Injector {

	private $currentInjector;

	public function __construct() {
		$currentInjector = new PropertyInjector();
	}
}

# правильно
class Injector {
	public function __construct(Injector $currentInjector)
}
```