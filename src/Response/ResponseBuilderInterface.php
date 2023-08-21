<?php
	namespace DaybreakStudios\Rest\Response;

	use DaybreakStudios\Rest\Error\ApiErrorInterface;
	use Symfony\Component\HttpFoundation\Response;

	interface ResponseBuilderInterface {
		/**
		 * Special serializer context key that can be used to force a response format during serialization. If not
		 * provided, it will be up to the implementation to infer a serialization format.
		 */
		const CONTEXT_SERIALIZER_FORMAT = 'dbstudios.serializer_format';

		/**
		 * Creates a new response, following some common rules.
		 *
		 * If `$data` is `null`, no body content will be set. If `$status` is `null`, then 204 No Content will be sent.
		 *
		 * If `$data` is any other value, it will be passed to the configured `symfony/serializer` instanced before
		 * being set as the body content.
		 *
		 * Also see {@see ResponseBuilderInterface::CONTEXT_SERIALIZER_FORMAT} for information on forcing serialization
		 * formats.
		 *
		 * @param mixed    $data    The data to send
		 * @param int|null $status  The HTTP status code for the response
		 * @param array    $headers An array of headers to send; values provided here will take priority over any
		 *                          default or inferred headers
		 * @param array    $context An array containing cont ext options for the serializer
		 *
		 * @return Response
		 */
		public function create(
			mixed $data,
			int $status = null,
			array $headers = [],
			array $context = [],
		): Response;

		/**
		 * Creates a new error response.
		 *
		 * Error responses follow a stable format to make error detection easier. Bodies will always have a top level
		 * `error` key which contains the error data in a similar format to Symfony's built-in exception serialization
		 * format. For example, in JSON:
		 *
		 * ```
		 * {
		 *     "error": {
		 *         "code": "not_found",
		 *         "message": "Resource not found."
		 *     }
		 * }
		 * ```
		 *
		 * See {@see ResponseBuilderInterface::create()} for information on method arguments.
		 *
		 * @param ApiErrorInterface $error
		 * @param int|null          $status
		 * @param array             $headers
		 * @param array             $context
		 *
		 * @return Response
		 */
		public function createError(
			ApiErrorInterface $error,
			int $status = null,
			array $headers = [],
			array $context = [],
		): Response;
	}
