<?php
	namespace DaybreakStudios\Rest\Serializer\Mapping;

	/**
	 * @template T
	 */
	class ClassMetadata implements ClassMetadataInterface {
		protected \ReflectionClass $reflectionClass;

		/**
		 * @var AttributeMetadataInterface[]
		 */
		protected array $attributes = [];

		/**
		 * @param class-string<T> $class
		 */
		public function __construct(
			protected string $class,
		) {}

		/**
		 * @return class-string<T>
		 */
		public function getName(): string {
			return $this->class;
		}

		public function addAttributeMetadata(AttributeMetadataInterface $metadata): void {
			$this->attributes[$metadata->getName()] = $metadata;
		}

		public function getAllAttributeMetadata(): array {
			return $this->attributes;
		}

		public function getAttributeMetadata(string $name): ?AttributeMetadataInterface {
			return $this->getAllAttributeMetadata()[$name] ?? null;
		}

		/**
		 * @return \ReflectionClass<T>
		 */
		public function getReflectionClass(): \ReflectionClass {
			if (!isset($this->reflectionClass))
				$this->reflectionClass = new \ReflectionClass($this->getName());

			return $this->reflectionClass;
		}

		public function __sleep(): array {
			return [
				'name',
				'attributes',
			];
		}
	}
