<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-scheduled')->everyMinute()->withoutOverlapping()->onOneServer();
Schedule::command('relance:envoyer')->dailyAt('08:00')->withoutOverlapping()->onOneServer();

