const App = {
    menuScroll: function () {
        var lastPS = window.pageYOffset;
        window.onscroll = function () {
            var currentPS = window.pageYOffset;
            if (lastPS > currentPS) {
                document.getElementById("hdFixed").style.top = "0";
            } else if (lastPS < 168) {
                document.getElementById("hdFixed").style.top = "0";
            } else {
                document.getElementById("hdFixed").style.top = "-100px";
            }
            lastPS = currentPS;
        };
    },
};

$(document).ready(function () {
    App.menuScroll();
    $(document).on(
        "click",
        ".post-content-wrapper img, .single-content figure img",
        function () {
            const src = $(this).attr("src");
            Fancybox.show([
                {
                    src: src,
                    type: "image",
                },
            ]);
        }
    );

    $(document).on("click", ".btn-copy", function () {
        const link = $(this).data("href");
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(link).select();
        document.execCommand("copy");
        $temp.remove();
        window.showAlert("alert-success", "Đã sao chép liên kết");
    });

    const popupCenter = ({ url, title, w, h }) => {
        // Fixes dual-screen position                             Most browsers      Firefox
        const dualScreenLeft =
            window.screenLeft !== undefined
                ? window.screenLeft
                : window.screenX;
        const dualScreenTop =
            window.screenTop !== undefined ? window.screenTop : window.screenY;

        const width = window.innerWidth
            ? window.innerWidth
            : document.documentElement.clientWidth
            ? document.documentElement.clientWidth
            : screen.width;
        const height = window.innerHeight
            ? window.innerHeight
            : document.documentElement.clientHeight
            ? document.documentElement.clientHeight
            : screen.height;

        const systemZoom = width / window.screen.availWidth;
        const left = (width - w) / 2 / systemZoom + dualScreenLeft;
        const top = (height - h) / 2 / systemZoom + dualScreenTop;
        const newWindow = window.open(
            url,
            title,
            `
          scrollbars=yes,
          width=${w / systemZoom},
          height=${h / systemZoom},
          top=${top},
          left=${left}
          `
        );

        if (window.focus) newWindow.focus();
    };

    $(document).on("click", ".fb-share-button", function () {
        var url = $(this).attr("data-href");
        var title = $(this).attr("data-title");
        url =
            "https://www.facebook.com/sharer/sharer.php?u=" +
            url +
            "&title=" +
            title;
        popupCenter({ url: url, title: title, w: 626, h: 436 });
    });

    $(document).on("click", ".fb-mess-share-button", function () {
        var url = $(this).attr("data-href");
        var title = $(this).attr("data-title");
        url =
            "http://www.facebook.com/dialog/send?app_id=717695893406776&display=popup&link=" +
            url +
            "&redirect_uri=" +
            url;
        popupCenter({ url: url, title: title, w: 626, h: 436 });
    });

    $(document).on("click", ".twitter-share-button", function () {
        var url = $(this).attr("data-href");
        var title = $(this).attr("data-title");
        url = "https://twitter.com/intent/tweet?url=" + url + "&text=" + title;
        popupCenter({ url: url, title: title, w: 626, h: 436 });
    });

    //Copy discount code Tiki
    var cpnBtn = $(".cpnBtn");

    $(document).on("click", ".cpnBtn", function () {
        var cpnCode = $(this).prev();
        navigator.clipboard.writeText(cpnCode.text());
        $(this).text("Đã copy");
        window.showAlert("alert-success", "Đã copy mã giảm giá");
        setTimeout(function () {
            cpnBtn.text("Copy mã");
        }, 3000);
    });

    $(".info")
        .popover({
            html: true,
            trigger: "manual",
            placement: "bottom",
            content: function () {
                return $(this).parents('.coupon-card').find('.info_msg').html();
            },
        })
        .on("mouseenter", function () {
            var _this = this;
            $(this).popover("show");
            $(".popover").on("mouseleave", function () {
                $(_this).popover("hide");
            });
        })
        .on("mouseleave", function () {
            var _this = this;
            setTimeout(function () {
                if (!$(".popover:hover").length) {
                    $(_this).popover("hide");
                }
            }, 100);
        });

    //Discount code select2
    $('.select2').select2();
});
