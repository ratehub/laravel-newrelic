<?php

namespace RateHub\NewRelic\Adapters;

use Psr\Log\LoggerInterface;
use RateHub\NewRelic\Contracts\Adapters\Adapter;

final class LogAdapter implements Adapter
{
    /** @var */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setApplicationName(string $applicationName): bool
    {
        $this->logger->info('NewRelic call to setApplicationName', [
            '$applicationName' => $applicationName,
        ]);
        return true;
    }

    public function noticeError(string $message, \Throwable $throwable = null): void
    {
        $this->logger->error('NewRelic call to noticeError', [
            '$message'   => $message,
            '$throwable' => $throwable,
        ]);
        return;
    }

    public function nameTransaction(string $name): bool
    {
        $this->logger->info('NewRelic call to nameTransaction', [
            '$name' => $name,
        ]);
        return true;
    }

    public function startTransaction(string $applicationName): bool
    {
        $this->logger->info('NewRelic call to startTransaction', [
            '$applicationName' => $applicationName,
        ]);
        return true;
    }

    public function endOfTransaction(): void
    {
        $this->logger->info('NewRelic call to endOfTransaction');
        return;
    }

    public function endTransaction(bool $ignore = false): bool
    {
        $this->logger->info('NewRelic call to endTransaction', [
            '$ignore' => $ignore,
        ]);
        return true;
    }

    public function ignoreTransaction(): void
    {
        $this->logger->info('NewRelic call to ignoreTransaction');
        return;
    }

    public function ignoreApdex(): void
    {
        $this->logger->info('NewRelic call to ignoreApdex');
        return;
    }

    public function backgroundJob(bool $flag = true): void
    {
        $this->logger->info('NewRelic call to backgroundJob', [
            '$flag' => $flag,
        ]);
        return;
    }

    public function captureParams(bool $enable = true): void
    {
        $this->logger->info('NewRelic call to captureParams', [
            '$enable' => $enable,
        ]);
        return;
    }

    public function customMetric(string $name, float $value): bool
    {
        $this->logger->info('NewRelic call to customMetric', [
            '$name'  => $name,
            '$value' => $value,
        ]);
        return true;
    }

    public function addCustomParameter(string $key, $value): bool
    {
        $this->logger->info('NewRelic call to addCustomParameter', [
            '$key'   => $key,
            '$value' => $value,
        ]);
        return true;
    }

    public function addCustomTracer(string $functionName): bool
    {
        $this->logger->info('NewRelic call to addCustomTracer', [
            '$functionName' => $functionName,
        ]);
        return true;
    }

    public function getBrowserTimingHeader(bool $includeTags = true): string
    {
        $this->logger->info('NewRelic call to getBrowserTimingHeader', [
            '$includeTags' => $includeTags,
        ]);
        return '';
    }

    public function getBrowserTimingFooter(bool $includeTags = true): string
    {
        $this->logger->info('NewRelic call to getBrowserTimingFooter', [
            '$includeTags' => $includeTags,
        ]);
        return '';
    }

    public function disableAutoRUM(): void
    {
        $this->logger->info('NewRelic call to disableAutoRUM');
        return;
    }

    public function setUserAttributes(string $user = "", string $account = "", string $product = ""): bool
    {
        $this->logger->info('NewRelic call to setUserAttributes', [
            '$user'    => $user,
            '$account' => $account,
            '$product' => $product,
        ]);
        return true;
    }

    public function recordCustomEvent(string $name, array $attributes): void
    {
        $this->logger->info('NewRelic call to recordCustomEvent', [
            '$name'       => $name,
            '$attributes' => $attributes,
        ]);
        return;
    }
}
