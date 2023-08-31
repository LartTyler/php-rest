<?php
	namespace DaybreakStudios\Rest\Event\Listeners\Controller;

	use DaybreakStudios\Rest\Event\Events\Controller\PayloadInitEvent;
	use DaybreakStudios\Rest\Event\Events\DefaultRequestFormatEvent;
	use DaybreakStudios\Rest\Event\Listeners\DefaultRequestFormatProvider;
	use DaybreakStudios\Rest\Transformer\Errors\ConstraintViolationError;
	use Psr\EventDispatcher\EventDispatcherInterface;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpFoundation\RequestStack;
	use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
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

			try {
				$instance = $this->serializer->deserialize(
					$request->getContent(),
					$event->getDtoClass(),
					$this->getDefaultFormat(),
				);
			} catch (PartialDenormalizationException $exception) {
				// If symfony/validator isn't installed, just rethrow the exception
				if (!class_exists('Symfony\Component\Validator\ConstraintViolationList'))
					throw $exception;

				$violations = new ConstraintViolationList();

				foreach ($exception->getErrors() as $error) {
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
	}
