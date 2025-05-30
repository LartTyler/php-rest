<?php
	namespace DaybreakStudios\RestBundle;

	use DaybreakStudios\DoctrineQueryDocument\QueryManager;
	use DaybreakStudios\DoctrineQueryDocument\QueryManagerInterface;
	use DaybreakStudios\RestBundle\Config\Config;
	use DaybreakStudios\RestBundle\Config\RequestConfig;
	use DaybreakStudios\RestBundle\DependencyInjection\Compiler\CrudRoutingPass;
	use DaybreakStudios\RestBundle\Entity\AsCrudEntity;
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
	use DaybreakStudios\RestBundle\Serializer\EntityDenormalizer;
	use DaybreakStudios\RestBundle\Serializer\ObjectNormalizer;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;
	use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
	use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
	use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
	use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

	class DaybreakStudiosRestBundle extends AbstractBundle {
		public const PREFIX = 'daybreak_studios_rest.';

		public function build(ContainerBuilder $container): void {
			parent::build($container);
			$container->addCompilerPass(new CrudRoutingPass());
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
			$config = new Config($config);

			if ($config->getCrudConfig()->isEnabled()) {
				$container->parameters()
					->set(Parameters::ENTITIES, $config->getCrudConfig()->getEntities())
					->set(Parameters::USE_FORMAT_PARAM, $config->getCrudConfig()->getUseFormatParam())
					->set(Parameters::USE_LOCALIZED_ROUTES, $config->getCrudConfig()->getUseLocalizedRoutes());
			}

			$services = $container->services();

			$services
				->set(self::PREFIX . 'response_builder', ResponseBuilder::class)
				->args([service($config->getSerializerId()), service($config->getEventDispatcherId())])
				->alias(ResponseBuilderInterface::class, self::PREFIX . 'response_builder');

			$services
				->set(self::PREFIX . 'query_manager', QueryManager::class)
				->args([service($config->getEntityManagerId())])
				->alias(QueryManagerInterface::class, self::PREFIX . 'query_manager');

			$services
				->set(self::PREFIX . 'format_provider', DefaultRequestFormatProvider::class)
				->args([service('request_stack'), $config->getFallbackFormat()])
				->tag('kernel.event_listener');

			$services
				->set(self::PREFIX . 'serializer.normalizer.object', ObjectNormalizer::class)
				->args(
					[
						service('serializer.normalizer.object'),
						service('serializer.mapping.class_metadata_factory'),
						service('serializer.name_converter.metadata_aware'),
						service('property_info')->ignoreOnInvalid(),
						service('serializer.mapping.class_discriminator_resolver')->ignoreOnInvalid(),
						null,
						[],
					],
				)
				->tag('serializer.normalizer');

			$services
				->set(self::PREFIX . 'serializer.denormalizer.entity', EntityDenormalizer::class)
				->args([service($config->getEntityManagerId())]);

			if ($config->getShouldWrapErrorExceptions()) {
				$services
					->set(self::PREFIX . 'error_exception_handler', ErrorExceptionListener::class)
					->args([service(self::PREFIX . 'response_builder')])
					->tag('kernel.event_listener');
			}

			if ($config->getPayloadConfig()->isEnabled()) {
				$services
					->set(self::PREFIX . 'payload.init_listener', PayloadInitListener::class)
					->args(
						[
							service($config->getSerializerId()),
							service('request_stack'),
							service($config->getEventDispatcherId()),
						],
					)
					->tag('kernel.event_listener');

				if ($config->getPayloadConfig()->isValidationEnabled() && $validatorId = $config->getValidatorId()) {
					$services
						->set(self::PREFIX . 'payload.validation_listener', PayloadInitValidationListener::class)
						->args([service($validatorId)])
						->tag('kernel.event_listener');
				}
			}

			$this->initRequestServices($config->getRequestConfig(), $services);
		}

		protected function initRequestServices(RequestConfig $config, ServicesConfigurator $services): void {
			if (($projection = $config->getProjectionConfig())->isEnabled()) {
				$services
					->set(self::PREFIX . 'request.projection_listener', ProjectionInitListener::class)
					->args(
						[service('request_stack'), $projection->getKey(), $projection->getDefaultMatchBehaviorKey()],
					)
					->tag('kernel.event_listener');
			}

			if ($config->getQueryConfig()->isEnabled()) {
				$services
					->set(self::PREFIX . 'request.query_listener', QueryInitListener::class)
					->args([service('request_stack'), $config->getQueryConfig()->getKey()])
					->tag('kernel.event_listener');
			}

			if ($config->getLimitConfig()->isEnabled()) {
				$services
					->set(self::PREFIX . 'request.limit_listener', QueryLimitInitListener::class)
					->args([service('request_stack'), $config->getLimitConfig()->getKey()])
					->tag('kernel.event_listener');
			}

			if ($config->getOffsetConfig()->isEnabled()) {
				$services
					->set(self::PREFIX . 'request.offset_listener', QueryOffsetInitListener::class)
					->args([service('request_stack'), $config->getOffsetConfig()->getKey()])
					->tag('kernel.event_listener');
			}
		}

		public function configure(DefinitionConfigurator $definition): void {
			$root = $definition->rootNode()->children();

			// @formatter:off
			$root
				->scalarNode('serializer')
					->example('@app.serializer')
					->info('The serializer service to use for REST endpoints');

			$root
				->scalarNode('event_dispatcher')
					->defaultValue('event_dispatcher')
					->info('The event dispatcher to use for this bundle\'s events');

			$root
				->scalarNode('@entity_manager')
					->defaultValue('doctrine.orm.default_entity_manager')
					->info('The preferred Doctrine entity manager to use');

			$root
				->scalarNode('validator')
					->example('@app.validator')
					->info('The validator service to use for payload and entity validation (if enabled)');

			$root
				->booleanNode('wrap_error_exceptions')
					->defaultTrue()
					->info(
						'If true, uncaught exceptions implementing '
						. AsApiErrorInterface::class
						. ' will be converted to an error response',
					);

			$root
				->scalarNode('fallback_format')
					->defaultValue('json')
					->info('The format to use if one cannot be determined');

			$root
				->arrayNode('payload')->children()
					->booleanNode('enabled')
						->defaultTrue()
						->info('Toggles registration of the default `PayloadInitEvent` listener')
						->end()
					->booleanNode('validate')
						->defaultTrue()
						->info('Toggles registration of the default `PayloadInitValidationEvent` listener');

			$root
				->arrayNode('request')->children()
					->arrayNode('projection')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a projection object from the request')
							->end()
						->scalarNode('key')
							->defaultValue('p')
							->info('The request key to retrieve the projection object from')
							->end()
						->scalarNode('defaultMatchBehaviorKey')
							->defaultValue('_default')
							->info('The projection key to retrieve the default match behavior from (if set)')
							->end()
						->end()
					->end()
					->arrayNode('query')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a query object from the request')
							->end()
						->scalarNode('key')
							->defaultValue('q')
							->info('The request key to retrieve the query object from')
							->end()
						->end()
					->end()
					->arrayNode('limit')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a query limit from the request')
							->end()
						->scalarNode('key')
							->defaultValue('limit')
							->info('The request key to retrieve the query limit from')
							->end()
						->end()
					->end()
					->arrayNode('offset')->children()
						->booleanNode('enabled')
							->defaultTrue()
							->info('Toggles parsing of a query offset from the request')
							->end()
						->scalarNode('key')
							->defaultValue('offset')
							->info('The request key to retrieve the query offset from');

			$root
				->arrayNode('crud')->children()
					->booleanNode('enabled')
						->defaultFalse()
						->info('Toggles automatic CRUD routing for tagged entities.')
						->end()
					->arrayNode('entities')
						->scalarPrototype()->end()
						->info(
							'An array of directories to scan for entities. Only concrete classes implementing ' .
							EntityInterface::class . ' and tagged with the ' . AsCrudEntity::class .
							' attribute will be processed.'
						)
						->end()
					->booleanNode('use_format_param')
						->defaultTrue()
						->info('If set, include a ".{_format}" param at the end of each generated route.')
						->end()
					->booleanNode('use_localized_routes')
						->defaultFalse()
						->info('If set, include a "/{_locale}" param at the start of each generated route.');
			// @formatter:on
		}
	}
