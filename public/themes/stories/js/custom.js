$(document).ready(function () {
    $(document).on("click", ".post-content-wrapper img, .single-content figure img", function () {
        const src = $(this).attr("src");
        Fancybox.show([
            {
                src: src,
                type: "image"
            }
        ]);
    });
});
