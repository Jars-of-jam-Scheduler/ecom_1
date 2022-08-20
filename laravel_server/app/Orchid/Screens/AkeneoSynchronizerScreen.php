<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert;

use App\Jobs\SynchronizeAkeneo;

class AkeneoSynchronizerScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return __('orchid.akeneosynchronizer.name');
    }

	public function description(): ?string
    {
        return __('orchid.akeneosynchronizer.description');
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
		return [
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
		return [
            Layout::rows([
                Button::make('Send Message')
                ->icon('paper-plane')
                ->method('synchronizeWithAkeneo')
            ])
        ];
    }
	

    public function synchronizeWithAkeneo()
    {
		SynchronizeAkeneo::dispatch();
        Alert::info('The synchronization has been started. You will be notified by mail and in your browser when it is complete.');
    }

}
