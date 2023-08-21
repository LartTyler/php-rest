<?php
	namespace DaybreakStudios\Rest\Serializer\Mapping;

	class AttributeMetadata implements AttributeMetadataInterface {
		public function __construct(
			protected string $name,
		) {

		}

		public function getName(): string {
			// TODO: Implement getName() method.
		}

		public function isStrict(): bool {
			// TODO: Implement isStrict() method.
		}
	}
