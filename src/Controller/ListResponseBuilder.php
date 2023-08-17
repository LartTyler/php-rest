<?php
	namespace DaybreakStudios\Rest\Controller;

	use Doctrine\ORM\QueryBuilder;

	class ListResponseBuilder {
		public function __construct(
			protected QueryBuilder $query,
			protected ?array $queryDocument = null,
			protected ?int $limit = null,
			protected ?int $offset = null,
		) {}

		/**
		 * @return QueryBuilder
		 */
		public function getQuery(): QueryBuilder {
			return $this->query;
		}

		/**
		 * @param QueryBuilder $query
		 *
		 * @return static
		 */
		public function setQuery(QueryBuilder $query): static {
			$this->query = $query;
			return $this;
		}

		/**
		 * @return array|null
		 */
		public function getQueryDocument(): ?array {
			return $this->queryDocument;
		}

		/**
		 * @param array|null $queryDocument
		 *
		 * @return static
		 */
		public function setQueryDocument(?array $queryDocument): static {
			$this->queryDocument = $queryDocument;
			return $this;
		}

		/**
		 * @return int|null
		 */
		public function getLimit(): ?int {
			return $this->limit;
		}

		/**
		 * @param int|null $limit
		 *
		 * @return static
		 */
		public function setLimit(?int $limit): static {
			$this->limit = $limit;
			return $this;
		}

		/**
		 * @return int|null
		 */
		public function getOffset(): ?int {
			return $this->offset;
		}

		/**
		 * @param int|null $offset
		 *
		 * @return static
		 */
		public function setOffset(?int $offset): static {
			$this->offset = $offset;
			return $this;
		}
	}
