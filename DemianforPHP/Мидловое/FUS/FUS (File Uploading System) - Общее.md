Позволяет загружать данные в S3 совместимые хранилища и вытягивать из них метаданные.

- Стандартизирует процесс загрузки файлов
- Вывести логику загрузки в отдельный сервис

### Для работы с FUS требуется:
- Установить ограничения по потребляемым ресурсам.

### При падении:
- Восстанавливается только в ручном режиме
- В кубере можно указать `restartPolicy: always` что позволит куберу перезагрузить его автоматически, если он приляжет

### Переменные окружения:
- Используется для логирования, а также возвращается из корневого эндпоинта.
`APP_VERSION=0.0.0`

- Название окружения, в котором запущено приложение. Может содержать символы a-z (в нижнем регистре), 0-9 и дефис (-).
`ENVIRONMENT_NAME=local`

- Ключ от Sentry, который будет использоваться для репортинга ошибок в Sentry.
Эта переменная опциональна.
`SENTRY_DSN=`

- Алгоритм, используемый для подписи JWT.
Эта переменная опциональна. По умолчанию имеет значение "RS256".
`JWT_SIGNING_ALGORITHM=RS256`

- Значение, которое будет записано в поле "iss" в JWT
Эта переменная опциональна. По умолчанию имеет значение "fus".
`JWT_ISSUER=fus`

- Приватный ключ, который будет использоваться для подписи JWT.
`JWT_PRIVATE_KEY=`

- Публичный ключ, который будет использоваться для валидации JWT.
`JWT_PUBLIC_KEY=`

- Хост, на котором находится S3-совместимое хранилище.
Этому хосту не обязательно быть доступным из внешней сети
`S3_ENDPOINT=`

- Регион S3.
`S3_REGION=`

- Идентификатор ключа для доступа к S3.
`S3_ACCESS_KEY=`

- Секретное значение ключа для доступа к S3.
`S3_SECRET_KEY=`

- Бакет по умолчанию, в который будут загружаться файлы, у которых не был указан целевой бакет.
`S3_DEFAULT_BUCKET=`

- Пусть до исполняемого файпа ffprobe.
Эта переменная опциональна. По умолчанию имеет значение "ffprobe".
`FFPROBE_EXECUTABLE_PATH=ffproble`

- Пусть до исполняемого файпа file.
Эта переменная опциональна. По умолчанию имеет значение "file".
`FILE_EXECUTABLE_PATH=file`

### Логгирование
- микросервис пишет логи только в stdout

### CORS
- FUS поддерживает CORS. На данный момент поддерживаются запросы с любого ориджина: `Access-Control-Allow-Origin: *`

### Как работает:
- Для загрузки binary файла необходимо сгенерировать ссылку, которая будет на него указывать. Пример:
```
https://example.com/upload/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1MTYyMzkwMjIsImV4cCI6MTUzMTI1NjYzNCwiY29uc3RyYWludHMiOnsidmlkZW8vbXA0Ijp7ImR1cmF0aW9uIjp7Im1heCI6NjAwLCJtaW4iOjV9LCJmaWxlU2l6ZSI6eyJtYXgiOjEwNDg1NzYwfSwibWVnYXBpeGVscyI6eyJtYXgiOjEyfSwicmVzb2x1dGlvbiI6eyJtYXgiOnsiaGVpZ2h0IjoxOTIwLCJ3aWR0aCI6MTA4MH0sIm1pbiI6eyJoZWlnaHQiOjQ4MCwid2lkdGgiOjcyMH19fX0sInMzIjp7ImFjbCI6InByaXZhdGUiLCJidWNrZXQiOiJhdHRhY2htZW50cyIsImNhY2hlQ29udHJvbCI6InB1YmxpYywgbWF4LWFnZT02MDQ4MDAsIGltbXV0YWJsZSIsIm9iamVjdCI6eyJpZk9iamVjdEV4aXN0cyI6ImFkZF9pbmNyZW1lbnRhbF9zdWZmaXgiLCJwcmVmaXgiOiJkb2N1bWVudHMvIiwibmFtaW5nU3RyYXRlZ3kiOiJrZWVwX29yaWdpbmFsX2ZpbGVfbmFtZSJ9LCJzdG9yYWdlQ2xhc3MiOiJTVEFOREFSRCJ9LCJzdGF0ZSI6eyJwdXJwb3NlIjoiYnVzaW5lc3NfZG9jdW1lbnRzIn0sInZlcnNpb24iOjJ9.oFhyHOs9my4behzoh7aE30lZ5PKogC1J1655DCNW-VA
```
- Последняя часть ссылки это Json Web token - JWT
- Данный JWT содержит в себе информацию, закрытую приватным ключом куда нужно загрузить файл, а также требования, которым он должен соответствовать.
- Как правило, ссылку на FUS генерирует API
- Файл загружается методом `multipart/formdata`, где файл должен находиться в поле `file`.
- После успешной загрузки возвращаются все данные о файле, которые можн опередать в API

#### На пальцах:
1. Клиент сообщает API, что хочет загрузить файл на сервер. Например аватар пользователя.
2. API возвращает ссылку на загрузку файла (ссылка будет указывать на FUS)
3. Клиент загружает файл. Если файл будет соответствовать требованиям, указанным в JWT, то данный микросервис вернёт новый JWT, который нужно будет вернуть в API
4. Клиент вызвает метод API для сохранения автарки и передаёт в него полученный от микросервиса JWT. На этом этапе API редактирует профиль и обновляет аватар пользователя

### Поддерживаемые форматы:
Из всех форматов файлов достаются следующие метаданные:
- MIME-Type
- Размер в байтах

`image/*` (кроме `/image/svg+xml)
- ширина изображения
- высота изображения

`video/*`
- ширина видео
- высота видео
- длительность

`audio/*`
- длительность

### JWT Token
Общение между сервисами происходит путём обмена подписанными JWT

### Структура JWT для загрузки файла:
```json
{
"constraints": {
	"video/mp4": {
		"duration": {
			"max": 600,
			"min": 5
		},
		"filesize": {
			"max": 10485760
		},
		"megapixels": {
			"max": 12
		},
		"resolution": {
			"max": {
				"height": 1920,
				"width": 1080
			},
			"min": {
				"height": 480,
				"width": 720
			},
		}
	}
},
"s3": {
	"acl": private,
	"bucket": "attachments",
	"cacheControl": "public, max-age=604800, immutable",
	"objectKey": {
		"ifObjectExists": "add_incremental_suffix",
		"namingStrategy": "keep_original_file_name",
		"prefix": "documents/"
	},
	"storageClass": "STANDARD"
},
"state": {
	"purpose": "business_documents"
},
"version": 2
}
```

Пояснения полей:
- version - создан для обратной совместимости других версий FUS (на данный момент поддерживает только 2)
- constraints - отвечает за валидацию загружаемого файла. FUS должен убедиться, что файлы соответствуют данным требованиям
- state - сюда можно добавить, что угодно. Эти данные могут быть закодированы в JWT
- s3 - содержит информацию как хранить файл в случае успешной валидации

Constraints содержит в себе:
- MIME type файла
- Значение объекта: ограничения (требования) для данного типа файлов.

Таким образом можно разрешить загрузку разных типов файлов и ограничений к ним.

В ключе может быть указан как конкретный MIME type: `image/png`, так и общий тип: `image/*`, или вообще свободный тип `*/*`.
Более специфичный тип имеет более высокий приоритет нежели чем общий или свободный.

```
На данный момент не рекомендуется использовать не конкретный MIME Type, ибо это может привести к багам.
```

Все поля внутри объекта `constraints.{mime-type}` являются опциональными, включая все вложенные поля:
- `constraints.{mime-type}.fieSize` - допустимый вес файла в байтах
- `constraints.{mime-type}.resolution` - допустимое разрешение медиафайла, в пикселях. Игнорируется, если тип файла не image/video
- `constraints.{mime-type}.megapixels` - допустимое количество мегапикселей у файла. Количество мегапикселей считается как `width * height / 1000000`. Игнорируется, если тип файла не image/video
- `constraints.{mime-type}.duration` - допустимая длительность аудио или видео в секундах. Игнорируется, если тип файла не audio/video

Поля для s3:
- `s3.acl` - идентификатор доступности объекта s3. Если это поле не указано, то применяется ACL, указанный в настройках бакета. Может быть любой строкой, главное, чтобы поддерживался s3 хранилищем:
  - [WebSocket](https://docs.aws.amazon.com/AmazonS3/latest/userguide/acl-overview.html#canned-acl)
  - [Yandex Cloud](https://yandex.cloud/ru/docs/storage/concepts/acl?utm_referrer=about%3Ablank)
- `s3.bucket` - название бакета, в который нужно будет охранить объект. Если это поле не указано, объект будет сохранён в бакет, указанный в .env: `S3_DEFAULT_BUKCET`
- `s3.cacheControl` - значение для заголовка, который будет присвоен объекту
- `s3.objectKey.ifObjectExists` - определяет поведение если объект с данным ключом уже существует:
	- add_incremental_suffix - добавить суффикс в конце файла с порядковым номером
	- overwrite - перезаписать файл
- `s3.objectKey.namingStrategy` - Стратегия именования файла:
	- keep_original_file_name - сервис попробует использовать оригинальное имя файла в качестве ключа объекта. Оригинальное имя файла будет взять из multipart запроса.
	- uuid_v4_with_extension - в качестве имени файла будет использован uuid v4, а также добавлено расширение на основе MIME Type
	- uuid_v4 - в качестве имени файла будет использован uuid v4
- `s3.objectKey.prefix` - префикс для ключа объекта
- s3.storageClass - идентификатор класса хранилища. Если это поле не указано, будет использован тот класс хранилища, который указан в настройках бакета. Может быть любой строкой, главное чтобы поддерживался s3 хранилищем:
	- [Yandex Cloud](https://yandex.cloud/ru/docs/storage/concepts/storage-class#storage-class-identifiers)

### Ответ в случае удачной загрузки файла:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE1MTYyMzkwMjIsImV4cCI6MTUzMTI1NjYzNCwidXBsb2FkZWRGaWxlIjp7Im1ldGFkYXRhIjp7ImR1cmF0aW9uIjo2NC4zLCJmaWxlU2l6ZSI6NjMyNjg4MjQsIm1pbWVUeXBlIjoidmlkZW8vbXA0IiwicmVzb2x1dGlvbiI6eyJoZWlnaHQiOjEwODAsIndpZHRoIjoxOTIwfX0sIm9yaWdpbmFsRmlsZU5hbWUiOiJhZC5tcDQiLCJzMyI6eyJvYmplY3QiOiJzMzovL2Rrei12aWRlb3MvbWFya2V0aW5nL2FkLm1wNCJ9LCJ1cmwiOiJodHRwczovL3N0b3JhZ2UueWFuZGV4Y2xvdWQubmV0L2Rrei12aWRlb3MvbWFya2V0aW5nL2FkLm1wND9YLUFtei1BbGdvcml0aG09QVdTNC1ITUFDLVNIQTI1NiZYLUFtei1DcmVkZW50aWFsPXpnZlhQMElvb09zLWlWdEJvTE56JTJGMjAyMjA1MDYlMkZydS1jZW50cmFsMSUyRnMzJTJGYXdzNF9yZXF1ZXN0JlgtQW16LURhdGU9MjAyMjA1MDZUMTk1NzAwWiZYLUFtei1FeHBpcmVzPTM2MDAmWC1BbXotU2lnbmF0dXJlPUI4RDdEMDYxREUzNDk0NTNFOUU2RDZDRjc3MUE5MDQ2ODM0MzlCMkNCN0E4NDM4M0Y2NzRDQURFRTFFNjU5NDImWC1BbXotU2lnbmVkSGVhZGVycz1ob3N0In0sInN0YXRlIjp7InB1cnBvc2UiOiJidXNpbmVzc19kb2N1bWVudHMifSwidmVyc2lvbiI6Mn0.9YjCXuj6li-OnTdpK6DItA9828TPpvN0rfMgdTT4Doc",
  "uploadedFile": {
	  "metadata": {
		  "duration": 64.3,
		  "fileSize": 63268824,
		  "mimeType": "video/mp4",
		  "resolution": {
			  "height": 1080,
			  "width": 1920,
		  }
	  },
	  "originalFileName": "video.mp4",
	  "s3": {
		  "object:" "s3://videos/marketing/video.mp4"
	  },
	      "url": "https://storage.yandexcloud.net/dkz-videos/marketing/ad.mp4?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=zgfXP0IooOs-iVtBoLNz%2F20220506%2Fru-central1%2Fs3%2Faws4_request&X-Amz-Date=20220506T195700Z&X-Amz-Expires=3600&X-Amz-Signature=B8D7D061DE349453E9E6D6CF771A904683439B2CB7A84383F674CADEE1E65942&X-Amz-SignedHeaders=host"
  },
  "version": 2,
}
```

Поля:
- `token` - JWT, внутри которого находится JSON (этот JWT является валидным 24 часа с момента создания). В данном JSON могут быть два поля:
	- uploadedFile - структура и содержание совпадает с содержимом поле uploadedFile
	- state - не изменённое значение поля state из JWT, который использовался для загрузки файла. Это поле будет отсутствовать, если при загрузке не будет указан state
- `uploadedFile.metadata.duration` - длительность видео или аудио в секундах. Отсутствует, если загружен файл иного типа
- `uploadedFile.metadata.fileSize` - вес файла в байтах
- `uploadedFile.metadata.mimeType` - MIME Type файла
- `uploadedFile.metadata.resolution` - разрешение растрового изображения или видео. Отсутствует, если загружен файл иного типа.
- `uploadedFile.originalFilename` - оригинальное имя загруженного файла
- `uploadedFile.s3.object` - идентификатор объекта в s3 формате: `s3://{bucket}/{objectKey}`
- `uploadedFile.s3.url `- URL для доступа к файлу. Это подписанная ссылка на чтение объекта в s3. Подписанная ссылка будет действительна 24 часа с момента создания.