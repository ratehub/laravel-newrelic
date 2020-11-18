<?php

namespace RateHub\NewRelic\Exceptions;

use Illuminate\Contracts\Debug\ExceptionHandler as IExceptionHandler;
use Illuminate\Support\Arr;
use RateHub\NewRelic\Contracts\Adapters\Adapter;
use RateHub\NewRelic\Contracts\DetailProcessors\DetailProcessor;
use RateHub\NewRelic\Contracts\Exceptions\ExceptionFilter;
use Throwable;

final class ExceptionHandler implements IExceptionHandler
{
    /**
     * @var DetailProcessor
     */
    private $detailProcessor;

    /**
     * @var Adapter
     */
    private $newRelic;

    /**
     * @var ExceptionFilter
     */
    protected $exceptionFilter;

    public function __construct(DetailProcessor $detailProcessor, Adapter $newRelic, ExceptionFilter $exceptionFilter)
    {
        $this->detailProcessor = $detailProcessor;
        $this->newRelic = $newRelic;
        $this->exceptionFilter = $exceptionFilter;
    }

    public function report(Throwable $e)
    {
        if ($this->exceptionFilter->shouldReport($e)) {
            $this->logException($e);
        }
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param  \Throwable  $e
     * @return bool
     */
    public function shouldReport(Throwable $e)
    {
        return $this->exceptionFilter->shouldReport($e);
    }

    public function render($request, Throwable $e)
    {
        // Nothing to do for New Relic
    }

    public function renderForConsole($output, Throwable $e)
    {
        // Nothing to do for New Relic
    }

    /**
     * Logs the exception to New Relic (if the extension is loaded)
     * Note: If you want some attributes ignored you have to add them
     * to the ini file under the field newrelic.attributes.exclude
     *
     * @param Throwable $exception
     */
    protected function logException(Throwable $exception)
    {
        $logDetails = Arr::dot($this->detailProcessor->process([]));
        foreach ($logDetails as $param => $value) {
            if (!is_scalar($value)) {
                $value = json_encode($value);
            }

            $this->newRelic->addCustomParameter($param, $value);
        }

        $this->newRelic->noticeError($exception->getMessage(), $exception);
    }
}
