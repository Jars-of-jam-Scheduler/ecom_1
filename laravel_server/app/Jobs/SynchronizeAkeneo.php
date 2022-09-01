<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

use App\Models\User;
use App\Models\AkeneoProduct;
use App\Notifications\AkeneoSynchronized;
use App\Notifications\AkeneoFailedToSynchronize;

class SynchronizeAkeneo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	use \App\Http\Traits\AkeneoConnector;
	use \App\Http\Traits\ElasticsearchConnector;

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
        $this->onQueue('default');
        $this->onConnection('database');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$akeneo_products_to_insert_or_update = [];
		$elasticsearch_products_to_insert_or_update = "";

        $result = $this->getFromAkeneo(config('akeneo.connections.rest_api.endpoint') . '/products', [
			'pagination_type' => 'search_after',
			'limit' => 100

		], function($query_result) use ($elasticsearch_products_to_insert_or_update) {

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

				foreach($response_as_object->_embedded->items as $akeneo_product) {
					$elasticsearch_products_to_insert_or_update .= "{\"index\": {}}\n";
					$elasticsearch_products_to_insert_or_update .= json_encode([
							'code' => $akeneo_product->identifier, 
							'reference' => $akeneo_product->identifier, 
							'name' => property_exists($akeneo_product->values, 'name') ? $akeneo_product->values->name[0]->data : NULL,
							'description' => property_exists($akeneo_product->values, 'description') ? $akeneo_product->values->description[0]->data : NULL, 
							'type' => property_exists($akeneo_product->values, 'name') && str_contains(strtolower($akeneo_product->values->name[0]->data), 'system') ? 'service' : 'simple_product'
						]) . "\n";
				}
				$this->queryElasticsearch('post', config('elasticsearch.connections.rest_api.endpoint') . '/ecom_1/_bulk?pretty', $elasticsearch_products_to_insert_or_update);

				
			} while(
				property_exists($response_as_object->_links, 'next') && $query_result = $this->getFromAkeneo($response_as_object->_links->next->href)
			);

		});

		$user = User::find(1);
		$user->notify(new AkeneoSynchronized());
    }

	/**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
		$user = User::find(1);
		$user->notify(new AkeneoFailedToSynchronize());
        report($exception);
    }
}
