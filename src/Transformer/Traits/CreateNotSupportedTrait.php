<?php
	namespace DaybreakStudios\Rest\Transformer\Traits;

	use DaybreakStudios\Rest\Transformer\Exceptions\ActionNotSupportedException;
	use DaybreakStudios\Rest\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see TransformerInterface::create()
	 */
	trait CreateNotSupportedTrait {
		public function doCreate(object $payload): EntityInterface {
			throw new ActionNotSupportedException(static::class, 'create');
		}
	}
