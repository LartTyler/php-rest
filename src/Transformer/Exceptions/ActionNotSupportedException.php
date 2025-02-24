<?php
	namespace DaybreakStudios\RestBundle\Transformer\Exceptions;

	class ActionNotSupportedException extends \Exception {
		public function __construct(string $class, string $action) {
			parent::__construct($class . ' cannot ' . $action . ' objects');
		}
	}
