<?php

namespace RateHub\NewRelic\Adapters;

use RateHub\NewRelic\Contracts\Adapters\Adapter;

final class NewRelicAgentAdapter implements Adapter
{
    /** @var bool */
    private $extensionInstalled = false;

    public function __construct(bool $throwWhenMissing = false)
    {
        $this->extensionInstalled = extension_loaded('newrelic');

        if (!$this->extensionInstalled && $throwWhenMissing) {
            throw new \RuntimeException("New Relic PHP agent is missing");
        }
    }

    public function setApplicationName(string $applicationName): bool
    {
        return newrelic_set_appname($applicationName);
    }

    public function noticeError(string $message, \Throwable $throwable = null): void
    {
        newrelic_notice_error($message, $throwable);
    }

    public function nameTransaction(string $name): bool
    {
        return newrelic_name_transaction($name);
    }

    public function startTransaction(string $applicationName): bool
    {
        return newrelic_start_transaction($applicationName);
    }

    public function endOfTransaction(): void
    {
        newrelic_end_of_transaction();
    }

    public function endTransaction(bool $ignore = false): bool
    {
        return newrelic_end_transaction($ignore);
    }

    public function ignoreTransaction(): void
    {
        newrelic_ignore_transaction();
    }

    public function ignoreApdex(): void
    {
        newrelic_ignore_apdex();
    }

    public function backgroundJob(bool $flag = true): void
    {
        newrelic_background_job($flag);
    }

    public function captureParams(bool $enable = true): void
    {
        newrelic_capture_params($enable);
    }

    public function customMetric(string $name, float $value): bool
    {
        return newrelic_custom_metric($name, $value);
    }

    public function addCustomParameter(string $key, $value): bool
    {
        return newrelic_add_custom_parameter($key, $value);
    }

    public function addCustomTracer(string $functionName): bool
    {
        return newrelic_add_custom_tracer($functionName);
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        return newrelic_get_browser_timing_header($includeTags);
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        return newrelic_get_browser_timing_footer($includeTags);
    }

    public function disableAutoRUM(): void
    {
        newrelic_disable_autorum();
    }

    public function setUserAttributes(string $user = "", string $account = "", string $product = ""): bool
    {
        return newrelic_set_user_attributes($user, $account, $product);
    }

    public function recordCustomEvent(string $name, array $attributes): void
    {
        newrelic_record_custom_event($name, $attributes);
    }
}
