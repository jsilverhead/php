Как это работает:
1. Подписываемся на события
2. Получаем уведомления

Адрес для подписки на событие выглядит примерно так: `http://sse-service.com/?accessToken=jwt`

Пример на JavaScript:
```js
const source = new EventSource('http://localhost/?accesToken=...');
source.onopen = function (event) {
	console.log('open', event);
}
source.onerror = function (event) {
	console.log('error', event)
}
source.addEventListener('conversation-message', (event) => {
	console.log('conversationMessage called');
	console.log(event);
	console.log(JSON.parse(event.data));
})
```