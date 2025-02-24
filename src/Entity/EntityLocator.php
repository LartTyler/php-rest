<?php
	namespace DaybreakStudios\RestBundle\Entity;

	use Traversable;

	class EntityLocator implements \IteratorAggregate {
		public function __construct(
			protected string $basePath,
		) {}

		public function getIterator(): Traversable {
			$dirs = new \RecursiveDirectoryIterator($this->basePath, \FilesystemIterator::CURRENT_AS_PATHNAME);

			$recurse = new \RecursiveIteratorIterator($dirs);
			$iter = new \RegexIterator($recurse, '/\.php$/');

			foreach ($iter as $item) {
				$class = $this->getNamespace($item) . '\\' . basename($item, '.php');

				if (!class_exists($class))
					continue;

				yield $class;
			}
		}

		protected function getNamespace(string $file): ?string {
			if (!is_file($file) || !is_readable($file))
				return null;

			$handle = fopen($file, 'r');

			while (($line = fgets($handle)) !== false) {
				$line = trim($line);

				if (str_starts_with($line, 'namespace'))
					return rtrim(explode(' ', $line)[1], ';');
			}

			return '\\';
		}
	}