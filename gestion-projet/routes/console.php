<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('maintenance:check-auto-disable')->everyMinute();
