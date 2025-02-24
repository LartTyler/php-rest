<?php
	namespace DaybreakStudios\RestBundle\Transformer\Traits;

	use DaybreakStudios\RestBundle\Transformer\Exceptions\ActionNotSupportedException;
	use DaybreakStudios\RestBundle\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see TransformerInterface::create()
	 */
	trait CreateNotSupportedTrait {
		public function doCreate(object $payload): EntityInterface {
			throw new ActionNotSupportedException(static::class, 'create');
		}
	}
