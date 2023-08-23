<?php
	namespace DaybreakStudios\Rest\Transformer\Traits;

	use DaybreakStudios\Rest\Transformer\Exceptions\ActionNotSupportedException;
	use DaybreakStudios\Rest\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see TransformerInterface::clone()
	 */
	trait CloneNotSupportedTrait {
		public function	doClone(EntityInterface $original): EntityInterface {
			throw new ActionNotSupportedException(static::class, 'clone');
		}
	}
