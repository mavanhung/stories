<div class="coupon-card mb-30 hover-up-2 transition-normal">
    <div class="top">
        <div class="left">
            <img src="{{ RvMedia::getImageUrl($discountCode->icon_url, null, false, RvMedia::getDefaultImage()) }}" class="seller_logo" loading="lazy">
        </div>
        <div class="right">
            <a class="seller-name" href="{{ $discountCode->seller ? $discountCode->seller->seller_url : 'javascript:void(0);' }}" target="_blank">{{ $discountCode->seller ? $discountCode->seller->seller_name : 'Tiki' }}</a>
            <h3 class="label">{{ $discountCode->label }}</h3>
            <p class="short-description">
                {{ $discountCode->short_description }}
                <a data-toggle="collapse" href="#collapseExample{{$discountCode->id}}" role="button" aria-expanded="false" aria-controls="collapseExample" title="Bấm xem chi tiết">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                </a>
            </p>
        </div>
    </div>
    <div class="bottom">
        <div class="content">
            <div class="collapse" id="collapseExample{{$discountCode->id}}">
                <ul class="long-description">
                    @foreach (explode("\n", $discountCode->long_description) as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="coupon-row">
            <span class="cpnCode">{{ $discountCode->coupon_code }}</span>
            <span class="cpnBtn">Copy mã</span>
        </div>
        <p class="text-center">HSD: {{ date('d/m/Y', strtotime($discountCode->expired_at)) }}</p>
    </div>
</div>
