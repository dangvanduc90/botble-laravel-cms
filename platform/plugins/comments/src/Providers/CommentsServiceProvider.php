<?php

namespace Botble\Comments\Providers;

use Botble\Comments\Models\Comments;
use Illuminate\Support\ServiceProvider;
use Botble\Comments\Repositories\Caches\CommentsCacheDecorator;
use Botble\Comments\Repositories\Eloquent\CommentsRepository;
use Botble\Comments\Repositories\Interfaces\CommentsInterface;
use Botble\Base\Supports\Helper;
use Event;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class CommentsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    public function register()
    {
        $this->app->bind(CommentsInterface::class, function () {
            return new CommentsCacheDecorator(new CommentsRepository(new Comments));
        });

        Helper::autoload(__DIR__ . '/../../helpers');
    }

    public function boot()
    {
        $this->setNamespace('plugins/comments')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadMigrations()
            ->loadAndPublishTranslations()
            ->loadRoutes(['web']);

        Event::listen(RouteMatched::class, function () {
            if (defined('LANGUAGE_MODULE_SCREEN_NAME')) {
                \Language::registerModule([Comments::class]);
            }

            dashboard_menu()->registerItem([
                'id'          => 'cms-plugins-comments',
                'priority'    => 5,
                'parent_id'   => null,
                'name'        => 'plugins/comments::comments.name',
                'icon'        => 'fa fa-list',
                'url'         => route('comments.index'),
                'permissions' => ['comments.index'],
            ]);
        });
    }
}
