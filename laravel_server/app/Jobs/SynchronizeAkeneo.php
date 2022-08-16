<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Providers\AskForAkeneoSynchronization;

class SynchronizeAkeneo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	use \App\Http\Traits\AkeneoConnector;

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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$akeneo_products_to_insert_or_update = [];

        $result = $this->getFromAkeneo(config('akeneo.connections.rest_api.endpoint') . '/products', [
			'pagination_type' => 'search_after',
			'limit' => 100

		], function($query_result) {

			do {
				$response_as_object = $query_result->object();

				$akeneo_products_to_insert_or_update = array_map(fn ($akeneo_product) => [
					'code' => $akeneo_product->identifier,  
					'reference' => $akeneo_product->identifier,
					'name' => property_exists($akeneo_product->values, 'name') ? $akeneo_product->values->name[0]->data : NULL,
					'description' => property_exists($akeneo_product->values, 'description') ? $akeneo_product->values->description[0]->data : NULL, 
					'type' => property_exists($akeneo_product->values, 'name') && str_contains(strtolower($akeneo_product->values->name[0]->data), 'system') ? 'service' : 'simple_product'
				], $response_as_object->_embedded->items);

				AkeneoProduct::upsert($akeneo_products_to_insert_or_update, ['reference'], ['name', 'description', 'type']);
				
			} while(
				property_exists($response_as_object->_links, 'next') && $query_result = $this->getFromAkeneo($response_as_object->_links->next->href)
			);

		});

		AskForAkeneoSynchronization::dispatch();
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
