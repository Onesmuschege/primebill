<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('billing:generate-invoices')->monthlyOn(1, '08:00');
Schedule::command('billing:suspend-overdue')->daily()->at('09:00');
Schedule::command('billing:send-reminders')->daily()->at('08:00');
Schedule::command('logs:clean')->weekly();
