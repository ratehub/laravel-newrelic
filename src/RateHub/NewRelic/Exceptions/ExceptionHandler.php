<?php

namespace RateHub\NewRelic\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler as IExceptionHandler;
use Illuminate\Support\Arr;
use RateHub\NewRelic\Adapters\NewRelicAgentAdapter;
use RateHub\NewRelic\Contracts\DetailProcessors\DetailProcessor;

final class ExceptionHandler implements IExceptionHandler
{
    /**
     * @var DetailProcessor
     */
    private $detailProcessor;

    /**
     * @var NewRelicAgentAdapter
     */
    private $newRelic;

    /**
     * @var ShouldReportException
     */
    protected $shouldReportException;

    public function __construct(DetailProcessor $detailProcessor, NewRelicAgentAdapter $newRelic, $ignoredExceptions = [], $ignoredFields = [])
    {
        $this->detailProcessor = $detailProcessor;
        $this->newRelic = $newRelic;
    }

    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            $this->logException($e);
        }
    }

    public function render($request, Exception $e)
    {
        // Nothing to do for New Relic
    }

    public function renderForConsole($output, Exception $e)
    {
        // Nothing to do for New Relic
    }

    /**
     * Logs the exception to New Relic (if the extension is loaded)
     * Note: If you want some attributes ignored you have to add them
     * to the ini file under the field newrelic.attributes.exclude
     *
     * @param Exception $e
     */
    protected function logException(Exception $e)
    {
        $logDetails = $this->detailProcessor->__invoke([]);
        foreach ($logDetails as $param => $value) {
            $this->newRelic->addCustomParameter($param, $value);
        }

        $this->newRelic->noticeError($e->getMessage(), $e);
    }

    protected function shouldReport(Exception $e): bool
    {
        return !$this->shouldntReport($e);
    }

    protected function shouldntReport(Exception $e): bool
    {
        return !is_null(Arr::first($this->shouldReportException, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }
}
