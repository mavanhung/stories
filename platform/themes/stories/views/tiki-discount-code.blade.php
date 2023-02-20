<div class="bg-grey">
    <div class="container">
        <div class="archive-header pt-50">
            <h2 class="font-weight-900">{{ SeoHelper::getTitle() }}</h2>
            {!! Theme::partial('breadcrumbs') !!}
            <div class="bt-1 border-color-1 mt-30 mb-50"></div>
        </div>
        <div class="form-search-discount-code-wrapper mb-50">
            <form action="#" id="header-search-people" class="form-area" novalidate="novalidate" autocomplete="off">
                <div class="row">
                    <div class="col-md-10 col-12">
                        <div class="styled-input wide multi">
                            <div class="first-name" id="input-first-name">
                                <input type="text" name="qs" id="fn" value="{{ app('request')->input('qs') }}" autocomplete="off"
                                    data-placeholder-focus="false" required />
                                <label>Nhập nội dung tìm kiếm</label>
                                <svg class="icon--check" width="21px" height="17px" viewBox="0 0 21 17" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
                                        stroke-linecap="round">
                                        <g id="UI-Elements-Forms" transform="translate(-255.000000, -746.000000)"
                                            fill-rule="nonzero" stroke="#81B44C" stroke-width="3">
                                            <polyline id="Path-2"
                                                points="257 754.064225 263.505943 760.733489 273.634603 748"></polyline>
                                        </g>
                                    </g>
                                </svg>
                                <svg class="icon--error" width="15px" height="15px" viewBox="0 0 15 15" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"
                                        stroke-linecap="round">
                                        <g id="UI-Elements-Forms" transform="translate(-550.000000, -747.000000)"
                                            fill-rule="nonzero" stroke="#D0021B" stroke-width="3">
                                            <g id="Group" transform="translate(552.000000, 749.000000)">
                                                <path d="M0,11.1298982 L11.1298982,-5.68434189e-14" id="Path-2-Copy"></path>
                                                <path d="M0,11.1298982 L11.1298982,-5.68434189e-14" id="Path-2-Copy-2"
                                                    transform="translate(5.564949, 5.564949) scale(-1, 1) translate(-5.564949, -5.564949) ">
                                                </path>
                                            </g>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                            <div class="state" id="select-state">
                                <select class="select2" name="seller" data-default="{{ $seller }}">
                                    {{-- <option value="">Tất cả</option>
                                    <option value="0">Tiki</option>
                                    @foreach ($sellers as $seller)
                                        <option value="{{ $seller->id }}" data-img="{{ RvMedia::getImageUrl($seller->logo, null, false, RvMedia::getDefaultImage()) }}">
                                            {{ $seller->seller_name }}
                                        </option>
                                    @endforeach --}}
                                </select>
                                <label>Cửa hàng</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-6 no-pad-left-10">
                        <button type="button" class="primary-btn serach-btn d-flex align-items-center justify-content-center" id="refresh_btn">
                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="col-md-1 col-6 no-pad-left-10">
                        <button type="submit" class="primary-btn serach-btn d-flex align-items-center justify-content-center" id="submit_btn" aria-label="Search seller">
                            <i class="elegant-icon icon_search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-6">
                <p>Tổng số mã: {{ $discountCodes->total() }}</p>
            </div>
            <div class="col-6 text-right">
                <p>Ngày cập nhật: {{ date('d/m/Y', strtotime(now())) }}</p>
            </div>
        </div>
        <div class="row">
            @foreach ($discountCodes as $discountCode)
                <div class="col-md-4">
                    @includeIf('theme.stories::views.templates.discount-code', ['discountCode' => $discountCode])
                </div>
            @endforeach
        </div>
        <div class="pagination-area pb-30 justify-content-start">
            {!! $discountCodes->withQueryString()->links() !!}
        </div>
        <div class="page-content">
        </div>
    </div>
</div>
