<?php

namespace Botble\DiscountCode\Providers;

use Botble\DiscountCode\Models\DiscountCode;
use Illuminate\Support\ServiceProvider;
use Botble\DiscountCode\Repositories\Caches\DiscountCodeCacheDecorator;
use Botble\DiscountCode\Repositories\Eloquent\DiscountCodeRepository;
use Botble\DiscountCode\Repositories\Interfaces\DiscountCodeInterface;
use Illuminate\Support\Facades\Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class DiscountCodeServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(DiscountCodeInterface::class, function () {
            return new DiscountCodeCacheDecorator(new DiscountCodeRepository(new DiscountCode));
        });

        $this->setNamespace('plugins/discount-code')->loadHelpers();
    }

    public function boot()
    {
        $this
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes(['web']);

        if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            if (defined('LANGUAGE_ADVANCED_MODULE_SCREEN_NAME')) {
                // Use language v2
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(DiscountCode::class, [
                    'name',
                ]);
            } else {
                // Use language v1
                $this->app->booted(function () {
                    \Language::registerModule([DiscountCode::class]);
                });
            }
        }

        Event::listen(RouteMatched::class, function () {
            dashboard_menu()->registerItem([
                'id'          => 'cms-plugins-discount-code',
                'priority'    => 5,
                'parent_id'   => null,
                'name'        => 'plugins/discount-code::discount-code.name',
                'icon'        => 'fa fa-list',
                'url'         => route('discount-code.index'),
                'permissions' => ['discount-code.index'],
            ]);
        });
    }
}
