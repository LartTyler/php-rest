<?php
	namespace DaybreakStudios\RestBundle\Transformer;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @template Entity of EntityInterface
	 * @template Payload of object
	 */
	interface TransformerInterface {
		/**
		 * @psalm-param Payload $data
		 *
		 * @param object        $data
		 *
		 * @return EntityInterface
		 * @psalm-return Entity
		 */
		public function create(object $data): EntityInterface;

		/**
		 * @psalm-param Payload   $data
		 * @psalm-param Entity    $entity
		 *
		 * @param object          $data
		 * @param EntityInterface $entity
		 *
		 * @return void
		 */
		public function update(object $data, EntityInterface $entity): void;

		/**
		 * @psalm-param Entity    $entity
		 *
		 * @param EntityInterface $entity
		 *
		 * @return void
		 */
		public function delete(EntityInterface $entity): void;

		/**
		 * @psalm-param Entity    $original
		 * @psalm-param Payload   $data
		 *
		 * @param EntityInterface $original
		 * @param object|null     $data
		 *
		 * @return EntityInterface
		 * @psalm-return Entity
		 */
		public function clone(EntityInterface $original, object $data = null): EntityInterface;
	}
