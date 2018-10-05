<?php

namespace RateHub\NewRelic\Adapters;

use RateHub\NewRelic\Contracts\Adapters\Adapter;

final class NullAdapter implements Adapter
{
    public function setApplicationName(string $applicationName): bool
    {
        return true;
    }

    public function noticeError(string $message, \Throwable $throwable = null): void
    {
        return;
    }

    public function nameTransaction(string $name): bool
    {
        return true;
    }

    public function startTransaction(string $applicationName): bool
    {
        return true;
    }

    public function endOfTransaction(): void
    {
        return;
    }

    public function endTransaction(bool $ignore = false): bool
    {
        return true;
    }

    public function ignoreTransaction(): void
    {
        return;
    }

    public function ignoreApdex(): void
    {
        return;
    }

    public function backgroundJob(bool $flag = true): void
    {
        return;
    }

    public function captureParams(bool $enable = true): void
    {
        return;
    }

    public function customMetric(string $name, float $value): bool
    {
        return true;
    }

    public function addCustomParameter(string $key, $value): bool
    {
        return true;
    }

    public function addCustomTracer(string $functionName): bool
    {
        return true;
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        return '';
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        return '';
    }

    public function disableAutoRUM(): void
    {
        return;
    }

    public function setUserAttributes(string $user = "", string $account = "", string $product = ""): bool
    {
        return true;
    }

    public function recordCustomEvent(string $name, array $attributes): void
    {
        return;
    }
}
