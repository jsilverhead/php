Для того, чтобы отдавать некие данные в консоль используется `LoggerInterface` и `ConsoleLogger`, передаётся через конструктор.

```php
class ParseNews extends Command
{
	public function __construct(private LoggerInterface $logger, private NewsParser $parser)


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$logger = new ConsoleLogger($output);
	
		$news = $this->parser->setLogger($logger)->parse(true);

		return Command::SUCCESS
	}
}
```

Внутри сервиса:
```php
class NewsParser
{
	public function __construct(private DOMParser $parser, private CreateTextService $createTextService) {}

	private LoggerInterface $logger;

	public function setLogger(LoggerInterface $logger): self {
		$this->logger = $logger;
	}

	public function parse(bool $paseAll): void
	{
		$this->logger->info('Starting job');
		$texts = $this->parser->parse('https://blogger.com/news');

		foreach($texts as $text) {
			$this->logger->info(
			sprintf('Parsing text: %s', $text->title
				)
			);
			$this->createTextService->create($text);
		}
	}
}
```