<?php

namespace RateHub\NewRelic\Contracts\Exceptions;

interface ExceptionFilter
{
    public function shouldReport(\Throwable $exception): bool;
}
