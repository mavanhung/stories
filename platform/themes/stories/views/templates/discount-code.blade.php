<div class="coupon-card mb-30">
    <img src="{{ RvMedia::getImageUrl($discountCode->icon_url, null, false, RvMedia::getDefaultImage()) }}" class="logo">
    <h2>{{ $discountCode->seller ? $discountCode->seller->seller_name : 'Tiki' }}</h2>
    <h3>{{ $discountCode->label }}</h3>
    <p class="description">
        {{ $discountCode->short_description }}
        <i class="fa fa-info-circle ml-5 info" aria-hidden="true"></i>
        <div class="info_msg hidden" id="popover-cont">
            <ul class="long-description">
                @foreach (explode("\n", $discountCode->long_description) as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
    </p>
    <div class="coupon-row">
        <span class="cpnCode">{{ $discountCode->coupon_code }}</span>
        <span class="cpnBtn">Copy mã</span>
    </div>
    <p>Còn 2 ngày</p>
    <div class="circle1"></div>
    <div class="circle2"></div>
</div>
