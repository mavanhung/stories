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


    //Select2
    var sellerDefault = $('.select2').data('default');
    $(".select2").select2({
        data: [sellerDefault],
        ajax: {
            // url: "https://api.github.com/search/repositories",
            url: "/ajax/tiki-seller",
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: params.page * 30 < data.total_count,
                    },
                };
            },
            // cache: true,
        },
        language: {
            inputTooShort: function() {
                return 'Nhập tên cửa hàng cần tìm';
            },
            searching: function() {
                return 'Đang tìm kiếm...';
            },
            loadingMore: function() {
                return 'Đang tải thêm kết quả...';
            },
            errorLoading: function () {
                return 'Không thể tải kết quả';
            },
            noResults: function () {
                return "Không tìm thấy kết quả"
            },
        },
        placeholder: 'Tất cả',
        // placeholder: {
        //     id: '',
        //     text: "Tất cả"
        // },
        minimumInputLength: 1,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection,
    });


    function formatRepo(repo) {
        if (repo.loading) {
            return repo.text;
        }

        var $container = $(
            "<div class='select2-result-repository clearfix d-flex'>" +
                "<div class='select2-result-repository__avatar'><img width='50' src='" +
                repo.logo +
                "' /></div>" +
                "<div class='select2-result-repository__meta ml-10 d-flex align-items-center'>" +
                "<div class='select2-result-repository__title'></div>" +
                "</div>" +
                "</div>"
        );

        $container
            .find(".select2-result-repository__title")
            .text(repo.seller_name);

        return $container;
    }

    function formatRepoSelection(repo) {
        return repo.seller_name || repo.text;
    }

    $(document).on('click', '#refresh_btn', function(e) {
        $('input[name="qs"]').val('');
        $('.select2').val(null).trigger('change');
        window.showAlert("alert-success", "Làm mới bộ lọc thành công");
    });
});
