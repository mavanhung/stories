<div class="post-module-3">
    <div class="loop-list loop-list-style-1">
        @foreach($posts as $post)
            <article class="hover-up-2 transition-normal wow fadeInUp animated">
                <div class="row mb-40 list-style-2">
                    <div class="col-md-4">
                        <div class="post-thumb position-relative border-radius-5">
                            <div class="img-hover-slide border-radius-5 position-relative" style="background-image: url({{ RvMedia::getImageUrl($post->image, 'small', false, RvMedia::getDefaultImage()) }})">
                                <a class="img-link" href="{{ $post->url }}"></a>
                            </div>
                            <ul class="social-share">
                                {{-- <li><a href="#"><i class="elegant-icon social_share"></i></a></li>
                                <li><a class="fb" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url) }}&title={{ $post->description }}" title="{{ __('Share on Facebook') }}" target="_blank"><i class="elegant-icon social_facebook"></i></a></li>
                                <li><a class="tw" href="https://twitter.com/intent/tweet?url={{ urlencode($post->url) }}&text={{ $post->description }}" target="_blank" title="{{ __('Tweet now') }}"><i class="elegant-icon social_twitter"></i></a></li>
                                <li><a class="pt" href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($post->url) }}&summary={{ rawurldecode($post->description) }}" target="_blank" title="{{ __('Share on Linkedin') }}"><i class="elegant-icon social_linkedin"></i></a></li> --}}

                                <li><a href="#"><i class="elegant-icon social_share"></i></a></li>
                                {{-- zalo --}}
                                <li>
                                    <a class="zalo" title="Share Zalo" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url) }}&title={{ $post->description }}">
                                        <img src="{{ Theme::asset()->url('images/icon/share_zalo_bg_white.webp') }}" alt="shareZalo">
                                    </a>
                                </li>
                                {{-- facebook --}}
                                <li>
                                    <a class="fb" title="Share Facebook" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url) }}&title={{ $post->description }}">
                                        <img src="{{ Theme::asset()->url('images/icon/share_facebook.svg') }}" alt="shareFacebook">
                                    </a>
                                </li>
                                {{-- messenger --}}
                                <li>
                                    <a class="mess" title="Share Messenger" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($post->url) }}&title={{ $post->description }}">
                                        <img src="{{ Theme::asset()->url('images/icon/share_messenger.svg') }}" alt="shareMessenger">
                                    </a>
                                </li>
                                {{-- twitter --}}
                                <li>
                                    <a class="tw" title="Share Twitter" target="_blank" href="https://twitter.com/intent/tweet?url={{ urlencode($post->url) }}&text={{ $post->description }}">
                                        <img src="{{ Theme::asset()->url('images/icon/share_twitter.svg') }}" alt="shareTwitter">
                                    </a>
                                </li>
                                {{-- copy --}}
                                <li>
                                    <a class="copy btn-copy" title="Share Copy Link" href="javascript:void(0);" data-href="{{ $post->url }}">
                                        <img src="{{ Theme::asset()->url('images/icon/share_copy.svg') }}" alt="shareCopy">
                                    </a>
                                </li>
                            </ul>
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
                            <div class="entry-meta meta-1 float-left font-small text-uppercase">
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

    <div class="pagination-area mb-30 wow fadeInUp animated justify-content-start">
        {!! $posts->withQueryString()->links() !!}
    </div>
</div>
