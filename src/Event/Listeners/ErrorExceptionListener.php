<?php
	namespace DaybreakStudios\Rest\Event\Listeners;

	use DaybreakStudios\Rest\Error\AsApiErrorInterface;
	use DaybreakStudios\Rest\Response\ResponseBuilderInterface;
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
