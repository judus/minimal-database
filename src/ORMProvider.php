<?php

namespace Maduser\Minimal\Database;

use Maduser\Minimal\Framework\Facades\Config;
use Maduser\Minimal\Framework\Providers\AbstractProvider;

class ORMProvider extends AbstractProvider
{
    public function resolve()
    {
        if ($databaseConfig = Config::item('database')) {
            DB::connections($databaseConfig);
        }
    }
}