<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:send-scheduled')->everyMinute();


