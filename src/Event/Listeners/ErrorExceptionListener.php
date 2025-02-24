<?php
	namespace DaybreakStudios\RestBundle\Event\Listeners;

	use DaybreakStudios\RestBundle\Error\AsApiErrorInterface;
	use DaybreakStudios\RestBundle\Response\ResponseBuilderInterface;
	use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
	use Symfony\Component\HttpKernel\Event\ExceptionEvent;

	#[AsEventListener]
	class ErrorExceptionListener {
		public function __construct(
			protected ResponseBuilderInterface $responseBuilder,
		) {}

		public function __invoke(ExceptionEvent $event): void {
			$exception = $event->getThrowable();

			if (!($exception instanceof AsApiErrorInterface))
				return;

			$error = $exception->asApiError();
			$event->setResponse($this->responseBuilder->createError($error, context: $error->getContext() ?? []));
		}
	}
