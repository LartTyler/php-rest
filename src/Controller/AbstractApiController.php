<?php
	namespace DaybreakStudios\Rest\Controller;

	use DaybreakStudios\DoctrineQueryDocument\Projection\Projection;
	use DaybreakStudios\DoctrineQueryDocument\QueryManagerInterface;
	use DaybreakStudios\Rest\Controller\Exceptions\NullPayloadException;
	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use DaybreakStudios\Rest\Error\AsApiErrorInterface;
	use DaybreakStudios\Rest\Event\Events\Controller\PayloadInitEvent;
	use DaybreakStudios\Rest\Event\Events\Controller\ProjectionInitEvent;
	use DaybreakStudios\Rest\Event\Events\Controller\QueryInitEvent;
	use DaybreakStudios\Rest\Event\Events\Controller\QueryLimitInitEvent;
	use DaybreakStudios\Rest\Event\Events\Controller\QueryOffsetInitEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\EntityCreateEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\EntityDeleteEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\EntityUpdateEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\PostEntityCreateEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\PostEntityUpdateEvent;
	use DaybreakStudios\Rest\Response\ResponseBuilderInterface;
	use DaybreakStudios\Rest\Serializer\ObjectNormalizerContextBuilder;
	use DaybreakStudios\Rest\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use Psr\EventDispatcher\EventDispatcherInterface;
	use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Serializer\Context\ContextBuilderInterface;

	abstract class AbstractApiController extends AbstractController {
		protected function __construct(
			private readonly EventDispatcherInterface $eventDispatcher,
			private readonly ResponseBuilderInterface $responseBuilder,
			private readonly EntityManagerInterface $entityManager,
			private readonly QueryManagerInterface $queryManager,
		) {}

		/**
		 * @template T of object
		 *
		 * @param class-string<T>      $dtoClass
		 * @param TransformerInterface $transformer
		 *
		 * @return Response
		 * @throws \Exception
		 */
		protected function doCreate(string $dtoClass, TransformerInterface $transformer): Response {
			$event = new PayloadInitEvent($dtoClass);
			$this->eventDispatcher->dispatch($event);

			$data = $event->getInstance();
			assert($this->checkPayloadInstanceNotNull($data));

			try {
				$entity = $transformer->create($data);
			} catch (\Exception $exception) {
				return $this->tryHandleException($exception);
			}

			$event = new EntityCreateEvent($entity, $data);
			$this->eventDispatcher->dispatch($event);

			if ($event->getShouldFlush()) {
				$this->entityManager->flush();

				$event = new PostEntityCreateEvent($entity, $data);
				$this->eventDispatcher->dispatch($event);

				if ($event->getShouldFlush())
					$this->entityManager->flush();
			}

			return $this->respond($entity);
		}

		protected function doUpdate(
			string $dtoClass,
			EntityInterface $entity,
			TransformerInterface $transformer,
		): Response {
			$event = new PayloadInitEvent($dtoClass);
			$this->eventDispatcher->dispatch($event);

			$data = $event->getInstance();
			assert($this->checkPayloadInstanceNotNull($data));

			try {
				$transformer->update($data, $entity);
			} catch (\Exception $exception) {
				$this->tryHandleException($exception);
			}

			$event = new EntityUpdateEvent($entity, $data);
			$this->eventDispatcher->dispatch($event);

			if ($event->getShouldFlush()) {
				$this->entityManager->flush();

				$event = new PostEntityUpdateEvent($entity, $data);
				$this->eventDispatcher->dispatch($event);

				if ($event->getShouldFlush())
					$this->entityManager->flush();
			}

			return $this->respond($entity);
		}

		protected function doDelete(EntityInterface $entity, TransformerInterface $transformer): Response {
			try {
				$transformer->delete($entity);
			} catch (\Exception $exception) {
				return $this->tryHandleException($exception);
			}

			$event = new EntityDeleteEvent($entity);
			$this->eventDispatcher->dispatch($event);

			if ($event->getShouldFlush())
				$this->entityManager->flush();

			return new Response(status: Response::HTTP_NO_CONTENT);
		}

		protected function doList(
			string $entityClass,
			?callable $configurator = null,
			string $alias = 'entity',
		): Response {
			$query = $this->entityManager->getRepository($entityClass)->createQueryBuilder($alias);
			$builder = new ListResponseBuilder($query);

			$event = new QueryInitEvent();
			$this->eventDispatcher->dispatch($event);

			if ($error = $event->getError())
				return $this->respond($error);

			$builder->setQueryDocument($event->getQuery());

			$event = new QueryLimitInitEvent();
			$this->eventDispatcher->dispatch($event);

			if ($error = $event->getError())
				return $this->respond($error);

			$builder->setLimit($event->getLimit());

			$event = new QueryOffsetInitEvent();
			$this->eventDispatcher->dispatch($event);

			if ($error = $event->getError())
				return $this->respond($error);

			$builder->setOffset($event->getOffset());

			if ($configurator !== null)
				$configurator($builder);

			$query = $builder->getQuery();

			if (null !== $limit = $builder->getLimit() && $query->getMaxResults() === null)
				$query->setMaxResults($limit);

			if (null !== $offset = $builder->getOffset() && $query->getFirstResult() === null)
				$query->setFirstResult($offset);

			try {
				$this->queryManager->apply($query, $builder->getQueryDocument());
			} catch (\Exception $exception) {
				return $this->tryHandleException($exception);
			}

			return $this->respond($query->getQuery()->getResult());
		}

		protected function tryHandleException(\Exception $exception): Response {
			if ($exception instanceof AsApiErrorInterface)
				$error = $exception->asApiError();
			else
				throw $exception; // if we can't transform to an ApiErrorInterface, just rethrow the exception

			return $this->respond($error);
		}

		protected function respond(mixed $data): Response {
			if ($data instanceof ApiErrorInterface)
				return $this->responseBuilder->createError($data);

			$event = new ProjectionInitEvent();
			$this->eventDispatcher->dispatch($event);

			$projection = $event->getProjection() ?? Projection::fromFields([]);

			return $this->responseBuilder->create(
				$data,
				context: (new ObjectNormalizerContextBuilder())
					->withProjection($projection)
					->withContext($this->createSerializerContext())
					->toArray(),
			);
		}

		protected function createSerializerContext(): ContextBuilderInterface|array {
			return [];
		}

		private function checkPayloadInstanceNotNull(?object $data): bool {
			if ($data === null)
				throw new NullPayloadException();

			return true;
		}
	}
