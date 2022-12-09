<div class="pb-50">
    <p>{{ $gallery->description }}</p>
    <div id="list-photo">
        @foreach (gallery_meta_data($gallery) as $image)
            @if ($image)
                <div class="item" data-src="{{ RvMedia::getImageUrl(Arr::get($image, 'img')) }}" data-sub-html="{{ clean(Arr::get($image, 'description')) }}">
                    <div class="photo-item">
                        <div class="thumb">
                            <a href="{{ RvMedia::getImageUrl(Arr::get($image, 'img')) }}">
                                <img src="{{ RvMedia::getImageUrl(Arr::get($image, 'img')) }}" alt="{{ clean(Arr::get($image, 'description')) }}">
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    @if (theme_option('facebook_comment_enabled_in_post', 'yes') == 'yes')
    <br>
    {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, Theme::partial('comments-facebook')) !!}
    @endif
</div>
