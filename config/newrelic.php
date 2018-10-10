<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Adapter
    |--------------------------------------------------------------------------
    |
    | Mostly used for development/staging servers that don't have new relic and
    | running tests.
    |
    | One of 'newrelic', 'null', 'log'
    |
    | When set to 'newrelic' you need to specify whether or not to throw an
    | exception if the extension is missing. When false we'll use the fallback
    | adapter.
    |
    */

    'adapter' => env('NEWRELIC_ADAPTER', 'newrelic'),

    'adapters' => [
        'log' => [
            'channel' => 'stack',
        ],
    ],

    'throw_when_missing' => env('NEWRELIC_THROW_IF_NOT_INSTALLED', true),

    'fallback' => env('NEWRELIC_FALLBACK_ADAPTER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Auto name transactions
    |--------------------------------------------------------------------------
    |
    | Will automatically name transactions in New Relic using the Laravel route
    | name, action, or request.
    |
    | Set to false to use the default New Relic
    | behaviour or to set your own.
    |
    */

    'auto_name_transactions' => env('NEWRELIC_AUTO_NAME_TRANSACTION', true),

    /*
    |--------------------------------------------------------------------------
    | Auto name jobs
    |--------------------------------------------------------------------------
    |
    | Will automatically name queued jobs in New Relic using the job class,
    | data, or connection name.
    |
    | Set this to false to use the default New Relic behaviour or to set your
    | own.
    |
    */

    'auto_name_jobs' => env('NEWRELIC_AUTO_NAME_JOB', true),

    /*
    |--------------------------------------------------------------------------
    | Transaction Naming Provider
    |--------------------------------------------------------------------------
    |
    | Define the name used when automatically naming transactions.
    | a token string:
    |      a pattern you define yourself, available tokens:
    |          {controller} = Controller@action or Closure@path
    |          {method} = GET / POST / etc.
    |          {route} = route name if named, otherwise same as {controller}
    |          {path} = the registered route path (includes variable names)
    |          {uri} = the actual URI requested
    |      anything that is not a matched token will remain a string literal
    |      example:
    |          "GET /world" with pattern 'hello {path} you really {method} me' would return:
    |          'hello /world you really GET me'
    |
    */

    'name_provider' => env('NEWRELIC_NAME_PROVIDER', '{uri} {route}'),

    /*
    |--------------------------------------------------------------------------
    | Job Naming Provider
    |--------------------------------------------------------------------------
    | Define the name used when automatically naming queued jobs.
    | a token string:
    |       a pattern you define yourself, available tokens:
    |           {connection} = The name of the queue connection
    |           {class} = The name of the job class
    |       anything that is not a matched token will remain a string literal
    |       example:
    |           Given a job named App\MyJob, on the connection "sync"
    |           the pattern 'I say {connection} when I run {class}' would return:
    |           'I say sync, world when I run App\MyJob'
    */

    'job_name_provider' => env('NEWRELIC_JOB_NAME_PROVIDER', '{class}'),

    /*
    |--------------------------------------------------------------------------
    | New Relic Agent Missing
    |--------------------------------------------------------------------------
    |
    | True if you'd like an exception to be thrown when New Relic is not found.
    |
    */

    'throw_if_not_installed' => env('NEWRELIC_THROW_IF_NOT_INSTALLED', false),

    /*
    |--------------------------------------------------------------------------
    | Logging - Exceptions
    |--------------------------------------------------------------------------
    |
    | Facilities for ignoring exceptions. You can implement the ExceptionFilter interface
    | if you have special needs such as checking status codes or the presence of request
    | variables.
    |
    */

    'exceptionFilter' => 'blacklist',
    'filters'         => [
        'aggregate' => [
            'filters' => [
                \RateHub\NewRelic\Exceptions\BlacklistExceptionFilter::class,
            ],
        ],
        'blacklist' => [
            'ignoredExceptions' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging - Ignored Fields
    |--------------------------------------------------------------------------
    |
    | Doesn't log certain fields. Note that in order to truly ignore these fields
    | you will also need to set the ini setting newrelic.attributes.exclude
    |
    */

    'ignored_fields' => [
        'password',
        'password_confirm',
        'confirm_password',
        'new_password',
        'current_password',
    ],
];
