<?php
	namespace DaybreakStudios\RestBundle\Transformer\Traits;

	use DaybreakStudios\RestBundle\Transformer\AbstractTransformer;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see AbstractTransformer::delete()
	 */
	trait StubDeleteTrait {
		public function doDelete(EntityInterface $entity): void {
			// no-op
		}
	}
