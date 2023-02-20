<div class="post-module-3">
    <div class="loop-list loop-list-style-1">
        @foreach($posts as $post)
            <article class="hover-up-2 transition-normal wow animated">
                <div class="row mb-40 list-style-2">
                    <div class="col-md-4">
                        <div class="post-thumb position-relative border-radius-5">
                            {{-- <div class="img-hover-slide border-radius-5 position-relative" style="background-image: url({{ RvMedia::getImageUrl($post->image, 'small', false, RvMedia::getDefaultImage()) }})"> --}}
                            <div class="img-hover-slide border-radius-5 position-relative lazy-background" data-bg_url="{{ RvMedia::getImageUrl($post->image, 'small', false, RvMedia::getDefaultImage()) }}">
                                <a class="img-link" href="{{ $post->url }}"></a>
                            </div>
                            @includeIf('theme.stories::partials.components.social-share', ['post' => $post])
                        </div>
                    </div>
                    <div class="col-md-8 align-self-center">
                        <div class="post-content">
                            <div class="entry-meta meta-0 font-small mb-10">
                                @foreach($post->categories as $category)
                                    <a href="{{ $category->url }}"><span class="post-cat text-{{ ['warning', 'primary', 'info', 'success'][array_rand(['warning', 'primary', 'info', 'success'])] }}">{{ $category->name }}</span></a>
                                @endforeach
                            </div>
                            <h5 class="post-title font-weight-900 mb-20">
                                <a href="{{ $post->url }}">{{ $post->name }}</a>
                            </h5>
                            <div class="entry-meta meta-1 float-left font-small">
                                <span class="post-on">{{ $post->created_at->format('d/m/Y') }}</span>
                                {{-- <span class="time-reading has-dot">{{ number_format(strlen($post->content) / 200) }} {{ __('mins read') }}</span> --}}
                                <span class="post-by has-dot">{{ number_format($post->views) }} {{ __('views') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pagination-area mb-30 wow animated justify-content-start">
        {!! $posts->withQueryString()->links() !!}
    </div>
</div>
