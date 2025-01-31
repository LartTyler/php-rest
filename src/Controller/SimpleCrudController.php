<?php
	namespace DaybreakStudios\RestBundle\Controller;

	use DaybreakStudios\DoctrineQueryDocument\QueryManagerInterface;
	use DaybreakStudios\RestBundle\Entity\AsCrudEntity;
	use DaybreakStudios\RestBundle\Response\ResponseBuilderInterface;
	use DaybreakStudios\RestBundle\Serializer\ObjectNormalizerContextBuilder;
	use DaybreakStudios\RestBundle\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use Psr\EventDispatcher\EventDispatcherInterface;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Serializer\Context\ContextBuilderInterface;

	class SimpleCrudController extends AbstractApiController {
		public function __construct(
			EventDispatcherInterface $eventDispatcher,
			ResponseBuilderInterface $responseBuilder,
			EntityManagerInterface $entityManager,
			QueryManagerInterface $queryManager,
			protected string $entity,
			protected ?string $dtoClass = null,
			protected mixed $firewallRole = null,
			protected array $allowedCrudMethods = [],
			protected ?TransformerInterface $transformer = null,
			protected ?array $strict = null,
		) {
			parent::__construct($eventDispatcher, $responseBuilder, $entityManager, $queryManager);
		}

		public function list(): Response {
			assert($this->checkCrudMethodAllowed(AsCrudEntity::METHOD_LIST));
			return $this->doList($this->entity);
		}

		public function create(): Response {
			assert($this->checkCrudMethodAllowed(AsCrudEntity::METHOD_CREATE));

			if ($this->firewallRole)
				$this->denyAccessUnlessGranted($this->firewallRole);

			return $this->doCreate($this->dtoClass, $this->transformer);
		}

		public function read(int $entity): Response {
			assert($this->checkCrudMethodAllowed(AsCrudEntity::METHOD_READ));
			return $this->respond($this->findEntityOrThrow($entity));
		}

		public function update(int $entity): Response {
			assert($this->checkCrudMethodAllowed(AsCrudEntity::METHOD_UPDATE));

			if ($this->firewallRole)
				$this->denyAccessUnlessGranted($this->firewallRole);

			return $this->doUpdate($this->dtoClass, $this->findEntityOrThrow($entity), $this->transformer);
		}

		public function delete(int $entity): Response {
			assert($this->checkCrudMethodAllowed(AsCrudEntity::METHOD_DELETE));

			if ($this->firewallRole)
				$this->denyAccessUnlessGranted($this->firewallRole);

			return $this->doDelete($this->findEntityOrThrow($entity), $this->transformer);
		}

		protected function checkCrudMethodAllowed(string $method): bool {
			assert(
				$this->isCrudMethodAllowed($method),
				$this->entity . ' is not configured to support the ' . $method . ' action',
			);

			match ($method) {
				AsCrudEntity::METHOD_CREATE, AsCrudEntity::METHOD_UPDATE => assert(
					$this->transformer !== null && $this->dtoClass !== null,
					'Provide an argument for "transformer" and "dtoClass" to use ' . $method . ' for ' . $this->entity,
				),
				AsCrudEntity::METHOD_DELETE => assert(
					$this->transformer !== null,
					'Provide an argument for "transformer" to use ' . $method . ' for ' . $this->entity,
				),
				default => null,
			};

			return true;
		}

		protected function isCrudMethodAllowed(string $method): bool {
			return !$this->allowedCrudMethods || isset($this->allowedCrudMethods[$method]);
		}

		protected function findEntityOrThrow(int $id): EntityInterface {
			$entity = $this->entityManager->find($this->entity, $id);
			return $entity ?? throw $this->createNotFoundException();
		}

		protected function createSerializerContext(): ContextBuilderInterface|array {
			$context = (new ObjectNormalizerContextBuilder())->withContext(parent::createSerializerContext());

			if ($this->strict !== null)
				$context = $context->withStrict($this->strict);

			return $context;
		}
	}