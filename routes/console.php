<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-scheduled')->everyMinute();
Schedule::command('relance:envoyer')->dailyAt('08:00');


