<ul class="social-share">
    <li><a href="javascript:void(0);" title="social share"><i class="elegant-icon social_share"></i></a></li>
    {{-- zalo --}}
    <li>
        {{-- <a class="zalo-share-button zalo" title="Share Zalo"
            rel="nofollow" data-href="{{ $post->url }}" data-oaid="2194457286054493180"
            data-customize="true">
            <img src="{{ Theme::asset()->url('images/icon/share_zalo_bg_white.webp') }}" alt="shareZalo">
        </a> --}}
        <a class="zalo" href="javascript:void(0);" title="Share Zalo"
            rel="nofollow" data-href="{{ $post->url }}" data-oaid="2194457286054493180"
            data-customize="true">
            <img src="{{ Theme::asset()->url('images/icon/share_zalo_bg_white.webp') }}" alt="shareZalo">
        </a>
    </li>
    {{-- facebook --}}
    <li>
        <a class="fb-share-button fb" href="javascript:void(0);" title="Share Facebook"
            target="_blank" rel="nofollow"
            data-href="{{ $post->url }}"
            data-title="{{ $post->description }}">
            <img src="{{ Theme::asset()->url('images/icon/share_facebook.svg') }}"
                alt="shareFacebook">
        </a>
    </li>
    {{-- messenger --}}
    <li>
        <a class="fb-mess-share-button mess" href="javascript:void(0);" title="Share Messenger" target="_blank"
            rel="nofollow"
            data-href="{{ $post->url }}"
            data-title="{{ $post->description }}">
            <img src="{{ Theme::asset()->url('images/icon/share_messenger.svg') }}"
                alt="shareMessenger">
        </a>
    </li>
    {{-- twitter --}}
    <li>
        <a class="twitter-share-button tw" href="javascript:void(0);" title="Share Twitter" target="_blank"
            rel="nofollow"
            data-href="{{ $post->url }}"
            data-title="{{ $post->description }}">
            <img src="{{ Theme::asset()->url('images/icon/share_twitter.svg') }}"
                alt="shareTwitter">
        </a>
    </li>
    {{-- copy --}}
    <li>
        <a class="copy btn-copy" title="Share Copy Link" rel="nofollow" href="javascript:void(0);" data-href="{{ $post->url }}">
            <img src="{{ Theme::asset()->url('images/icon/share_copy.svg') }}" alt="shareCopy">
        </a>
    </li>
</ul>
