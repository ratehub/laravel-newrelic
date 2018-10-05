<?php

namespace RateHub\NewRelic\Exceptions;

use Illuminate\Support\Arr;
use RateHub\NewRelic\Contracts\Exceptions\ExceptionFilter;

final class BlacklistExceptionFilter implements ExceptionFilter
{
    /**
     * @var array
     */
    private $ignoredExceptions;

    /**
     * @param array $ignoredExceptions
     */
    public function __construct(array $ignoredExceptions)
    {
        $this->ignoredExceptions = $ignoredExceptions;
    }

    public function shouldReport(\Throwable $exception): bool
    {
        return !$this->shouldntReport($exception);
    }

    private function shouldntReport(\Throwable $exception): bool
    {
        return !is_null(Arr::first($this->ignoredExceptions, function ($type) use ($exception) {
            return $exception instanceof $type;
        }));
    }
}
