<?php
namespace App\Trait;

    Trait TimeZone
    {
        public function changeTimeZone($time_zone)
        {
            date_default_timezone_set($time_zone);
        }
    }