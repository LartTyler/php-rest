<?php
	namespace DaybreakStudios\RestBundle\DependencyInjection;

	use DaybreakStudios\RestBundle\Entity\AsCrudEntity;
	use DaybreakStudios\RestBundle\Entity\EntityLocator;
	use Symfony\Bundle\FrameworkBundle\Routing\RouteLoaderInterface;
	use Symfony\Component\Routing\Loader\Configurator\CollectionConfigurator;
	use Symfony\Component\Routing\RouteCollection;

	class CrudRouteLoader implements RouteLoaderInterface {
		public function __construct(
			protected array $entityDirs,
			protected bool $useFormatParam = true,
			protected ?array $prefixes = null,
		) {}

		public function __invoke(): RouteCollection {
			$collection = new RouteCollection();
			$builder = new CollectionConfigurator($collection, '');

			if ($this->prefixes)
				$builder->prefix($this->prefixes);

			foreach ($this->entityDirs as $entityDir) {
				$locator = new EntityLocator($entityDir);

				foreach ($locator as $class) {
					$attr = AsCrudEntity::getInstance($class);

					if (!$attr)
						continue;

					$prefix = 'dbstudios_rest.crud.generated.' . AsCrudEntity::getEntityPrefix($class);
					$controller = AsCrudEntity::getEntityControllerName($class);

					$path = $attr->basePath;

					if ($this->useFormatParam)
						$path .= '.{_format}';

					if ($attr->isList()) {
						$builder->add($prefix . '.list', $path)
							->methods(['GET'])
							->controller([$controller, 'list']);
					}

					if ($attr->isCreate()) {
						$builder->add($prefix . '.create', $path)
							->methods(['PUT'])
							->controller([$controller, 'create']);
					}

					$path = $attr->basePath . '/{entity<\d+>}';

					// DELETE is added before possibly adding the _format param since it doesn't make sense to set a
					// format on a 204 No Content response.
					if ($attr->isDelete()) {
						$builder->add($prefix . '.delete', $path)
							->methods(['DELETE'])
							->controller([$controller, 'delete']);
					}

					if ($this->useFormatParam)
						$path .= '.{_format}';

					if ($attr->isRead()) {
						$builder->add($prefix . '.read', $path)
							->methods(['GET'])
							->controller([$controller, 'read']);
					}

					if ($attr->isUpdate()) {
						$builder->add($prefix . ' .update', $path)
							->methods(['PATCH'])
							->controller([$controller, 'update']);
					}
				}
			}

			return $collection;
		}
	}