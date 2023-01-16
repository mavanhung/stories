<?php

namespace Botble\DiscountCode;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('discount_codes');
        Schema::dropIfExists('discount_codes_translations');
    }
}
