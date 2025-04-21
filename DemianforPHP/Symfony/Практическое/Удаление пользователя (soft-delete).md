При удалении пользователя лучшей практикой является анонимизация личных (чувстительных данных).

1. Изменить email:
   ```php
$user->setEmail('deleted_' . $user->getId()->toRfc4122() . '@example.com')

// ИЛИ

$user->setEmail(uniqid('deleted_', true) . '@example.com')

```
2. Изменить пароль:
```php
$hashedPassword = $this->passwordHasher->hash($user, inuquid('deleted_', true));

$user->setPassword = $hasherdPassword;
```
3. Добавить deletedAt(необязательно):
```php
$user->setDeletedAt(new DateTimeImmutable());
```

##### Что делает uniqid?
Генерирует уникальный идентификатор исходя из состояния даты вплоть до милисекунд. Принимает префикс и параметр энтропии. true - добавляет больше энтропии, создавая более уникальный идентификатор.