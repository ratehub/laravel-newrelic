<?php

namespace RateHub\NewRelic\Contracts\DetailProcessors;

/**
 * Simple interface for decorating an array with different
 * pieces of information.
 * Inspired by the monologger processors
 *
 * @package RateHub\NewRelic\Contracts\DetailProcessors
 */
interface DetailProcessor
{
    public function process(array $details): array;
}
