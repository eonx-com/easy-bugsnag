<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Doctrine\Statement;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use EonX\EasyBugsnag\Doctrine\Logger\QueryBreadcrumbLogger;
use EonX\EasyBugsnag\Doctrine\ValueObject\QueryBreadcrumb;

final class BreadcrumbLoggerStatement extends AbstractStatementMiddleware
{
    private readonly QueryBreadcrumb $queryBreadcrumb;

    public function __construct(
        StatementInterface $statement,
        string $sql,
        private readonly QueryBreadcrumbLogger $queryBreadcrumbLogger,
        private readonly string $connectionName,
    ) {
        parent::__construct($statement);

        $this->queryBreadcrumb = new QueryBreadcrumb($sql, $this->connectionName);
    }

    /**
     * {@inheritdoc}
     */
    public function bindValue($param, mixed $value, $type = ParameterType::STRING): bool
    {
        $this->queryBreadcrumb->setQueryParameter($param, $value, $type);

        return parent::bindValue($param, $value, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($params = null): ResultInterface
    {
        // Clone to prevent variables by reference to change
        $sqlBreadcrumb = clone $this->queryBreadcrumb;

        try {
            return parent::execute($params);
        } finally {
            $this->queryBreadcrumbLogger->log($sqlBreadcrumb);
        }
    }
}
