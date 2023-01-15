<?php

use Botble\Widget\AbstractWidget;

class LatestPostsWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $frontendTemplate = 'frontend';

    /**
     * @var string
     */
    protected $backendTemplate = 'backend';

    /**
     * @var string
     */
    protected $widgetDirectory = 'latest-posts';

    /**
     * LatestPosts constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'name'        => __('Latest Posts'),
            'description' => __('Widget to display latest posts'),
            'number_display' => 5,
        ]);
    }
}
