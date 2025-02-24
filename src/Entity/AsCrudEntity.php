<?php
	namespace DaybreakStudios\RestBundle\Entity;

	use DaybreakStudios\RestBundle\DaybreakStudiosRestBundle;
	use DaybreakStudios\Utility\DoctrineEntities\EntityInterface;

	#[\Attribute(\Attribute::TARGET_CLASS)]
	class AsCrudEntity {
		public const METHOD_LIST = 'list';
		public const METHOD_CREATE = 'create';
		public const METHOD_READ = 'read';
		public const METHOD_UPDATE = 'update';
		public const METHOD_DELETE = 'delete';

		protected const METHOD_ALL = '*';

		/**
		 * @var array<static::METHOD_*, true>
		 */
		public array $methods = [];

		/**
		 * @var array<static::METHOD_*, mixed>
		 */
		public array $firewallRoles = [];

		/**
		 * @param string                                        $basePath
		 * @param array<static::METHOD_*>|static::METHOD_*|null $method
		 * @param array<static::METHOD_*, mixed>|mixed|null     $firewallRole
		 * @param string|null                                   $transformer
		 * @param string|null                                   $dtoClass
		 * @param array|null                                    $strict
		 */
		public function __construct(
			public string $basePath,
			array|string|null $method = null,
			mixed $firewallRole = null,
			public ?string $transformer = null,
			public ?string $dtoClass = null,
			public ?array $strict = null,
		) {
			if ($method) {
				if (is_string($method))
					$this->methods = [$method => true];
				else {
					foreach ($method as $value)
						$this->methods[$value] = true;
				}
			}

			if ($firewallRole) {
				if (!is_array($firewallRole))
					$this->firewallRoles[static::METHOD_ALL] = $firewallRole;
				else
					$this->firewallRoles = $firewallRole;
			}
		}

		/**
		 * @param static::METHOD_* $method
		 *
		 * @return mixed
		 */
		public function getFirewallRole(string $method): mixed {
			return $this->firewallRoles[$method] ?? $this->firewallRoles[static::METHOD_ALL] ?? null;
		}

		public function isList(): bool {
			return $this->hasMethod(static::METHOD_LIST);
		}

		public function isCreate(): bool {
			return $this->hasMethod(static::METHOD_CREATE);
		}

		public function isRead(): bool {
			return $this->hasMethod(static::METHOD_READ);
		}

		public function isUpdate(): bool {
			return $this->hasMethod(static::METHOD_UPDATE);
		}

		public function isDelete(): bool {
			return $this->hasMethod(static::METHOD_DELETE);
		}

		protected function hasMethod(string $method): bool {
			return !$this->methods || isset($this->methods[$method]);
		}

		public static function getInstance(string $class): ?static {
			if (!class_exists($class) || !is_a($class, EntityInterface::class, true))
				return null;

			$refl = new \ReflectionClass($class);
			$attr = $refl->getAttributes(static::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

			return $attr?->newInstance();
		}

		public static function getEntityPrefix(string $class): string {
			return strtolower(str_replace('\\', '.', $class));
		}

		public static function getEntityControllerName(string $class): string {
			return DaybreakStudiosRestBundle::PREFIX . 'generated.crud.' . static::getEntityPrefix($class);
		}
	}