<?php

namespace RateHub\NewRelic\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\ServiceProvider;
use RateHub\NewRelic\Adapters\NullAdapter;

const NEW_RELIC_CONFIG_PATH = __DIR__ . '../../../../config/newrelic.php';

final class NewRelicServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([realpath(NEW_RELIC_CONFIG_PATH) => config_path('newrelic.php')], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(realpath(NEW_RELIC_CONFIG_PATH), 'newrelic');

        $this->app->singleton(
            'newrelic',
            function ($app) {
                return new NullAdapter();
            }
        );

        $this->registerNamedTransactions();
        $this->registerQueueTransactions();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['newrelic'];
    }

    /**
     * Registers the named transactions with the NewRelic PHP agent
     */
    protected function registerNamedTransactions()
    {
        $app = $this->app;

        if ($app['config']->get('newrelic.auto_name_transactions')) {
            $app['events']->listen(RouteMatched::class, function (RouteMatched $routeMatched) use ($app) {
                $app['newrelic']->nameTransaction($this->getTransactionName());
            });
        }
    }

    /**
     * Registers the queue transactions with the NewRelic PHP agent
     */
    protected function registerQueueTransactions()
    {
        $app = $this->app;

        $app['queue']->before(function (JobProcessed $event) use ($app) {
            $app['newrelic']->backgroundJob(true);
            $app['newrelic']->startTransaction(ini_get('newrelic.appname'));
            if ($app['config']->get('newrelic.auto_name_jobs')) {
                $app['newrelic']->nameTransaction($this->getJobName($event));
            }
        });

        $app['queue']->after(function (JobProcessed $event) use ($app) {
            $app['newrelic']->endTransaction();
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
    public function getJobName(JobProcessed $event)
    {
        return str_replace(
            [
                '{connection}',
                '{class}',
                '{data}',
                '{args}',
                '{input}',
            ],
            [
                $event->connectionName,
                get_class($event->job),
                json_encode($event->data),
                implode(', ', array_keys($event->data)),
                implode(', ', array_values($event->data)),
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
