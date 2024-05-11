<?php
	namespace DaybreakStudios\Rest\Serializer;

	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

	class EntityDenormalizer implements DenormalizerInterface {
		public function __construct(
			protected EntityManagerInterface $entityManager,
		) {}

		public function getSupportedTypes(?string $format): array {
			return [
				EntityInterface::class => true,
			];
		}

		public function supportsDenormalization(
			mixed $data,
			string $type,
			?string $format = null,
			array $context = [],
		): bool {
			return is_numeric($data) && is_a($type, EntityInterface::class, true);
		}

		public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed {
			return $this->entityManager->find($type, (int)$data);
		}
	}
