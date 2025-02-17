<?php

declare(strict_types=1);

namespace AlephTools\DDD\Common\Application;

use AlephTools\DDD\Common\Infrastructure\ApplicationContext;
use AlephTools\DDD\Common\Infrastructure\Async;
use AlephTools\DDD\Common\Infrastructure\DomainEventPublisher;

abstract class AbstractApplicationService
{
    /**
     * @param mixed $callback
     */
    protected function runAsync($callback, array $params = []): void
    {
        $this->async()->run($callback, $params);
    }

    /**
     * @return mixed
     */
    protected function executeAtomically(callable $callback)
    {
        return $this->unitOfWork()->execute($callback);
    }

    protected function async(): Async
    {
        return ApplicationContext::get(Async::class);
    }

    protected function unitOfWork(): UnitOfWork
    {
        return ApplicationContext::get(UnitOfWork::class);
    }

    protected function eventPublisher(): DomainEventPublisher
    {
        return ApplicationContext::get(DomainEventPublisher::class);
    }
}
