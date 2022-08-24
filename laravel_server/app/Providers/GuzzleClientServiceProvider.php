<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Schema;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * @see https://codebriefly.com/laravel-logging-guzzle-requests-file/
 */
class GuzzleClientServiceProvider extends ServiceProvider
{
    private $logger;
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Bind GuzzleClient 
        $this->app->bind('GuzzleClient', function () {
             $messageFormats = [
                'REQUEST: ',
                'METHOD: {method}',
                'URL: {uri}',
                'HTTP/{version}',
                'HEADERS: {req_headers}',
                'Payload: {req_body}',
                'RESPONSE: ',
                'STATUS: {code}',
                'BODY: {res_body}',
            ];
 
            $stack = $this->setLoggingHandler($messageFormats);
 
            return function ($config) use ($stack){
                return new Client(array_merge($config, ['handler' => $stack]));
            };
        });
    }

    /**
     * Setup Logger
     */
    private function get_logger()
    {
        if (! $this->logger) {
            $this->logger = with(new Logger('guzzle-log'))->pushHandler(
                new RotatingFileHandler(storage_path('logs/guzzle-log.log'))
            );
        }
     
        return $this->logger;
    }

    /**
     * Setup Middleware
     */
    private function setGuzzleMiddleware(string $messageFormat)
    {
        return Middleware::log(
            $this->get_logger(),
            new MessageFormatter($messageFormat)
        );
    }

    /**
     * Setup Logging Handler Stack
     */
    private function setLoggingHandler(array $messageFormats)
    {
        $stack = HandlerStack::create();
 
        collect($messageFormats)->each(function ($messageFormat) use ($stack) {
            $stack->unshift(  // We'll use unshift instead of push, to add the middleware to the bottom of the stack, not the top
                $this->setGuzzleMiddleware($messageFormat)
            );
        });
     
        return $stack;
    }
}