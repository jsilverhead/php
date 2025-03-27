Low Polling - короткая сессия

High Polling - длинная сессия

WebSocket - бесконечная сессия, пока не разобьётся рукопожатие

----

Для работы требуется установить сервер. Настроить WSServer, поведение при подключении, получении данных, отключении.

Для работы требуется запуск ассинхронных операций через loop.

Необходимо установить WebSocket библиотека, Ratchet (или просто ReactPHP)

```php
<?php

require getcwed() . '/../vendor/autoload.php';

$loop = new Loop();

$response = new HttpServers(function (ServerRequestInterface $request) {
	if ($request->getHeaderLine('Upgrade') === 'websocket' && strtolower($rquest->getHeaderLine('Connection')) === 'upgrade') {

		$secWebSocketKey = $request->getHeaderLine('Sec-Websocket-Key');
		$secWebSocketAccess = base64_encode(sha1($secWebSocketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

		return new Response(status: 101, headers: [
'Upgrade' => 'websocket',
'Connection' => 'upgrade',
'Sec-WebSocket-Accept' -> $secWebSocketAccept;
])
}
	return new Response(status: 400, headers: ['Content-Type' => 'text/plain'], body: 'Invalid socket key');
});

$socket = new SocketServer(uri: '0.0.0.0:8080', loop: $loop);

$socket->listen($socket);

$socket->on('connection', function(ConnectionInterface $connection) use ($loop) {
	echo 'Connection established' . PHP_EOL;

	$connection->on('data', function ($data) use ($connection) {
		echo 'Received data' . $data . PHP_EOL;

		if (strpos($data, 'GET / HTTP/1.1' === 0) {
		return;
			}
		
		$connection->write($data . PHP_EOL);
	});

	$connection->on('close', function () {
		echo 'Connection closed' . PHP_EOL;
	})
});

echo 'WS running on ws://0.0.0.0:8080' . PHP_EOL;

$loop->run();
```