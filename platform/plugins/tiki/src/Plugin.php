<?php

namespace Botble\Tiki;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove()
    {
        Schema::dropIfExists('tikis');
        Schema::dropIfExists('tikis_translations');
    }
}
