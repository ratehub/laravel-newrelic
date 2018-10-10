<?php

namespace RateHub\NewRelic\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Log\LogManager;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use RateHub\NewRelic\Adapters\LogAdapter;
use RateHub\NewRelic\Adapters\NewRelicAgentAdapter;
use RateHub\NewRelic\Adapters\NullAdapter;
use RateHub\NewRelic\Commands\TestCommand;
use RateHub\NewRelic\Contracts\Adapters\Adapter;

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

        $app = $this->app;

        /** @var Repository $config */
        $config = $app->make('config');

        switch ($config->get('newrelic.adapter')) {
            case 'null':
                $app->bind(Adapter::class, NullAdapter::class);
                break;

            case 'newrelic':
                $throwWhenMissing = config('newrelic.throw_when_missing', true);

                if (!extension_loaded('newrelic') && $throwWhenMissing) {
                    throw new \Exception('New Relic PHP Agent is missing.');
                }

                $app->bind(Adapter::class, NewRelicAgentAdapter::class);
                break;

            case 'log':
                $app->bind(Adapter::class, LogAdapter::class, true);
                $app
                    ->when(LogAdapter::class)
                    ->needs(LoggerInterface::class)
                    ->give(function () use ($app, $config) {
                        return $app->make(LogManager::class)->channel($config->get('newrelic.adapters.log.channel'));
                    });
                break;
        }
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
        ];
    }

    /**
     * Registers the named transactions with the NewRelic PHP agent
     */
    protected function registerNamedTransactions()
    {
        $app = $this->app;

        if ($app['config']->get('newrelic.auto_name_transactions')) {
            $app['events']->listen(RouteMatched::class, function (RouteMatched $routeMatched) use ($app) {
                $app[Adapter::class]->nameTransaction($this->getTransactionName());
            });
        }
    }

    /**
     * Registers the queue transactions with the NewRelic PHP agent
     */
    protected function registerQueueTransactions()
    {
        $app = $this->app;

        $app['queue']->before(function ($event) use ($app) {
            $app[Adapter::class]->backgroundJob(true);
            $app[Adapter::class]->startTransaction(ini_get('newrelic.appname'));
            if ($app['config']->get('newrelic.auto_name_jobs')) {
                $app[Adapter::class]->nameTransaction($this->getJobName($event));
            }
        });

        $app['queue']->after(function ($event) use ($app) {
            $app[Adapter::class]->endTransaction();
        });
    }

    /**
     * Build the transaction name
     *
     * @return string
     */
    public function getTransactionName()
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
            $this->app['config']->get('newrelic.name_provider')
        );
    }

    /**
     * Build the job name
     *
     * @return string
     */
    public function getJobName($event)
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
            $this->app['config']->get('newrelic.job_name_provider')
        );
    }

    /**
     * Get the request method
     *
     * @return string
     */
    protected function getMethod()
    {
        return strtoupper($this->app['router']->getCurrentRequest()->method());
    }

    /**
     * Get the request URI path
     *
     * @return string
     */
    protected function getPath()
    {
        return ($this->app['router']->current()->uri() == '' ? '/' : $this->app['router']->current()->uri());
    }

    protected function getUri()
    {
        return $this->app['router']->getCurrentRequest()->path();
    }

    /**
     * Get the current controller / action
     *
     * @return string
     */
    protected function getController()
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
    protected function getRoute()
    {
        $name = $this->app['router']->currentRouteName();
        if (!$name) {
            $name = $this->getController();
        }

        return $name;
    }
}
