Варианты doctrine events:
- prePersist
- postPersist
- preRemove
- postRemove
- preUpdate
- postUpdate
- loadClassMetadata
- preFlush
- onFlush
- postFlush (не работает с EntityListener)
- onClear


```php
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Product {

	#[ORM\PrePersist]
	public function setCreatedAtValue(): void
	{
		$this->createdAt = new DateTimeImmutable();
	}
}
```

```php
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Post {

	#[ORM\PreUpdate]
	public function setBlockedAt(): void
	{
		if ($this->isBlocked && !$this->blockedAt) {
			$this->blockedAt = new DateTimeImmutable();
		}
	}
}
```

Как сделать EventListener:

```php

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Blog::class)]
class BlogListener
{
	public function preUpdate(Blog $blog, PreUpdateEventArgs $event): void {
		// Тут любая логика (PreUpdate выполнит этот листенер)
	}
}
```

Для всех сущностей делается LifecycleListener:
```php
#[AsDoctrineListener(event: Events::postFlush, priority: 500, connection: 'default')]
#[AsDoctrineListeren(event: Events::postPersist, priority: 500, connection: 'default')]
class BlogListener
{
	private array $entities = [];

	public function __construct(private MessageBusInterface $bus) {}

	public function postFlush(PostFlushEventArgs $event) {
		foreach(entities as entity) {
			$this->bus->dispatch(
			new ContentWatchJob(
			$entity->getId()
				)
			);
		}
	}

	public function postPersist(PostPersistEventArgs $event) {
		if ($event->getObject() instanceof Blog) {
			$this->entities[] = $event->getObject();
		}
	}
}
```