<?php
	namespace DaybreakStudios\Rest\Serializer\Mapping;

	interface ClassMetadataInterface {
		public function getName(): string;

		public function addAttributeMetadata(AttributeMetadataInterface $metadata): void;

		/**
		 * @return AttributeMetadataInterface[]
		 */
		public function getAllAttributeMetadata(): array;

		public function getAttributeMetadata(string $name): ?AttributeMetadataInterface;

		public function getReflectionClass(): \ReflectionClass;
	}
