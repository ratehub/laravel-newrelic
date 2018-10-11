<?php

namespace RateHub\NewRelic\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Log\LogManager;
use Illuminate\Queue\Queue;
use Illuminate\Queue\QueueManager;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use RateHub\NewRelic\Adapters\LogAdapter;
use RateHub\NewRelic\Adapters\NewRelicAgentAdapter;
use RateHub\NewRelic\Adapters\NullAdapter;
use RateHub\NewRelic\Commands\TestCommand;
use RateHub\NewRelic\Contracts\Adapters\Adapter;
use RateHub\NewRelic\Contracts\Exceptions\ExceptionFilter;
use RateHub\NewRelic\DetailProcessors\StackProcessor;
use RateHub\NewRelic\Exceptions\AggregateExceptionFilter;
use RateHub\NewRelic\Exceptions\BlacklistExceptionFilter;
use RateHub\NewRelic\Exceptions\ExceptionHandler;

const NEW_RELIC_CONFIG_PATH = __DIR__ . '/../../../../config/newrelic.php';

final class NewRelicServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Overwriting the type for code completion
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([realpath(NEW_RELIC_CONFIG_PATH) => config_path('newrelic.php')], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                TestCommand::class,
            ]);
        }

        $this->registerNamedTransactions();
        $this->registerQueueTransactions();
    }

    /**
     * Register the service provider.
     *
     * @return void
     * @throws \Exception When New Relic isn't installed and throw_when_missing is set to true.
     */
    public function register()
    {
        $this->mergeConfigFrom(realpath(NEW_RELIC_CONFIG_PATH), 'newrelic');

        $this->registerAdapter($this->app->make('config')->get('newrelic.adapter'));
        $this->registerExceptionFilters();
        $this->registerExceptionHandler();
        $this->registerDetailProcessors();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Adapter::class,
            AggregateExceptionFilter::class,
            BlacklistExceptionFilter::class,
            ExceptionFilter::class,
        ];
    }

    /**
     * Registers the named transactions with the NewRelic PHP agent
     */
    private function registerNamedTransactions()
    {
        $app = $this->app;

        if ($app['config']->get('newrelic.autoNameTransactions')) {
            $app['events']->listen(RouteMatched::class, function (RouteMatched $routeMatched) use ($app) {
                $app[Adapter::class]->nameTransaction($this->getTransactionName());
            });
        }
    }

    /**
     * Registers the queue transactions with the NewRelic PHP agent
     */
    private function registerQueueTransactions()
    {
        $app = $this->app;
        /** @var QueueManager $queueManager */
        $queueManager = $app->make('queue');
        $queueManager->before(function ($event) use ($app) {
            $app->make(Adapter::class)->backgroundJob(true);
            $app->make(Adapter::class)->startTransaction(ini_get('newrelic.appname'));
            if ($app->make('config')->get('newrelic.autoNameJobs')) {
                $app->make(Adapter::class)->nameTransaction($this->getJobName($event));
            }
        });

        $queueManager->after(function ($event) use ($app) {
            $app->make(Adapter::class)->endTransaction();
        });

        $queueManager->failing(function ($event) use ($app) {
            $app->make(ExceptionHandler::class)->report($event->exception);
        });
    }

    private function registerAdapter(string $adapter)
    {
        /** @var Repository $config */
        $config = $this->app->make('config');

        switch ($adapter) {
            case 'nullAdapter':
                $this->app->bind(Adapter::class, NullAdapter::class);
                break;

            case 'newrelic':
                $throwWhenMissing = config('newrelic.throwWhenMissing', true);

                if (extension_loaded('newrelic')) {
                    $this->app->bind(Adapter::class, NewRelicAgentAdapter::class);
                } else {
                    if ($throwWhenMissing) {
                        throw new \Exception('New Relic PHP Agent is missing.');
                    } else {
                        $this->registerAdapter($config->get('newrelic.fallback'));
                    }
                }

                break;
            case 'log':
                $logManager = $this->app->make('log');
                $this->app->singleton(Adapter::class, LogAdapter::class);
                $this->app
                    ->when(LogAdapter::class)
                    ->needs(LoggerInterface::class)
                    ->give(function () use ($config, $logManager) {
                        return $logManager->channel($config->get('newrelic.adapters.log.channel'));
                    });
                break;

            default:
                throw new \Exception('Invalid adapter specified.');
        }
    }

    private function registerExceptionFilters()
    {
        $this->app->singleton(BlacklistExceptionFilter::class, function () {
            /** @var Repository $config */
            $config = $this->app->make('config');

            return new BlacklistExceptionFilter($config->get('newrelic.filters.blacklist.ignoredExceptions', []));
        });

        $this->app->singleton(AggregateExceptionFilter::class, function () {
            /** @var Repository $config */
            $config = $this->app->make('config');

            $filters = [];
            foreach ($config->get('newrelic.filters.aggregate.filters', []) as $filterClass) {
                $filters[] = $this->app->make($filterClass);
            }

            return new AggregateExceptionFilter($filters);
        });
    }

    private function registerDetailProcessors()
    {
        $this->app->singleton(StackProcessor::class, function () {
            /** @var Repository $config */
            $config = $this->app->make('config');

            $filters = [];
            foreach ($config->get('newrelic.detailProcessors.stack.processors', []) as $filterClass) {
                $filters[] = $this->app->make($filterClass);
            }

            return new AggregateExceptionFilter($filters);
        });
    }

    private function registerExceptionHandler()
    {
        $this->app->singleton(ExceptionHandler::class, function () {
            /** @var Repository $config */
            $config = $this->app->make('config');

            $adapter = $this->app->make(Adapter::class);
            $detailProcessor = $this->app->make($config->get('newrelic.detailProcessor'));
            $exceptionFilter = $this->app->make(ExceptionFilter::class);

            return new ExceptionHandler($detailProcessor, $adapter, $exceptionFilter);
        });
    }

    /**
     * Build the transaction name
     *
     * @return string
     */
    private function getTransactionName()
    {
        return str_replace(
            [
                '{controller}',
                '{method}',
                '{route}',
                '{path}',
                '{uri}',
            ],
            [
                $this->getController(),
                $this->getMethod(),
                $this->getRoute(),
                $this->getPath(),
                $this->getUri(),
            ],
            $this->app['config']->get('newrelic.nameProvider')
        );
    }

    /**
     * Build the job name
     *
     * @return string
     */
    private function getJobName($event)
    {
        return str_replace(
            [
                '{connection}',
                '{class}',
            ],
            [
                $event->connectionName,
                get_class($event->job),
            ],
            $this->app['config']->get('newrelic.jobNameProvider')
        );
    }

    /**
     * Get the request method
     *
     * @return string
     */
    private function getMethod()
    {
        return strtoupper($this->app['router']->getCurrentRequest()->method());
    }

    /**
     * Get the request URI path
     *
     * @return string
     */
    private function getPath()
    {
        return ($this->app['router']->current()->uri() == '' ? '/' : $this->app['router']->current()->uri());
    }

    private function getUri()
    {
        return $this->app['router']->getCurrentRequest()->path();
    }

    /**
     * Get the current controller / action
     *
     * @return string
     */
    private function getController()
    {
        $controller = $this->app['router']->current() ? $this->app['router']->current()->getActionName() : 'unknown';
        if ($controller === 'Closure') {
            $controller .= '@' . $this->getPath();
        }

        return $controller;
    }

    /**
     * Get the current route name, or controller if not named
     *
     * @return string
     */
    private function getRoute()
    {
        $name = $this->app['router']->currentRouteName();
        if (!$name) {
            $name = $this->getController();
        }

        return $name;
    }
}
