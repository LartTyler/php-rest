<?php
	namespace DaybreakStudios\Rest\Serializer\Mapping;

	interface AttributeMetadataInterface {
		public function getName(): string;
		public function isStrict(): bool;
		public function setStrict(bool $strict): static;
	}
