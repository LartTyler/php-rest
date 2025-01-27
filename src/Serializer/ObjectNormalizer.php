<?php
	namespace DaybreakStudios\RestBundle\Serializer;

	use DaybreakStudios\DoctrineQueryDocument\Projection\PrefixableProjectionInterface;
	use DaybreakStudios\DoctrineQueryDocument\Projection\PrefixedProjection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use DaybreakStudios\RestBundle\Serializer\ObjectNormalizerContextBuilder as Context;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
	use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
	use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
	use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
	use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
	use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
	use Symfony\Component\Serializer\Normalizer\ObjectNormalizer as WrappedObjectNormalizer;

	class ObjectNormalizer extends AbstractObjectNormalizer {
		public function __construct(
			protected WrappedObjectNormalizer $objectNormalizer,
			?ClassMetadataFactoryInterface $classMetadataFactory = null,
			?NameConverterInterface $nameConverter = null,
			?PropertyTypeExtractorInterface $propertyTypeExtractor = null,
			?ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
			callable $objectClassResolver = null,
			array $defaultContext = [],
		) {
			$context = [
					// Normally we want to collect denormalization errors, so we can convert them to a
					// ConstraintViolationError to send back to the API consumer.
					DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
				] + $defaultContext;

			parent::__construct(
				$classMetadataFactory,
				$nameConverter,
				$propertyTypeExtractor,
				$classDiscriminatorResolver,
				$objectClassResolver,
				$context,
			);
		}

		public function getSupportedTypes(?string $format): array {
			return [
				'object' => true,
			];
		}

		protected function isAllowedAttribute(
			object|string $classOrObject,
			string $attribute,
			?string $format = null,
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

		/**
		 * Returns `true` if the attribute is allowed by a {@see ProjectionInterface} (if one has been set in the
		 * context).
		 *
		 * If {@see ObjectNormalizerContextBuilder::STRICT} has been set in the context,
		 * {@see ProjectionInterface::isAllowedExplicitly()} should be used instead of
		 * {@see ProjectionInterface::isAllowed()} to determine if an attribute is allowed.
		 *
		 * @param string $attribute
		 * @param array  $context
		 *
		 * @return bool
		 */
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

		/**
		 * Returns `true` if the attribute should be checked using {@see ProjectionInterface::isAllowedExplicitly()}.
		 *
		 * Examples:
		 * ["id", "name" => ["first"]] => id, name.first
		 * ["*", "-name"] => all fields except name
		 *
		 * @param string $attribute
		 * @param array  $context
		 *
		 * @return bool
		 */
		protected function isStrictAttribute(string $attribute, array $context): bool {
			$strictAttributes = $context[Context::STRICT] ?? null;

			// Obviously, nothing is strict if we weren't provided with an array of strict nodes.
			if (!is_array($strictAttributes))
				return false;

			// If the attribute name prefixed with a minus "-" is in the array, then the field has been explicitly
			// flagged as NOT strict.
			if (in_array("-" . $attribute, $strictAttributes))
				return false;

			// If the match-all symbol is found in the array, then ALL fields at this level are flagged as strict.
			if (in_array(ProjectionInterface::MATCH_ALL_SYMBOL, $strictAttributes))
				return true;

			// Finally, the attribute can only still be strict if it's a value in the array. If it's a key, we can
			// assume it has child nodes that are strict, which will be handled later when we normalize children.
			return in_array($attribute, $strictAttributes, true);
		}

		protected function extractAttributes(object $object, string $format = null, array $context = []): array {
			$attributes = [];

			foreach ($this->objectNormalizer->extractAttributes($object, $format, $context) as $extractedAttribute) {
				if (!$this->isAllowedByProjection($extractedAttribute, $context))
					continue;

				$attributes[] = $extractedAttribute;
			}

			return $attributes;
		}

		protected function getAttributeValue(
			object $object,
			string $attribute,
			?string $format = null,
			array $context = [],
		): mixed {
			return $this->objectNormalizer->getAttributeValue($object, $attribute, $format, $context);
		}

		protected function setAttributeValue(
			object $object,
			string $attribute,
			mixed $value,
			?string $format = null,
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
			$context[Context::PROJECTION] = $this->createPrefixedProjection($prevProjection, $attribute);

			if (null !== $value = $context[Context::STRICT][$attribute] ?? null)
				$context[Context::STRICT] = $value;
			else if (null !== $strictAttributes = $context[Context::STRICT] ?? null) {
				$isNestedStrict = in_array(ProjectionInterface::MATCH_ALL_SYMBOL, $strictAttributes)
					|| in_array($attribute, $strictAttributes);

				if ($isNestedStrict)
					$context[Context::STRICT] = [ProjectionInterface::MATCH_ALL_SYMBOL];
				else
					unset($context[Context::STRICT]);
			}

			return $context;
		}

		protected function handleCircularReference(object $object, ?string $format = null, array $context = []): mixed {
			if ($object instanceof EntityInterface)
				return ['id' => $object->getId()];

			return parent::handleCircularReference($object, $format, $context);
		}

		/**
		 * Creates a new {@see ProjectionInterface} using a child attribute name as the projection's prefix.
		 *
		 * If $projection implements {@see PrefixableProjectionInterface},
		 * {@see PrefixableProjectionInterface::withPrefix()} should be used to create the prefixed projection to avoid
		 * deeply nesting projections as we descend through child attributes.
		 *
		 * @param ProjectionInterface $projection
		 * @param string              $prefix
		 *
		 * @return ProjectionInterface
		 */
		protected function createPrefixedProjection(
			ProjectionInterface $projection,
			string $prefix,
		): ProjectionInterface {
			if ($projection instanceof PrefixableProjectionInterface)
				return $projection->withPrefix($prefix);

			return new PrefixedProjection($projection, $prefix);
		}
	}
