<?php
	namespace DaybreakStudios\Rest\Serializer;

	use DaybreakStudios\DoctrineQueryDocument\Projection\PrefixedProjection;
	use DaybreakStudios\DoctrineQueryDocument\Projection\ProjectionInterface;
	use Symfony\Component\Serializer\Context\ContextBuilderInterface;
	use Symfony\Component\Serializer\Context\ContextBuilderTrait;

	class EntityNormalizerContextBuilder implements ContextBuilderInterface {
		use ContextBuilderTrait;

		public const PROJECTION = 'dbstudios.projection';

		public function withProjection(ProjectionInterface $projection): static {
			return $this->with(static::PROJECTION, $projection);
		}

		public function withPrefixedProjection(string $prefix, ProjectionInterface $parentProjection): static {
			return $this->with(static::PROJECTION, new PrefixedProjection($parentProjection, $prefix));
		}
	}
