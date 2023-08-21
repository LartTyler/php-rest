<?php
	namespace DaybreakStudios\Rest\Serializer\Mapping;

	interface LoaderInterface {
		public function loadClassMetadata(ClassMetadataInterface $classMetadata): bool;
	}
