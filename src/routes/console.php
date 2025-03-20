<?php

use Illuminate\Support\Facades\Schedule;
use Limonlabs\Bigcommerce\Mail\Admin\WeeklyStoresEmail;
use Illuminate\Support\Facades\Mail;

Schedule::call(function () {
    Mail::to(array_map('trim', explode(',', config('mail.from.admin_address'))))
        ->send(new WeeklyStoresEmail());
})->weekly();