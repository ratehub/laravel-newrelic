<?php

namespace RateHub\NewRelic\DetailProcessors;

use RateHub\NewRelic\Contracts\DetailProcessors\DetailProcessor;

final class NullProcessor implements DetailProcessor
{
    public function process(array $details): array
    {
        return $details;
    }
}
