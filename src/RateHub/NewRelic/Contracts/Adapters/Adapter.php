<?php

namespace RateHub\NewRelic\Contracts\Adapters;

interface Adapter
{
    public function setApplicationName(string $applicationName): bool;

    public function noticeError(string $message, \Throwable $throwable = null): void;

    public function nameTransaction(string $name): bool;

    public function startTransaction(string $applicationName): bool;

    public function endOfTransaction(): void;

    public function endTransaction(bool $ignore = false): bool;

    public function ignoreTransaction(): void;

    public function ignoreApdex(): void;

    public function backgroundJob(bool $flag);

    public function captureParams(bool $enable);

    public function customMetric(string $name, float $value);

    public function addCustomParameter(string $key, $value): bool;

    public function addCustomTracer(string $functionName): bool;

    public function getBrowserTimingHeader(bool $includeTags = true): string;

    public function getBrowserTimingFooter(bool $includeTags = true): string;

    public function disableAutoRUM(): void;

    public function setUserAttributes(string $user = "", string $account = "", string $product = ""): bool;

    public function recordCustomEvent(string $name, array $attributes): void;
}
