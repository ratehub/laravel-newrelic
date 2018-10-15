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
    | One of 'newrelic', 'nullAdapter', 'log'
    |
    | When set to 'newrelic' you need to specify whether or not to throw an
    | exception if the extension is missing. When false we'll use the fallback
    | adapter.
    |
    */

    'adapter' => env('NEWRELIC_ADAPTER', 'newrelic'),

    'adapters' => [
        'log' => [
            'channel' => env('NEWRELIC_ADAPTER_LOG_CHANNEL', 'stack'),
        ],
    ],

    'throwWhenMissing' => env('NEWRELIC_THROW_IF_NOT_INSTALLED', true),

    'fallback' => env('NEWRELIC_FALLBACK_ADAPTER', 'nullAdapter'),

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

    'autoNameTransactions' => env('NEWRELIC_AUTO_NAME_TRANSACTION', true),

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

    'autoNameJobs' => env('NEWRELIC_AUTO_NAME_JOB', true),

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

    'nameProvider' => env('NEWRELIC_NAME_PROVIDER', '{uri} {route}'),

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

    'jobNameProvider' => env('NEWRELIC_JOB_NAME_PROVIDER', '{class}'),

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

    'exceptionFilter' => \RateHub\NewRelic\Exceptions\BlacklistExceptionFilter::class,

    'filters' => [
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
    | Logging - Detail Processors
    |--------------------------------------------------------------------------
    |
    | Extra information or manipulation of logged data.
    | If you're looking at omitting request attributes you will also need
    | to set the ini setting newrelic.attributes.exclude in order to properly
    | ignore things like submitted passwords.
    |
    */

    'detailProcessor' => \RateHub\NewRelic\DetailProcessors\StackProcessor::class,

    'detailProcessors' => [
        'stack'         => [
            'processors' => [
                \RateHub\NewRelic\DetailProcessors\NullProcessor::class,
            ],
        ],
        'ignoredFields' => [
            'password',
            'password_confirm',
            'confirm_password',
            'new_password',
            'current_password',
        ],
    ],
];
