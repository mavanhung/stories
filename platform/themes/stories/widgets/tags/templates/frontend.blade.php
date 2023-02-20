<div class="col-lg-3 col-md-6">
    <div class="sidebar-widget widget_tagcloud mb-30">
        <div class="widget-header-2 position-relative mb-30">
            <h5 class="mt-5 mb-30">{{ $config['name'] }}</h5>
        </div>
        <div class="tagcloud mt-50">
            @foreach (get_popular_tags($config['number_display']) as $tag)
            <a class="tag-cloud-link" href="{{ $tag->url }}">{{ $tag->name }}</a>
            @endforeach
        </div>
    </div>
</div>
