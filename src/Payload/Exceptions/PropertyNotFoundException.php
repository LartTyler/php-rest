<?php
	namespace DaybreakStudios\Rest\Payload\Exceptions;

	class PropertyNotFoundException extends \Exception {
		public function __construct(string $class, string $property, int $code = 0, ?\Throwable $previous = null) {
			parent::__construct(
				sprintf('Could not access %s::$%s because it does not exist', $class, $property),
				$code,
				$previous,
			);
		}
	}
