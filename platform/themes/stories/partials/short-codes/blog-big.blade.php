<div class="loop-list loop-list-style-1">
    <div class="row">
        @foreach($posts as $post)
            <article class="col-md-6 mb-40 wow fadeInUp animated">
                <div class="post-card-1 border-radius-10 hover-up">
                    <div class="post-thumb thumb-overlay img-hover-slide position-relative" style="background-image: url({{ RvMedia::getImageUrl($post->image, null, false, RvMedia::getDefaultImage()) }})">
                        <a class="img-link" href="{{ $post->url }}" title="{{ $post->name }}"></a>
                        @includeIf('theme.stories::partials.components.social-share', ['post' => $post])
                    </div>
                    <div class="post-content p-30">
                        <div class="entry-meta meta-0 font-small mb-10">
                            @foreach($post->categories as $category)
                                <a href="{{ $category->url }}" title="{{ $category->name }}"><span class="post-cat text-{{ ['warning', 'primary', 'info', 'success'][array_rand(['warning', 'primary', 'info', 'success'])] }}">{{ $category->name }}</span></a>
                            @endforeach
                        </div>
                        <div class="d-flex post-card-content">
                            <h5 class="post-title mb-20 font-weight-900">
                                <a href="{{ $post->url }}" title="{{ $post->name }}">{{ $post->name }}</a>
                            </h5>
                            <div class="post-excerpt mb-25 font-small text-muted">
                                <p>{{ $post->description }}</p>
                            </div>
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
</div>

<div class="pagination-area mb-30 wow fadeInUp animated justify-content-start">
    {!! $posts->withQueryString()->links() !!}
</div>
