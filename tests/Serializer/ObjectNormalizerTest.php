<?php
	namespace Serializer;

	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\Rest\Serializer\ObjectNormalizer;
	use DaybreakStudios\Rest\Serializer\ObjectNormalizerContextBuilder;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use PHPUnit\Framework\TestCase;
	use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder as BaseObjectNormalizerContextBuilder;
	use Symfony\Component\Serializer\Encoder\JsonEncoder;
	use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
	use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
	use Symfony\Component\Serializer\Normalizer\ObjectNormalizer as BaseObjectNormalizer;
	use Symfony\Component\Serializer\Serializer;

	class ObjectNormalizerTest extends TestCase {
		protected Serializer $serializer;
		protected ObjectNormalizer $normalizer;

		public function testNormalizeWithoutProjection() {
			$entity = new TestEntity();
			$output = $this->normalizer->normalize($entity);

			$this->assertEquals(
				[
					'id' => 0,
					'name' => 'Test',
					'isFoo' => true,
					'bar' => 'bar',
				],
				$output,
			);
		}

		public function testNormalizeWithProjection() {
			$entity = new TestEntity();
			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(
					Projection::fromFields(
						[
							'id' => true,
						],
					),
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(['id' => 0], $output);
		}

		public function testNormalizeDifferentProjections() {
			$entity = new TestEntity();
			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(
					Projection::fromFields(
						[
							'id' => true,
							'bar' => true,
						],
					),
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(['id' => 0, 'bar' => 'bar'], $output);

			$context = $context->withProjection(
				Projection::fromFields(
					[
						'id' => false,
						'bar' => false,
					],
				),
			);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(['name' => 'Test', 'isFoo' => true], $output);
		}

		public function testNoAccessWhenDeniedByProjection() {
			$entity = $this->createMock(TestEntity::class);
			$entity->expects($this->never())->method('getBar');
			$entity->expects($this->once())->method('getId')->willReturn(0);
			$entity->expects($this->once())->method('getName')->willReturn('Test');

			$context = (new ObjectNormalizerContextBuilder())
				->withContext($this->getMockObjectIgnoredAttributesContext())
				->withProjection(
					Projection::fromFields(
						[
							'bar' => false,
						],
					),
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(['id' => 0, 'name' => 'Test', 'isFoo' => true], $output);
		}

		public function testNestedObjects() {
			$parent = new ParentEntity();

			$output = $this->normalizer->normalize($parent);
			$this->assertEquals(
				[
					'id' => 0,
					'child' => [
						'id' => 0,
						'name' => 'Test',
						'bar' => 'bar',
						'isFoo' => true,
					],
				],
				$output,
			);

			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(
					Projection::fromFields(
						[
							'child' => false,
						],
					),
				);

			$output = $this->normalizer->normalize($parent, context: $context->toArray());
			$this->assertEquals(['id' => 0], $output);

			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(
					Projection::fromFields(
						[
							'id' => false,
							'child.name' => false,
						],
					),
				);

			$output = $this->normalizer->normalize($parent, context: $context->toArray());
			$this->assertEquals(
				[
					'child' => [
						'id' => 0,
						'bar' => 'bar',
						'isFoo' => true,
					],
				],
				$output,
			);
		}

		public function testStrictAttribute() {
			$entity = new ParentEntity();
			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(
					Projection::fromFields(
						[
							'id' => true,
						],
					),
				)
				->withStrict(
					[
						'child',
					],
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(
				[
					'id' => 0,
				],
				$output,
			);

			$context = $context
				->withProjection(
					Projection::fromFields(
						[
							'child.bar' => true,
						],
						true,
					),
				)
				->withStrict(
					[
						'child' => [
							'isFoo',
							'bar',
						],
					],
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(
				[
					'id' => 0,
					'child' => [
						'id' => 0,
						'name' => 'Test',
						'bar' => 'bar',
					],
				],
				$output,
			);

			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(
					Projection::fromFields(
						[
							'child' => true,
						],
						true,
					),
				)
				->withStrict(
					[
						'child',
					],
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(
				[
					'id' => 0,
					'child' => [
						'id' => 0,
						'name' => 'Test',
						'bar' => 'bar',
						'isFoo' => true,
					],
				],
				$output,
			);

			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(
					Projection::fromFields(
						[
							'child' => true,
						],
						true,
					),
				)
				->withStrict(
					[
						'child' => [
							'isFoo',
						],
					],
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(
				[
					'id' => 0,
					'child' => [
						'id' => 0,
						'name' => 'Test',
						'isFoo' => true,
						'bar' => 'bar',
					],
				],
				$output,
			);

			$context = (new ObjectNormalizerContextBuilder())
				->withStrict(
					[
						'child' => [
							'*',
							'-name',
						],
					],
				);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(
				[
					'id' => 0,
					'child' => [
						'name' => 'Test',
					],
				],
				$output,
			);

			$context = $context->withProjection(
				Projection::fromFields(
					[
						'child.id' => true,
					],
					true,
				),
			);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(
				[
					'id' => 0,
					'child' => [
						'name' => 'Test',
						'id' => 0,
					],
				],
				$output,
			);

			$context = $context->withStrict(
				[
					'child',
				],
			);

			$output = $this->normalizer->normalize($entity, context: $context->toArray());
			$this->assertEquals(
				[
					'id' => 0,
					'child' => [
						'id' => 0,
					],
				],
				$output,
			);
		}

		public function testArray() {
			$entities = [
				new TestEntity(),
				new TestEntity(),
				new TestEntity(),
			];

			$context = (new ObjectNormalizerContextBuilder())
				->withProjection(Projection::fromFields(['id' => true]));

			$output = $this->serializer->normalize($entities, context: $context->toArray());
			$this->assertEquals(
				[
					[
						'id' => 0,
					],
					[
						'id' => 0,
					],
					[
						'id' => 0,
					],
				],
				$output,
			);
		}

		public function testCircularReference() {
			$entityA = new CircularEntity();
			$entityB = new CircularEntity($entityA);
			$entityA->other = $entityB;

			$output = $this->normalizer->normalize($entityA);
			$this->assertEquals(
				[
					'id' => 0,
					'other' => [
						'id' => 0,
						'other' => [
							'id' => 0,
						],
					],
				],
				$output,
			);
		}

		protected function setUp(): void {
			$metadataFactory = new ClassMetadataFactory(new AnnotationLoader());

			$baseNormalizer = new BaseObjectNormalizer($metadataFactory);
			$this->normalizer = new ObjectNormalizer($baseNormalizer, $metadataFactory);

			$this->serializer = new Serializer(
				[
					$this->normalizer,
					$baseNormalizer,
				],
				[
					new JsonEncoder(),
				],
			);
		}

		private function getMockObjectIgnoredAttributesContext(): array {
			// Required in order to support passing mock objects to a normalizer
			return (new BaseObjectNormalizerContextBuilder())
				->withIgnoredAttributes(
					[
						'__phpunit_configurableMethods',
						'__phpunit_returnValueGeneration',
						'__phpunit_invocationMocker',
						'__phpunit_originalObject',
					],
				)
				->toArray();
		}
	}

	class TestEntity implements EntityInterface {
		public bool $isFoo = true;
		private int $id = 0;

		public function __construct(
			private readonly string $name = 'Test',
		) {}

		public function getId(): int {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getName(): string {
			return $this->name;
		}

		public function getBar(): string {
			return 'bar';
		}
	}

	class ParentEntity implements EntityInterface {
		private int $id = 0;
		private TestEntity $child;

		public function __construct() {
			$this->child = new TestEntity();
		}

		/**
		 * @return int
		 */
		public function getId(): int {
			return $this->id;
		}

		/**
		 * @return TestEntity
		 */
		public function getChild(): TestEntity {
			return $this->child;
		}
	}

	class CircularEntity implements EntityInterface {
		public function __construct(
			public ?CircularEntity $other = null,
		) {}

		public function getId(): ?int {
			return 0;
		}
	}
