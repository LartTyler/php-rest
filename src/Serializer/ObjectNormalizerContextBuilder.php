<?php
	namespace DaybreakStudios\Rest\Serializer;

	use DaybreakStudios\DoctrineQueryDocument\Projection\PrefixedProjection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use Symfony\Component\Serializer\Context\ContextBuilderInterface;
	use Symfony\Component\Serializer\Context\ContextBuilderTrait;

	class ObjectNormalizerContextBuilder implements ContextBuilderInterface {
		use ContextBuilderTrait;

		public const PROJECTION = 'dbstudios.projection';
		public const STRICT = 'dbstudios.strict';

		public function withProjection(ProjectionInterface $projection): static {
			return $this->with(static::PROJECTION, $projection);
		}

		public function withStrict(array $strictAttributes): static {
			return $this->with(static::STRICT, $strictAttributes);
		}
	}
