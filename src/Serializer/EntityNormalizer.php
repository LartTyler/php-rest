<?php
	namespace DaybreakStudios\Rest\Serializer;

	use DaybreakStudios\DoctrineQueryDocument\Projection\PrefixedProjection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use DaybreakStudios\Rest\Serializer\EntityNormalizerContextBuilder as Context;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
	use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
	use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
	use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
	use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
	use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

	class EntityNormalizer extends AbstractObjectNormalizer {
		public function __construct(
			protected ObjectNormalizer $objectNormalizer,
			ClassMetadataFactoryInterface $classMetadataFactory = null,
			NameConverterInterface $nameConverter = null,
			?PropertyTypeExtractorInterface $propertyTypeExtractor = null,
			ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
			callable $objectClassResolver = null,
			array $defaultContext = [],
		) {
			parent::__construct(
				$classMetadataFactory,
				$nameConverter,
				$propertyTypeExtractor,
				$classDiscriminatorResolver,
				$objectClassResolver,
				[static::ALLOW_EXTRA_ATTRIBUTES => false] + $defaultContext,
			);
		}

		public function supportsNormalization(mixed $data, string $format = null): bool {
			return $data instanceof EntityInterface;
		}

		public function getSupportedTypes(?string $format): array {
			return [
				EntityInterface::class => true,
			];
		}

		protected function isAllowedAttribute(
			object|string $classOrObject,
			string $attribute,
			string $format = null,
			array $context = [],
		): bool {
			return $this->isAllowedByProjection($attribute, $context)
				&& parent::isAllowedAttribute(
					$classOrObject,
					$attribute,
					$format,
					$context,
				);
		}

		protected function isAllowedByProjection(string $attribute, array $context): bool {
			/** @var ProjectionInterface|null $projection */
			$projection = $context[Context::PROJECTION] ?? null;

			// If we don't have a projection, everything is allowed
			if (!$projection)
				return true;

			if ($this->isStrictAttribute($attribute, $context))
				return $projection->isAllowedExplicitly($attribute);
			else
				return $projection->isAllowed($attribute);
		}

		protected function isStrictAttribute(string $attribute, array $context): bool {
			$strictAttributes = $context[Context::STRICT] ?? null;

			if (isset($strictAttributes[$attribute]))
				return true;

			if (is_array($strictAttributes))
				return in_array($attribute, $strictAttributes, true);

			return false;
		}

		protected function extractAttributes(object $object, string $format = null, array $context = []): array {
			return $this->objectNormalizer->extractAttributes($object, $format, $context);
		}

		protected function getAttributeValue(
			object $object,
			string $attribute,
			string $format = null,
			array $context = [],
		) {
			return $this->objectNormalizer->getAttributeValue($object, $attribute, $format, $context);
		}

		protected function setAttributeValue(
			object $object,
			string $attribute,
			mixed $value,
			string $format = null,
			array $context = [],
		): void {
			$this->objectNormalizer->setAttributeValue($object, $attribute, $value, $format, $context);
		}

		protected function createChildContext(array $parentContext, string $attribute, ?string $format): array {
			$context = parent::createChildContext(
				$parentContext,
				$attribute,
				$format,
			);

			$prevProjection = $context[Context::PROJECTION] ?? Projection::fromFields([]);
			$context[Context::PROJECTION] = new PrefixedProjection($prevProjection, $attribute);

			if (isset($context[Context::STRICT][$attribute]))
				$context[Context::STRICT] = $context[Context::STRICT][$attribute];
			else
				unset($context[Context::STRICT]);

			return $context;
		}
	}
