<?php
	namespace DaybreakStudios\RestBundle\DependencyInjection\Compiler;

	use DaybreakStudios\RestBundle\Controller\SimpleCrudController;
	use DaybreakStudios\RestBundle\CrudRouteLoader;
	use DaybreakStudios\RestBundle\DaybreakStudiosRestBundle;
	use DaybreakStudios\RestBundle\Entity\AsCrudEntity;
	use DaybreakStudios\RestBundle\Entity\EntityLocator;
	use DaybreakStudios\RestBundle\Parameters;
	use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Definition;
	use Symfony\Component\DependencyInjection\Reference;

	class CrudRoutingPass implements CompilerPassInterface {
		public function __construct() {}

		public function process(ContainerBuilder $container): void {
			if (!$container->hasParameter(Parameters::ENTITIES))
				return;

			$container->addObjectResource(SimpleCrudController::class);

			$entities = $container->getParameter(Parameters::ENTITIES);
			$container->setDefinition(
				DaybreakStudiosRestBundle::PREFIX . 'crud.route_loader',
				(new Definition(CrudRouteLoader::class))
					->setArguments(
						[
							$entities,
							$container->getParameter(Parameters::USE_FORMAT_PARAM),
							$container->getParameter(Parameters::USE_LOCALIZED_ROUTES),
						],
					)
					->addTag('routing.route_loader'),
			);

			foreach ($entities as $path) {
				$locator = new EntityLocator($path);

				foreach ($locator as $class) {
					$attr = AsCrudEntity::getInstance($class);

					if (!$attr)
						continue;

					$def = (new Definition(SimpleCrudController::class))
						->setBindings(
							[
								'$entity' => $class,
								'$dtoClass' => $attr->dtoClass,
								'$firewallRole' => $attr->firewallRoles,
								'$allowedCrudMethods' => $attr->methods,
								'$transformer' => $attr->transformer ? new Reference($attr->transformer) : null,
								'$strict' => $attr->strict,
							],
						)
						->addTag('controller.service_arguments')
						->addTag('container.service_subscriber')
						->setPublic(true)
						->setAutoconfigured(true)
						->setAutowired(true);

					$container->setDefinition(AsCrudEntity::getEntityControllerName($class), $def);
					$container->addObjectResource($class);
				}
			}
		}
	}