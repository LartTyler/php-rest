<?php
	namespace DaybreakStudios\Rest\Transformer\Traits;

	use DaybreakStudios\Rest\Transformer\Exceptions\ActionNotSupportedException;
	use DaybreakStudios\Rest\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see TransformerInterface::delete()
	 */
	trait DeleteNotSupportedTrait {
		public function doDelete(EntityInterface $entity): void {
			throw new ActionNotSupportedException(static::class, 'delete');
		}
	}
