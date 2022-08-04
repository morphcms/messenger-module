<?php

namespace Modules\Messenger\Listeners;

use RTippin\Messenger\Events\BroadcastFailedEvent;

class BroadcastError
{
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
     * @param  BroadcastFailedEvent  $event
     * @return void
     */
    public function handle(BroadcastFailedEvent $event): void
    {
        report($event->exception);
    }}
