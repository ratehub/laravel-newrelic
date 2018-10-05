<?php

namespace RateHub\NewRelic;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Log\LogManager;
use RateHub\NewRelic\Adapters\LogAdapter;
use RateHub\NewRelic\Adapters\NewRelicAgentAdapter;
use RateHub\NewRelic\Adapters\NullAdapter;
use RateHub\NewRelic\Contracts\Adapters\Adapter;

final class AdapterFactory
{
    /** @var Repository */
    private $config;

    public function __construct(LogManager $logManager, Repository $config)
    {
        $this->config = $config;
        $this->logManager = $logManager;
    }

    /**
     * @param string $driver
     *
     * @return Adapter
     * @throws \Exception
     */
    public function make(string $driver): Adapter
    {
        switch ($driver) {
            case 'null':
                return new NullAdapter();
            case 'newrelic':
                return new NewRelicAgentAdapter();
            case 'log':
                $logger = $this->logManager->channel(
                    $this->config->get('newrelic.adapters.log.channel')
                );
                return new LogAdapter($logger);
        }

        throw new \Exception('Invalid driver');
    }
}
