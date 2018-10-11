<?php

namespace RateHub\NewRelic\DetailProcessors;

use RateHub\NewRelic\Contracts\DetailProcessors\DetailProcessor;

final class StackProcessor implements DetailProcessor
{
    /**
     * @var DetailProcessor[]
     */
    private $detailProcessors;

    public function __construct(array $detailProcessors)
    {
        foreach ($detailProcessors as $detailProcessor) {
            if (!$detailProcessor instanceof DetailProcessor) {
                throw new \InvalidArgumentException('Must be an instance of ' . DetailProcessor::class);
            }
        }
        $this->detailProcessors = $detailProcessors;
    }

    public function process(array $details): array
    {
        foreach ($this->detailProcessors as $detailProcessor) {
            $details = $detailProcessor->process($details);
        }
        return $details;
    }
}
