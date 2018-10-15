<?php

namespace RateHub\NewRelic\Exceptions;

use RateHub\NewRelic\Contracts\Exceptions\ExceptionFilter;

final class AggregateExceptionFilter implements ExceptionFilter
{
    /**
     * @var ExceptionFilter[]
     */
    private $exceptionFilters;

    /**
     * @param ExceptionFilter[] $exceptionFilters
     */
    public function __construct(array $exceptionFilters)
    {
        if (count($exceptionFilters) === 0) {
            throw new \InvalidArgumentException('You must provide at least one exception filter');
        }

        foreach ($exceptionFilters as $exceptionFilter) {
            if (!$exceptionFilter instanceof ExceptionFilter) {
                throw new \InvalidArgumentException('Invalid exception filter');
            }
        }

        $this->exceptionFilters = $exceptionFilters;
    }

    public function shouldReport(\Throwable $exception): bool
    {
        foreach ($this->exceptionFilters as $exceptionFilter) {
            if (!$exceptionFilter->shouldReport($exception)) {
                return false;
            }
        }

        return true;
    }
}
