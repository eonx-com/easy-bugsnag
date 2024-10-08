<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Configurator;

use Bugsnag\Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class SessionTrackingClientConfigurator extends AbstractClientConfigurator
{
    private readonly int $expiresAfter;

    public function __construct(
        private readonly CacheInterface $cache,
        ?int $expiresAfter = null,
        ?int $priority = null,
    ) {
        $this->expiresAfter = $expiresAfter ?? 3600;

        parent::__construct($priority);
    }

    public function configure(Client $bugsnag): void
    {
        $bugsnag->getSessionTracker()
            ->setStorageFunction(function (string $key, $value = null) {
                $callback = function (ItemInterface $item) use ($value) {
                    $item->expiresAfter($this->expiresAfter);

                    return $value;
                };

                // If value not null, we want to expire the item straight away to replace the value
                $beta = $value === null ? 0 : \INF;

                return $this->cache->get($key, $callback, $beta);
            });
    }
}
