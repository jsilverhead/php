IDE для работой с кластерами Kubernetes.

## Настройка Lens

Установить утилиту [yc](https://cloud.yandex.com/en-ru/docs/cli/operations/install-cli) (Yandex Cloud cli).  
Установить утилиту [Lens](https://k8slens.dev/) (графическая оболочка для kubernetes).
#### [YC config](https://wiki.yandex.ru/homepage/otdel-backend/baza-znanijj/infrastruktura/nastrojjka-lens/#yc-config)

Смотрим список проектов, напротив выбранного будет стоять ACTIVE:

```
yc config profile list
```

Список будет пустой, если вы только что поставили эту утилиту. Если нужного проекта нет в списке, нужно его инициализировать.  
Команда `init` создаст новый профиль, перейдем по ссылке, авторизуемся на Яндекс Облаке. Затем выберем облако (most), фолдер (default) и зону (ru-central1-a).

```
yc init
```

Если надо переключиться на другой проект пишем:

```
yc config profile activate <нужный_профиль_из_списка>
```

Данные по доступным кластерам пишутся в `~/.kube/config`

Для добавления кластера вводим команду
```bash
yc container cluster get-credentials <ID_нужного_кластера> --external --force
```

## Поды:

Можно наблюдать за работой подов кластера в разделе Pods.
- Для запуска команд типа миграций использовать `shell` php-fpm.
- Для просмотра логов использовать `logs` nginx (чтобы видеть запросы и код ответа).

## Cron Jobs:

Можно редактировать и запускать новые джобы в разделе Cron Jobs.
- Для добавления Cron Job можно нажать +, далее заполнить данные по таймингу и, имени и команде.
- Можно устанавливать и перезапускать Cron Job'ы