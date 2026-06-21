<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('fraud:scan')->hourly();
Schedule::command('reconciliation:snapshot')->dailyAt('23:30');
