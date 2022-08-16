<?php

namespace App\Providers;

use App\Providers\AskForAkeneoSynchronization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

use \App\Models\AkeneoProduct;

class AkeneoSynchronizer implements ShouldQueue
{

	/**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'database';
 
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'default';

	/**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Providers\AskForAkeneoSynchronization  $event
     * @return void
     */
    public function handle(AskForAkeneoSynchronization $event)
    {
		//
    }

	/**
     * Handle a job failure.
     *
     * @param  \App\Events\AskForAkeneoSynchronization  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(AskForAkeneoSynchronization $event, $exception)
    {
        report($exception);
    }
}
