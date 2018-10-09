<?php

namespace RateHub\NewRelic\Commands;

use Illuminate\Console\Command;

final class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newrelic:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests the New Relic functionality.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $this->output->write('Hello world!');
    }
}
