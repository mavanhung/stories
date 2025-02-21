!(function (e) {
    "use strict";
    e(window).on("load", function () {
        e(".preloader").delay(450).fadeOut("slow");
    }),
        // new WOW().init(),
        e(document).ready(function () {
            var s, i, t, o, l, n;
            e("button.search-icon").on("click", function () {
                e("body").toggleClass("open-search-form"),
                    e(".mega-menu-item").removeClass("open");
            }),
                e(".search-close").on("click", function () {
                    e("body").removeClass("open-search-form");
                }),
                e(".off-canvas-toggle").on("click", function () {
                    e("body").toggleClass("canvas-opened");
                }),
                e(".dark-mark").on("click", function () {
                    e("body").removeClass("canvas-opened");
                }),
                e(".off-canvas-close").on("click", function () {
                    e("body").removeClass("canvas-opened");
                }),
                document.querySelector.bind(document),
                // new PerfectScrollbar(".custom-scrollbar"),
                e(".play-video").length &&
                    e(".play-video").magnificPopup({
                        disableOn: 700,
                        type: "iframe",
                        mainClass: "mfp-fade",
                        removalDelay: 160,
                        preloader: !1,
                        fixedContentPos: !1,
                    }),
                // e.scrollUp({
                //     scrollName: "scrollUp",
                //     topDistance: "300",
                //     topSpeed: 300,
                //     animation: "fade",
                //     animationInSpeed: 200,
                //     animationOutSpeed: 200,
                //     scrollText: '<i class="elegant-icon arrow_up"></i>',
                //     activeOverlay: !1,
                // }),
                e(window).on("scroll", function () {
                    e(window).scrollTop() < 245
                        ? e(".header-sticky").removeClass("sticky-bar")
                        : e(".header-sticky").addClass("sticky-bar");
                }),
                e(".sticky-sidebar").theiaStickySidebar(),
                e(".slide-fade").slick({
                    infinite: !0,
                    dots: !1,
                    arrows: !0,
                    autoplay: !0,
                    autoplaySpeed: 3e3,
                    fade: !0,
                    fadeSpeed: 1500,
                    prevArrow:
                        '<button type="button" class="slick-prev" aria-label="slick-prev"><i class="elegant-icon arrow_left"></i></button>',
                    nextArrow:
                        '<button type="button" class="slick-next" aria-label="slick-next"><i class="elegant-icon arrow_right"></i></button>',
                    appendArrows: ".arrow-cover",
                }),
                e(".carausel-3-columns").slick({
                    dots: !1,
                    infinite: !0,
                    speed: 2e3,
                    arrows: !0,
                    autoplay: !0,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    loop: !0,
                    adaptiveHeight: !0,
                    prevArrow:
                        '<button type="button" class="slick-prev" aria-label="slick-prev"><i class="elegant-icon arrow_left"></i></button>',
                    nextArrow:
                        '<button type="button" class="slick-next" aria-label="slick-next"><i class="elegant-icon arrow_right"></i></button>',
                    appendArrows: ".carausel-3-columns-wrapper",
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: { slidesToShow: 3, slidesToScroll: 1 },
                        },
                        {
                            breakpoint: 992,
                            settings: { slidesToShow: 2, slidesToScroll: 1 },
                        },
                        {
                            breakpoint: 481,
                            settings: { slidesToShow: 1, slidesToScroll: 1 },
                        },
                    ],
                }),
                e(".featured-slider-2-items").slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: !1,
                    dots: !1,
                    fade: !0,
                    asNavFor: ".featured-slider-2-nav",
                }),
                e(".featured-slider-2-nav").slick({
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    vertical: !0,
                    asNavFor: ".featured-slider-2-items",
                    dots: !1,
                    arrows: !1,
                    focusOnSelect: !0,
                    verticalSwiping: !0,
                }),
                e(".featured-slider-3-items").slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: !0,
                    dots: !1,
                    fade: !0,
                    prevArrow:
                        '<button type="button" class="slick-prev" aria-label="slick-prev"><i class="elegant-icon arrow_left"></i></button>',
                    nextArrow:
                        '<button type="button" class="slick-next" aria-label="slick-next"><i class="elegant-icon arrow_right"></i></button>',
                    appendArrows: ".slider-3-arrow-cover",
                }),
                e(".sub-mega-menu .nav-pills > a").on(
                    "mouseover",
                    function (s) {
                        e(this).tab("show");
                    }
                ),
                (s = e("ul#mobile-menu")).length &&
                    s.slicknav({
                        prependTo: ".mobile_menu",
                        closedSymbol: "+",
                        openedSymbol: "-",
                    }),
                ((i = function (e, s, i) {
                    (this.toRotate = s),
                        (this.el = e),
                        (this.loopNum = 0),
                        (this.period = parseInt(i, 10) || 2e3),
                        (this.txt = ""),
                        this.tick(),
                        (this.isDeleting = !1);
                }).prototype.tick = function () {
                    var e = this.loopNum % this.toRotate.length,
                        s = this.toRotate[e];
                    this.isDeleting
                        ? (this.txt = s.substring(0, this.txt.length - 1))
                        : (this.txt = s.substring(0, this.txt.length + 1)),
                        (this.el.innerHTML =
                            '<span class="wrap">' + this.txt + "</span>");
                    var i = this,
                        t = 200 - 100 * Math.random();
                    this.isDeleting && (t /= 2),
                        this.isDeleting || this.txt !== s
                            ? this.isDeleting &&
                              "" === this.txt &&
                              ((this.isDeleting = !1),
                              this.loopNum++,
                              (t = 500))
                            : ((t = this.period), (this.isDeleting = !0)),
                        setTimeout(function () {
                            i.tick();
                        }, t);
                }),
                (window.onload = function () {
                    for (
                        var e = document.getElementsByClassName("typewrite"),
                            s = 0;
                        s < e.length;
                        s++
                    ) {
                        var t = e[s].getAttribute("data-type"),
                            o = e[s].getAttribute("data-period");
                        t && new i(e[s], JSON.parse(t), o);
                    }
                    var l = document.createElement("style");
                    (l.type = "text/css"),
                        (l.innerHTML =
                            ".typewrite > .wrap { border-right: 0.05em solid #5869DA}"),
                        document.body.appendChild(l);
                }),
                e(".menu li.menu-item-has-children").on("click", function () {
                    var s = e(this);
                    s.hasClass("open")
                        ? (s.removeClass("open"),
                          s.find("li").removeClass("open"),
                          s.find("ul").slideUp(200))
                        : (s.addClass("open"),
                          s.children("ul").slideDown(200),
                          s.siblings("li").children("ul").slideUp(200),
                          s.siblings("li").removeClass("open"),
                          s.siblings("li").find("li").removeClass("open"),
                          s.siblings("li").find("ul").slideUp(200));
                }),
                (o = e(document).height()),
                (l = e(window).height()),
                e(window).on("scroll", function () {
                    (t = (e(window).scrollTop() / (o - l)) * 100),
                        e(".scroll-progress").width(t + "%");
                }),
                (function () {
                    if (e(".grid").length) {
                        var s = e(".grid").masonry({
                            itemSelector: ".grid-item",
                            percentPosition: !0,
                            columnWidth: ".grid-sizer",
                            gutter: 0,
                        });
                        s.imagesLoaded().progress(function () {
                            s.masonry();
                        });
                    }
                })(),
                // (n = e("select")).length && n.niceSelect(),
                (function () {
                    if (
                        ((e.fn.vwScroller = function (s) {
                            var i = !1,
                                t = e(document),
                                o = e(window);
                            s = e.extend(
                                {
                                    delay: 500,
                                    position: 0.7,
                                    visibleClass: "",
                                    invisibleClass: "",
                                },
                                s
                            );
                            var l = e.proxy(function () {
                                    var e =
                                        t.scrollTop() >
                                        (t.height() - o.height()) * s.position;
                                    !i && e ? n() : i && !e && a();
                                }, this),
                                n = e.proxy(function () {
                                    (i = !0),
                                        s.visibleClass &&
                                            this.addClass(s.visibleClass),
                                        s.invisibleClass &&
                                            this.removeClass(s.invisibleClass);
                                }, this),
                                a = e.proxy(function () {
                                    (i = !1),
                                        s.visibleClass &&
                                            this.removeClass(s.visibleClass),
                                        s.invisibleClass &&
                                            this.addClass(s.invisibleClass);
                                }, this);
                            return setInterval(l, s.delay), this;
                        }),
                        e.fn.vwScroller)
                    ) {
                        var s = e(".single-more-articles");
                        s.vwScroller({
                            visibleClass: "single-more-articles--visible",
                            position: 0.55,
                        }),
                            s
                                .find(".single-more-articles-close-button")
                                .on("click", function () {
                                    s.hide();
                                });
                    }
                    e("button.single-more-articles-close").on(
                        "click",
                        function () {
                            e(".single-more-articles").removeClass(
                                "single-more-articles--visible"
                            );
                        }
                    );
                })(),
                e("#news-flash").vTicker({
                    speed: 800,
                    pause: 3e3,
                    animation: "fade",
                    mousePause: !1,
                    showItems: 1,
                }),
                e("#date-time").vTicker({
                    speed: 800,
                    pause: 3e3,
                    animation: "fade",
                    mousePause: !1,
                    showItems: 1,
                });
        });
})(jQuery);
