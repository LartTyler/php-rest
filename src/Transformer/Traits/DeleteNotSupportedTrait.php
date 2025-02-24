<?php
	namespace DaybreakStudios\RestBundle\Transformer\Traits;

	use DaybreakStudios\RestBundle\Transformer\Exceptions\ActionNotSupportedException;
	use DaybreakStudios\RestBundle\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see TransformerInterface::delete()
	 */
	trait DeleteNotSupportedTrait {
		public function doDelete(EntityInterface $entity): void {
			throw new ActionNotSupportedException(static::class, 'delete');
		}
	}
