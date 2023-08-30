<?php
	namespace DaybreakStudios\Rest\Transformer;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	/**
	 * @template T of object
	 * @template E of EntityInterface
	 */
	interface TransformerInterface {
		/**
		 * @param T&object $data
		 *
		 * @return E&EntityInterface
		 */
		public function create(object $data): EntityInterface;

		/**
		 * @param T&object          $data
		 * @param E&EntityInterface $entity
		 *
		 * @return void
		 */
		public function update(object $data, EntityInterface $entity): void;

		/**
		 * @param E&EntityInterface $entity
		 *
		 * @return void
		 */
		public function delete(EntityInterface $entity): void;

		/**
		 * @param E&EntityInterface $original
		 * @param object|null       $data
		 *
		 * @return E&EntityInterface
		 */
		public function clone(EntityInterface $original, object $data = null): EntityInterface;
	}
