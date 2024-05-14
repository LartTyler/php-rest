<?php
	namespace DaybreakStudios\RestBundle\Controller\Exceptions;

	use JetBrains\PhpStorm\Pure;

	class NullPayloadException extends \Exception {
		#[Pure]
		public function __construct(int $code = 0, ?\Throwable $previous = null) {
			parent::__construct(
				'Payload instance was not parsed out of the request; did you forget to register a listener?',
				$code,
				$previous,
			);
		}
	}
