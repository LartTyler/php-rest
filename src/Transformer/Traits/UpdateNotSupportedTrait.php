<?php
	namespace DaybreakStudios\RestBundle\Transformer\Traits;

	use DaybreakStudios\RestBundle\Transformer\Exceptions\ActionNotSupportedException;
	use DaybreakStudios\RestBundle\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see TransformerInterface::update()
	 */
	trait UpdateNotSupportedTrait {
		public function	doUpdate(object $data, EntityInterface $entity): void {
			throw new ActionNotSupportedException(static::class, 'update');
		}

		protected function getShouldUpdateAfterCreate(): bool {
			return false;
		}
	}
