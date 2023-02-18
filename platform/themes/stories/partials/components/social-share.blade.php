<ul class="social-share">
    <li><span title="social share"><i class="elegant-icon social_share"></i></span></li>
    {{-- zalo --}}
    <li>
        {{-- <a class="zalo-share-button zalo" title="Share Zalo"
            rel="nofollow" data-href="{{ $post->url }}" data-oaid="2194457286054493180"
            data-customize="true">
            <img src="{{ Theme::asset()->url('images/icon/share_zalo_bg_white.webp') }}" alt="shareZalo">
        </a> --}}
        <a class="zalo" href="{{ $post->url }}" title="Share Zalo"
            data-href="{{ $post->url }}" data-oaid="2194457286054493180"
            data-customize="true">
            <img src="{{ Theme::asset()->url('images/icon/share_zalo_bg_white.webp') }}" width="20" height="20" alt="shareZalo">
        </a>
    </li>
    {{-- facebook --}}
    <li>
        <a class="fb-share-button fb" href="{{ $post->url }}" title="Share Facebook"
            target="_blank"
            data-href="{{ $post->url }}"
            data-title="{{ $post->description }}">
            <img src="{{ Theme::asset()->url('images/icon/share_facebook.svg') }}" width="20" height="20"
                alt="shareFacebook">
        </a>
    </li>
    {{-- messenger --}}
    <li>
        <a class="fb-mess-share-button mess" href="{{ $post->url }}" title="Share Messenger" target="_blank"
            data-href="{{ $post->url }}"
            data-title="{{ $post->description }}">
            <img src="{{ Theme::asset()->url('images/icon/share_messenger.svg') }}" width="20" height="20"
                alt="shareMessenger">
        </a>
    </li>
    {{-- twitter --}}
    <li>
        <a class="twitter-share-button tw" href="{{ $post->url }}" title="Share Twitter" target="_blank"
            data-href="{{ $post->url }}"
            data-title="{{ $post->description }}">
            <img src="{{ Theme::asset()->url('images/icon/share_twitter.svg') }}" width="20" height="20"
                alt="shareTwitter">
        </a>
    </li>
    {{-- copy --}}
    <li>
        <a class="copy btn-copy" title="Share Copy Link" href="{{ $post->url }}" data-href="{{ $post->url }}">
            <img src="{{ Theme::asset()->url('images/icon/share_copy.svg') }}" width="20" height="20" alt="shareCopy">
        </a>
    </li>
</ul>
