<?php
	namespace DaybreakStudios\RestBundle\Transformer\Traits;

	use DaybreakStudios\RestBundle\Transformer\Exceptions\ActionNotSupportedException;
	use DaybreakStudios\RestBundle\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see TransformerInterface::clone()
	 */
	trait CloneNotSupportedTrait {
		public function	doClone(EntityInterface $original, object $data = null): EntityInterface {
			throw new ActionNotSupportedException(static::class, 'clone');
		}
	}
