<?php
	namespace DaybreakStudios\Rest\Response;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use DaybreakStudios\Rest\Event\Events\DefaultRequestFormatEvent;
	use Psr\EventDispatcher\EventDispatcherInterface;
	use Symfony\Component\HttpFoundation\Response;
	use Symfony\Component\Serializer\SerializerInterface;

	class ResponseBuilder implements ResponseBuilderInterface {
		public function __construct(
			protected SerializerInterface $serializer,
			protected EventDispatcherInterface $eventDispatcher,
		) {}

		public function create(mixed $data, int $status = null, array $headers = [], array $context = []): Response {
			if ($data === null && $status === null)
				$status = Response::HTTP_NO_CONTENT;

			$format = $this->getFormat($context);

			if ($data !== null)
				$data = $this->serializer->serialize($data, $format, $context);

			return new Response(
				$data,
				$status ?? Response::HTTP_OK,
				$headers + [
					'Content-Type' => 'application/' . $format,
				]
			);
		}

		public function createError(
			ApiErrorInterface $error,
			int $status = null,
			array $headers = [],
			array $context = [],
		): Response {
			if ($status === null)
				$status = $error->getHttpStatus() ?? Response::HTTP_BAD_REQUEST;

			return $this->create(
				[
					'error' => $error,
				],
				$status,
				$headers,
				$context,
			);
		}

		protected function getFormat(array $context): string {
			return $context[ResponseBuilderContextBuilder::SERIALIZER_FORMAT] ?? $this->getDefaultFormat();
		}

		protected function getDefaultFormat(): string {
			$event = new DefaultRequestFormatEvent();
			$this->eventDispatcher->dispatch($event);

			return $event->getDefaultFormat();
		}
	}
