<?php

namespace Botble\Tiki\Providers;

use Botble\Tiki\Models\Tiki;
use Botble\Tiki\Models\Seller;
use Botble\Tiki\Models\DiscountCode;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Events\RouteMatched;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Tiki\Repositories\Eloquent\TikiRepository;
use Botble\Tiki\Repositories\Interfaces\TikiInterface;
use Botble\Tiki\Repositories\Caches\TikiCacheDecorator;
use Botble\Tiki\Repositories\Eloquent\SellerRepository;
use Botble\Tiki\Repositories\Interfaces\SellerInterface;
use Botble\Tiki\Repositories\Caches\SellerCacheDecorator;
use Botble\Tiki\Repositories\Eloquent\DiscountCodeRepository;
use Botble\Tiki\Repositories\Interfaces\DiscountCodeInterface;
use Botble\Tiki\Repositories\Caches\DiscountCodeCacheDecorator;

class TikiServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(TikiInterface::class, function () {
            return new TikiCacheDecorator(new TikiRepository(new Tiki));
        });

        $this->app->bind(DiscountCodeInterface::class, function () {
            return new DiscountCodeCacheDecorator(new DiscountCodeRepository(new DiscountCode));
        });

        $this->app->bind(SellerInterface::class, function () {
            return new SellerCacheDecorator(new SellerRepository(new Seller));
        });

        $this->setNamespace('plugins/tiki')->loadHelpers();
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
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Tiki::class, [
                    'name',
                ]);
            } else {
                // Use language v1
                $this->app->booted(function () {
                    \Language::registerModule([Tiki::class]);
                });
            }
        }

        Event::listen(RouteMatched::class, function () {
            dashboard_menu()
                ->registerItem([
                    'id'          => 'cms-plugins-tiki',
                    'priority'    => 4,
                    'parent_id'   => null,
                    'name'        => 'plugins/tiki::base.menu_name',
                    'icon'        => 'fa fa-tag',
                    'url'         => route('tiki_discount_code.index'),
                    'permissions' => ['discountcodes.index'],
                ])
                ->registerItem([
                    'id'          => 'cms-plugins-tiki-discount-code',
                    'priority'    => 1,
                    'parent_id'   => 'cms-plugins-tiki',
                    'name'        => 'plugins/tiki::discountcode.menu_name',
                    'icon'        => null,
                    'url'         => route('tiki_discount_code.index'),
                    'permissions' => ['discountcodes.index'],
                ])
                ->registerItem([
                    'id'          => 'cms-plugins-tiki-seller',
                    'priority'    => 2,
                    'parent_id'   => 'cms-plugins-tiki',
                    'name'        => 'plugins/tiki::seller.menu_name',
                    'icon'        => null,
                    'url'         => route('tiki_seller.index'),
                    'permissions' => ['sellers.index'],
                ]);
        });
    }
}
