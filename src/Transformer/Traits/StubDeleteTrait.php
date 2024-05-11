<?php
	namespace DaybreakStudios\Rest\Transformer\Traits;

	use DaybreakStudios\Rest\Transformer\AbstractTransformer;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see AbstractTransformer::delete()
	 */
	trait StubDeleteTrait {
		public function doDelete(EntityInterface $entity): void {
			// no-op
		}
	}
