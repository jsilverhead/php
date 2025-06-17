Aspect-Oriented Programming - принцип при котором необходимо сквозную логику выносить в отдельные модули - аспекты.

#### Что можно считать сквозной логикой?
Функциональность, которая распределена по разным частям программы, но не относится к её основной бизнес-логике. Например:
- Логгирование
- Кеширование
- Транзакции
- Безопасность
- Обработка ошибок

#### Термины AOP:
- Аспект - отдельный модуль, содержащий сквозную логику
- Совет - код, который выполняется до, после, вокруг целевого метода
- Точка соединения - место, где аспект внедряется
- Срез - выражение, определяющее, к каким методам применяется аспекты

Пример AOP:
```php
class OrderService {
	public function createOrder(Order $order) {
		$this->order->save();
	}
}

class LoggingAspect implements Aspect {

	/**
	* @Before("execution(OrderService->createOrder(*))")
	*/
	public function logBeforeCreateOrder(JoinPoint $jp) {
		$order = $jp->getArgs()[0];
		Logger::log('Creating order' . $order->getId());
	}

	/**
	* @After("execution(OrderService->createOrder(*))")
	*/
	public function logAfterCreateOrder(JoinPoint $jp) {
		$order = $jp->getArgs()[0];
		Logger::log('Order created' . $order->getId());
	}
}
```

Также необходимо настраивать AOP:
```php
class ApllicationApectKernel extends Aspectkernel
{
	protected function configureAop(AspectContainer $container)
	{
		$container->registerAspect(new LoggingAspect);
	}
}
```

#### Плюсы и минусы:
**Плюсы:**
- Уменьшает дублирование
- Чистая бизнес-логика
- Гибкость (можно включать и выключать аспекты)

**Минусы:**
- Сложность отладки (логика спрятана в аспектах)
- Проблемы с производительностью
- Не везде применим

JoinPoint - содержит в себе атрибуты вызова логгируемых функций
```php
class OrderService
	#[Loggable]
	public function createOrder(...) {...}
```

А то что пишется в аннотациях или атрибутах это точка среза в AOP.