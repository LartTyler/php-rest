<?php
	namespace DaybreakStudios\RestBundle\DependencyInjection;

	use DaybreakStudios\RestBundle\Config\CrudConfig;
	use DaybreakStudios\RestBundle\Controller\SimpleCrudController;
	use DaybreakStudios\RestBundle\Entity\AsCrudEntity;
	use DaybreakStudios\RestBundle\Entity\EntityLocator;
	use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Definition;

	class CrudRoutingPass implements CompilerPassInterface {
		public function __construct(
			protected CrudConfig $config,
		) {}

		public function process(ContainerBuilder $container): void {
			$container->addObjectResource(SimpleCrudController::class);

			$container->setDefinition(
				'dbstudios_rest.crud.route_loader',
				(new Definition(CrudRouteLoader::class))
					->setArguments(
						[
							$this->config->getEntities(),
							$this->config->getUseFormatParam(),
							$this->config->getPrefixes(),
						],
					),
			);

			foreach ($this->config->getEntities() as $path) {
				$locator = new EntityLocator($path);

				foreach ($locator as $class) {
					$attr = AsCrudEntity::getInstance($class);

					if (!$attr)
						continue;

					$def = (new Definition(SimpleCrudController::class))
						->setBindings(
							[
								'entity' => $class,
								'dtoClass' => $attr->dtoClass,
								'firewallRole' => $attr->firewallRoles,
								'allowedCrudMethods' => $attr->methods,
								'transformer' => $attr->transformer,
								'strict' => $attr->strict,
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