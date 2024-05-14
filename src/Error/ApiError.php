<?php
	namespace DaybreakStudios\RestBundle\Error;

	use Symfony\Component\Serializer\Annotation\Ignore;

	class ApiError implements ApiErrorInterface {
		public function __construct(
			protected string $code,
			protected string $message,

			#[Ignore]
			protected ?int $httpStatus = null,
			protected array $context = [],

			#[Ignore]
			protected ?array $httpHeaders = null,
		) {}

		/**
		 * @return string
		 */
		public function getCode(): string {
			return $this->code;
		}

		/**
		 * @return string
		 */
		public function getMessage(): string {
			return $this->message;
		}

		/**
		 * @return int|null
		 */
		#[Ignore]
		public function getHttpStatus(): ?int {
			return $this->httpStatus;
		}

		/**
		 * @return array
		 */
		public function getContext(): array {
			return $this->context;
		}

		#[Ignore]
		public function getHttpHeaders(): ?array {
			return $this->httpHeaders;
		}
	}
