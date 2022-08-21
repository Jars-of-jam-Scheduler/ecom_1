<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

use App\Exceptions\AkeneoQueryProblemException;
use App\Exceptions\AkeneoQueryUnknownProblemException;

trait AkeneoConnector {

	private function authenticateForAkeneo() {
		if(empty(Storage::files('akeneo_access_token'))) {
			$queried_tokens = Http::withBasicAuth(config('akeneo.authentication.client_id'), config('akeneo.authentication.secret'))->post(config('akeneo.authentication.endpoint'), [
				"grant_type" => config('akeneo.authentication.grant_type'),
				"username" => config('akeneo.authentication.username'),
				"password" => config('akeneo.authentication.password')
			])->throw();

		} elseif(now()->diffInSeconds(Carbon::createFromTimestamp(pathinfo(Storage::files('akeneo_access_token')[0])['filename'])) > 3000) {
			$queried_tokens = Http::withBasicAuth(config('akeneo.authentication.client_id'), config('akeneo.authentication.secret'))->post(config('akeneo.authentication.endpoint'), [
				"grant_type" => config('akeneo.authentication.grant_type_refresh'),
				"refresh_token" => Storage::get(Storage::files('akeneo_refresh_token')[0]),
			])->throw();

		}

		if(isset($queried_tokens)) {
			$now = now();
			
			$queried_tokens_as_object = $queried_tokens->object();
			Storage::deleteDirectory('akeneo_access_token');
			Storage::put('akeneo_access_token/' . $now->timestamp, $queried_tokens_as_object->access_token);

			Storage::deleteDirectory('akeneo_refresh_token');
			Storage::put('akeneo_refresh_token/' . $now->timestamp, $queried_tokens_as_object->refresh_token);
		}
	}

	function getFromAkeneo($request_uri, $data = NULL, $delegated_function = NULL) {

		$query_result = NULL;

		try {
			$this->authenticateForAkeneo();

			$query_result = Http::withToken(Storage::get(Storage::files('akeneo_access_token')[0]))->get($request_uri, $data)->throw();

			if(isset($delegated_function)) {
				$delegated_function($query_result);
			}

		}
		catch(\Illuminate\Http\Client\RequestException | \Illuminate\Http\Client\ConnectionException $e) {
			$error_code = 500;
			$error_message = __('akeneo.errors.connection_problem');

			if($e instanceof \Illuminate\Http\Client\RequestException) {
				switch($e->response->status()) {
					case 403:
						$error_code = 403;
						$error_message = __('akeneo.errors.forbidden');
						break;
					case 404:
						$error_code = 404;
						$error_message = __('akeneo.errors.not_found');
						break;
					case 429:
						$error_code = 429;
						$error_message = __('akeneo.errors.too_many_requests');
						break;
				}
			}

			report($e);  // In an outside-jobs execution context, top-catching these (important) exceptions could prevent them from being reported
			throw new AkeneoQueryProblemException($error_message, $error_code, $e);

		}
		catch(\League\Flysystem\UnableToWriteFile | \Exception $e) {
			report($e);
			throw new AkeneoQueryUnknownProblemException(__('akeneo.errors.unable_to_query_unknown_reason'), 500, $e);
		}

		return $query_result;
	}
	
}
