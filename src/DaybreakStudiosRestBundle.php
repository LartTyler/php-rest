<?php
	namespace DaybreakStudios\RestBundle;

	use DaybreakStudios\RestBundle\Error\AsApiErrorInterface;
	use DaybreakStudios\RestBundle\Event\Listeners\Controller\PayloadInitListener;
	use DaybreakStudios\RestBundle\Event\Listeners\Controller\PayloadInitValidationListener;
	use DaybreakStudios\RestBundle\Event\Listeners\Controller\ProjectionInitListener;
	use DaybreakStudios\RestBundle\Event\Listeners\Controller\QueryInitListener;
	use DaybreakStudios\RestBundle\Event\Listeners\Controller\QueryLimitInitListener;
	use DaybreakStudios\RestBundle\Event\Listeners\Controller\QueryOffsetInitListener;
	use DaybreakStudios\RestBundle\Event\Listeners\DefaultRequestFormatProvider;
	use DaybreakStudios\RestBundle\Event\Listeners\ErrorExceptionListener;
	use DaybreakStudios\RestBundle\Response\ResponseBuilder;
	use DaybreakStudios\RestBundle\Response\ResponseBuilderInterface;
	use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
	use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
	use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
	use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

	class DaybreakStudiosRestBundle extends AbstractBundle {
		public function configure(DefinitionConfigurator $definition): void {
			$root = $definition->rootNode()->children();

			// @formatter:off
			$root
				->scalarNode('serializer')
					->example('app.serializer')
					->info('The serializer service to use for REST endpoints')->end()
				->scalarNode('event_dispatcher')
					->defaultValue('event_dispatcher')
					->info('The event dispatcher to use for this bundle\'s events')->end()
				->scalarNode('validator')
					->example('@app.validator')
					->info('The validator service to use for payload and entity validation (if enabled)')->end()
				->booleanNode('wrap_error_exceptions')
					->defaultTrue()
					->info(
						'If true, uncaught exceptions implementing '
						. AsApiErrorInterface::class
						. ' will be converted to an error response',
					)->end()
				->scalarNode('fallback_format')
					->defaultValue('json')
					->info('The format to use if one cannot be determined')->end()
				->arrayNode('payload')->children()
					->booleanNode('enabled')
						->defaultTrue()
						->info('Toggles registration of the default `PayloadInitEvent` listener')->end()
					->booleanNode('validate')
						->defaultTrue()
						->info('Toggles registration of the default `PayloadInitValidationEvent` listener')->end()
				->end()
				->arrayNode('request')->children()
					->arrayNode('projection')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a projection object from the request')->end()
						->scalarNode('key')
							->defaultValue('p')
							->info('The request key to retrieve the projection object from')->end()
						->scalarNode('defaultMatchBehaviorKey')
							->defaultValue('_default')
							->info('The projection key to retrieve the default match behavior from (if set)')->end()
						->end()
					->arrayNode('query')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a query object from the request')->end()
						->scalarNode('key')
							->defaultValue('q')
							->info('The request key to retrieve the query object from')->end()
						->end()
					->arrayNode('limit')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a query limit from the request')->end()
						->scalarNode('key')
							->defaultValue('limit')
							->info('The request key to retrieve the query limit from')->end()
						->end()
					->arrayNode('offset')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a query offset from the request')->end()
						->scalarNode('key')
							->defaultValue('offset')
							->info('The request key to retrieve the query offset from')->end()
						->end();
			// @formatter:on
		}

		/**
		 * @param array                 $config
		 * @param ContainerConfigurator $container
		 * @param ContainerBuilder      $builder
		 *
		 * @return void
		 */
		public function loadExtension(
			array $config,
			ContainerConfigurator $container,
			ContainerBuilder $builder,
		): void {
			$serializer = service($config['serializer']);
			$eventDispatcher = service($config['event_dispatcher'] ?? 'event_dispatcher');

			$serviceBuilder = $container->services();

			$serviceBuilder
				->set('dbstudios_rest.response_builder', ResponseBuilder::class)
				->args([$serializer, $eventDispatcher])
				->alias(ResponseBuilderInterface::class, 'dbstudios_rest.response_builder');

			$serviceBuilder
				->set('dbstudios_rest.format_provider', DefaultRequestFormatProvider::class)
				->args([service('request_stack'), $config['fallback_format'] ?? 'json']);

			if ($config['wrap_error_exceptions'] ?? true) {
				$serviceBuilder
					->set('dbstudios.error_exception_handler', ErrorExceptionListener::class)
					->args([service('dbstudios_rest.response_builder')]);
			}

			if ($config['payload'] ?? null !== false && $config['payload']['enabled'] ?? true) {
				$serviceBuilder
					->set('dbstudios_rest.payload.init_listener', PayloadInitListener::class)
					->args([$serializer, service('request_stack'), $eventDispatcher]);

				if (($config['payload']['validate'] ?? true) && $config['validator']) {
					$serviceBuilder
						->set('dbstudios_rest.payload.validation_listener', PayloadInitValidationListener::class)
						->args([service($config['validator'])]);
				}
			}

			$this->initRequestServices($config['request'] ?? [], $serviceBuilder);
		}

		/**
		 * @param array                $config
		 * @param ServicesConfigurator $container
		 *
		 * @return void
		 */
		protected function initRequestServices(array $config, ServicesConfigurator $container): void {
			if ($config['projection'] ?? null !== false && $config['projection']['enabled'] ?? true) {
				$container
					->set('dbstudios_rest.request.projection_listener', ProjectionInitListener::class)
					->args(
						[
							service('request_stack'),
							$config['projection']['key'] ?? 'p',
							$config['projection']['defaultMatchBehaviorKey'] ?? '_default',
						],
					);
			}

			if ($config['query'] ?? null !== false && $config['query']['enabled'] ?? true) {
				$container
					->set('dbstudios_rest.request.query_listener', QueryInitListener::class)
					->args([service('request_stack'), $config['query']['key'] ?? 'q']);
			}

			if ($config['limit'] ?? null !== false && $config['query']['enabled'] ?? true) {
				$container
					->set('dbstudios_rest.request.limit_listener', QueryLimitInitListener::class)
					->args([service('request_stack'), $config['limit']['key'] ?? 'limit']);
			}

			if ($config['offset'] ?? null !== false && $config['offset']['enabled'] ?? true) {
				$container
					->set('dbstudios_rest.request.offset_listener', QueryOffsetInitListener::class)
					->args([service('request_stack'), $config['offset']['key'] ?? 'offset']);
			}
		}
	}
