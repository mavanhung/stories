<div class="form-search-discount-code-wrapper mb-50">
    <form action="#" id="header-search-people" class="form-area" novalidate="novalidate" autocomplete="off">
        <div class="row">
            <div class="col-md-11">
                <div class="styled-input wide multi">
                    <div class="first-name" id="input-first-name">
                        <input type="text" name="fn" id="fn" autocomplete="off"
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
                        <select class="select2" name="seller">
                            <option value="All">All</option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District Of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="PR">Puerto Rico</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select>
                        <label>Cửa hàng</label>
                    </div>
                </div>
            </div>
            <div class="col-md-1 no-pad-left-10">
                <button type="submit" class="primary-btn serach-btn d-flex align-items-center justify-content-center" id="submit_btn">
                    <i class="elegant-icon icon_search"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<div class="row">
    @foreach ($discountCodes as $discountCode)
        <div class="col-md-4">
            @includeIf('theme.stories::views.templates.discount-code', ['discountCode' => $discountCode])
        </div>
    @endforeach
</div>
<div class="pagination-area mb-30 wow fadeInUp animated justify-content-start">
    {!! $discountCodes->withQueryString()->links() !!}
</div>
