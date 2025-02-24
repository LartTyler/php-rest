<?php
	namespace DaybreakStudios\RestBundle\Response;

	use Symfony\Component\Serializer\Context\ContextBuilderInterface;
	use Symfony\Component\Serializer\Context\ContextBuilderTrait;

	class ResponseBuilderContextBuilder implements ContextBuilderInterface {
		use ContextBuilderTrait;

		public const SERIALIZER_FORMAT = 'dbstudios.serializer_format';

		public function withSerializerFormat(string $format): static {
			return $this->with(static::SERIALIZER_FORMAT, $format);
		}
	}
