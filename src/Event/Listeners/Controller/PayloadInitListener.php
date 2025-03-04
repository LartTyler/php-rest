<?php
	namespace DaybreakStudios\RestBundle\Event\Listeners\Controller;

	use DaybreakStudios\RestBundle\Error\Errors\Controller\EmptyPayloadError;
	use DaybreakStudios\RestBundle\Event\Events\Controller\PayloadInitEvent;
	use DaybreakStudios\RestBundle\Event\Events\DefaultRequestFormatEvent;
	use DaybreakStudios\RestBundle\Event\Listeners\DefaultRequestFormatProvider;
	use DaybreakStudios\RestBundle\Transformer\Errors\ConstraintViolationError;
	use Psr\EventDispatcher\EventDispatcherInterface;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
	use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
	use Symfony\Component\Serializer\Exception\UnexpectedValueException;
	use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
	use Symfony\Component\Serializer\SerializerInterface;
	use Symfony\Component\Validator\Constraints\Type as TypeConstraint;
	use Symfony\Component\Validator\ConstraintViolationList;
	use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

	#[AsEventListener]
	class PayloadInitListener {
		public function __construct(
			protected SerializerInterface $serializer,
			protected RequestStack $requestStack,
			protected EventDispatcherInterface $eventDispatcher,
		) {}

		public function __invoke(PayloadInitEvent $event): void {
			$request = $this->requestStack->getCurrentRequest();
			$content = $this->getRawPayloadFromRequest($request);

			if ($content === null) {
				$event->setError(new EmptyPayloadError());
				return;
			}

			try {
				$instance = $this->serializer->deserialize(
					$content,
					$event->getDtoClass(),
					$this->getDefaultFormat(),
					[
						DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
					],
				);
			} catch (UnexpectedValueException $exception) {
				// If symfony/validator isn't installed, just rethrow the exception
				if (!class_exists('Symfony\Component\Validator\ConstraintViolationList'))
					throw $exception;

				$violations = new ConstraintViolationList();

				foreach ($this->extractErrorsFromException($exception) as $error) {
					// The following code is adapted from Symfony\Component\Validator\Validator\RecursiveValidator and
					// Symfony\Component\Validator\Constraints\TypeValidator in order to mimic the behavior of a "real"
					// validator constraint violation.

					$constraint = new TypeConstraint($error->getExpectedTypes());

					// Both $root and $invalidValue are `null` below because we don't know those two values at this
					// point. At the time of writing, those two values seem to only be used when invoking
					// ConstraintViolation::__toString(), which we don't use in order to build the final error object.
					$builder = new ConstraintViolationBuilder(
						$violations,
						$constraint,
						$constraint->message,
						[],
						null,
						$error->getPath(),
						null,
						TranslatorStub::instance(),
					);

					$builder
						->setParameter('{{ type }}', implode('|', (array)$constraint->type))
						->setCode(TypeConstraint::INVALID_TYPE_ERROR)
						->addViolation();
				}

				// On the off-chance that we couldn't build any violations, rethrow the exception and let someone else
				// deal with it.
				if ($violations->count() === 0)
					throw $exception;

				$event->setError(new ConstraintViolationError($violations));

				return;
			}

			$event->setInstance($instance);
		}

		protected function getDefaultFormat(): string {
			$event = new DefaultRequestFormatEvent();
			$this->eventDispatcher->dispatch($event);

			if (null === $format = $event->getDefaultFormat()) {
				throw new \InvalidArgumentException(
					'Could not determine response format; did you forget to register '
					. DefaultRequestFormatProvider::class
					. '?',
				);
			}

			return $format;
		}

		protected function getRawPayloadFromRequest(Request $request): ?string {
			return $request->getContent() ?: null;
		}

		/**
		 * @param UnexpectedValueException $exception
		 *
		 * @return NotNormalizableValueException[]
		 */
		protected function extractErrorsFromException(UnexpectedValueException $exception): array {
			return match (true) {
				$exception instanceof NotNormalizableValueException => [$exception],
				$exception instanceof PartialDenormalizationException => $exception->getErrors(),

				// If we can't extract the type we want, just rethrow the exception and let someone else deal with it
				default => throw $exception,
			};
		}
	}
