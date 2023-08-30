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
	use DaybreakStudios\Rest\Event\Events\Entity\EntityCloneEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\EntityCreateEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\EntityDeleteEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\EntityUpdateEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\PostEntityCloneEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\PostEntityCreateEvent;
	use DaybreakStudios\Rest\Event\Events\Entity\PostEntityUpdateEvent;
	use DaybreakStudios\Rest\Payload\Intent;
	use DaybreakStudios\Rest\Response\ResponseBuilderInterface;
	use DaybreakStudios\Rest\Serializer\ObjectNormalizerContextBuilder;
	use DaybreakStudios\Rest\Transformer\TransformerInterface;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Doctrine\ORM\EntityManagerInterface;
	use Doctrine\ORM\QueryBuilder;
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
		 * @param class-string<T>               $dtoClass
		 * @param TransformerInterface          $transformer
		 * @param ContextBuilderInterface|array $context serializer context
		 *
		 * @return Response
		 */
		protected function doCreate(
			string $dtoClass,
			TransformerInterface $transformer,
			ContextBuilderInterface|array $context = [],
		): Response {
			$event = new PayloadInitEvent($dtoClass, [Intent::CREATE]);
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

			return $this->respond($entity, $context);
		}

		/**
		 * @param string                        $dtoClass the class to deserialize the request body into
		 * @param EntityInterface               $entity
		 * @param TransformerInterface          $transformer
		 * @param ContextBuilderInterface|array $context  serializer context
		 *
		 * @return Response
		 */
		protected function doUpdate(
			string $dtoClass,
			EntityInterface $entity,
			TransformerInterface $transformer,
			ContextBuilderInterface|array $context = [],
		): Response {
			$event = new PayloadInitEvent($dtoClass, [Intent::UPDATE]);
			$this->eventDispatcher->dispatch($event);

			$data = $event->getInstance();
			assert($this->checkPayloadInstanceNotNull($data));

			try {
				$transformer->update($data, $entity);
			} catch (\Exception $exception) {
				return $this->tryHandleException($exception);
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

			return $this->respond($entity, $context);
		}

		/**
		 * @param EntityInterface               $entity
		 * @param TransformerInterface          $transformer
		 * @param string|null                   $dtoClass the fully-qualified class name to deserialize the request
		 *                                                body into; if `null`, the request body will be ignored and
		 *                                                your transformer will not receive a payload object
		 * @param ContextBuilderInterface|array $context  serializer context
		 *
		 * @return Response
		 */
		protected function doClone(
			EntityInterface $entity,
			TransformerInterface $transformer,
			string $dtoClass = null,
			ContextBuilderInterface|array $context = [],
		): Response {
			if ($dtoClass !== null) {
				$event = new PayloadInitEvent($dtoClass, [Intent::CLONE]);
				$this->eventDispatcher->dispatch($event);

				$data = $event->getInstance();
				assert($this->checkPayloadInstanceNotNull($data));
			} else
				$data = null;

			try {
				$cloned = $transformer->clone($entity, $data);
			} catch (\Exception $exception) {
				return $this->tryHandleException($exception);
			}

			$event = new EntityCloneEvent($entity, $cloned, $data);
			$this->eventDispatcher->dispatch($event);

			if ($event->getShouldFlush()) {
				$this->entityManager->flush();

				$event = new PostEntityCloneEvent($entity, $cloned, $data);
				$this->eventDispatcher->dispatch($event);

				if ($event->getShouldFlush())
					$this->entityManager->flush();
			}

			return $this->respond($cloned, $context);
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

		/**
		 * @param string                        $entityClass    the fully-qualified class name of the root entity for
		 *                                                      the query
		 * @param string                        $alias          the alias to use for the root entity in the
		 *                                                      {@see QueryBuilder}
		 * @param array                         $queryOverrides an array of parameters to apply on top of any query
		 *                                                      document provided by the API client
		 * @param ContextBuilderInterface|array $context        serializer context
		 *
		 * @return Response
		 */
		protected function doList(
			string $entityClass,
			string $alias = 'entity',
			array $queryOverrides = [],
			ContextBuilderInterface|array $context = [],
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

			$query = $builder->getQuery();

			if (null !== $limit = $builder->getLimit() && $query->getMaxResults() === null)
				$query->setMaxResults($limit);

			if (null !== $offset = $builder->getOffset() && $query->getFirstResult() === null)
				$query->setFirstResult($offset);

			try {
				$this->queryManager->apply($query, $queryOverrides + $builder->getQueryDocument());
			} catch (\Exception $exception) {
				return $this->tryHandleException($exception);
			}

			return $this->respond($query->getQuery()->getResult(), $context);
		}

		protected function tryHandleException(\Exception $exception): Response {
			if ($exception instanceof AsApiErrorInterface)
				$error = $exception->asApiError();
			else
				throw $exception; // if we can't transform to an ApiErrorInterface, just rethrow the exception

			return $this->respond($error);
		}

		/**
		 * @param mixed                         $data
		 * @param ContextBuilderInterface|array $context serializer context
		 *
		 * @return Response
		 */
		protected function respond(mixed $data, ContextBuilderInterface|array $context = []): Response {
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
					->withContext($context)
					->toArray(),
			);
		}

		/**
		 * Should return context arguments that should be applied to every response generated by this controller.
		 *
		 * For per-endpoint context arguments, use the corresponding method's `$context` argument.
		 *
		 * @return ContextBuilderInterface|array
		 */
		protected function createSerializerContext(): ContextBuilderInterface|array {
			return [];
		}

		private function checkPayloadInstanceNotNull(?object $data): bool {
			if ($data === null)
				throw new NullPayloadException();

			return true;
		}
	}
