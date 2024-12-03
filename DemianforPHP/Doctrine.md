Настройка обновление схемы БД через доктрину.
```php
#config без миграций:
<configuration default="false" name="Setup clean DB" type="PhpLocalRunConfigurationType" factoryName="PHP Console" path="$PROJECT_DIR$/app/bin/console" scriptParameters="doctrine:schema:update --force --no-interaction --env=test">
```