<?php
	namespace DaybreakStudios\Rest\Transformer\Traits;

	use DaybreakStudios\Rest\Transformer\AbstractTransformer;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @see AbstractTransformer::clone()
	 */
	trait NaiveCloneTrait {
		public function doClone(EntityInterface $original, object $data = null): EntityInterface {
			return clone $original;
		}
	}
