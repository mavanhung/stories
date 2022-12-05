<?php

namespace Botble\Comment\Providers;

use Botble\Comment\Models\Comment;
use Illuminate\Support\ServiceProvider;
use Botble\Comment\Repositories\Caches\CommentCacheDecorator;
use Botble\Comment\Repositories\Eloquent\CommentRepository;
use Botble\Comment\Repositories\Interfaces\CommentInterface;
use Illuminate\Support\Facades\Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class CommentServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register()
    {
        $this->app->bind(CommentInterface::class, function () {
            return new CommentCacheDecorator(new CommentRepository(new Comment));
        });

        $this->setNamespace('plugins/comment')->loadHelpers();
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
                \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::registerModule(Comment::class, [
                    'name',
                ]);
            } else {
                // Use language v1
                $this->app->booted(function () {
                    \Language::registerModule([Comment::class]);
                });
            }
        }

        Event::listen(RouteMatched::class, function () {
            dashboard_menu()->registerItem([
                'id'          => 'cms-plugins-comment',
                'priority'    => 5,
                'parent_id'   => null,
                'name'        => 'plugins/comment::comment.name',
                'icon'        => 'fa fa-list',
                'url'         => route('comment.index'),
                'permissions' => ['comment.index'],
            ]);
        });
    }
}
