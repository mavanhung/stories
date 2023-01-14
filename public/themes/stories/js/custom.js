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
    $(document).on("click", ".post-content-wrapper img, .single-content figure img", function () {
        const src = $(this).attr("src");
        Fancybox.show([
            {
                src: src,
                type: "image"
            }
        ]);
    });

    $(document).on("click", ".btn-copy", function () {
        const link = $(this).data("href");
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(link).select();
        document.execCommand("copy");
        $temp.remove();
        window.showAlert('alert-success', 'Đã sao chép liên kết');
    });
});
