$(document).ready(function () {
    var showError = message => {
        window.showAlert('alert-danger', message);
    }

    var showSuccess = message => {
        window.showAlert('alert-success', message);
    }

    var handleError = data => {
        if (typeof (data.errors) !== 'undefined' && data.errors.length) {
            handleValidationError(data.errors);
        } else if (typeof (data.responseJSON) !== 'undefined') {
            if (typeof (data.responseJSON.errors) !== 'undefined') {
                if (data.status === 422) {
                    handleValidationError(data.responseJSON.errors);
                }
            } else if (typeof (data.responseJSON.message) !== 'undefined') {
                showError(data.responseJSON.message);
            } else {
                $.each(data.responseJSON, (index, el) => {
                    $.each(el, (key, item) => {
                        showError(item);
                    });
                });
            }
        } else {
            showError(data.statusText);
        }
    }

    var handleValidationError = errors => {
        let message = '';

        $.each(errors, (index, item) => {
            if (message !== '') {
                message += '<br />';
            }
            message += item;
        });

        showError(message);
    }

    //Hàm import thông tin người gửi comment vào form
    let importCommentAuthor = function () {
        let commentAuthor = window.localStorage.getItem('comment_author');
        if(commentAuthor) {
            commentAuthor = JSON.parse(window.localStorage.getItem('comment_author'))
            $('.form-comment-post').find('input[name="name"]').val(commentAuthor.name);
            $('.form-comment-post').find('input[name="email"]').val(commentAuthor.email);
            $('.form-comment-post').find('input[name="phone"]').val(commentAuthor.phone);
            $('#saveCommentAuthor').prop('checked', true);
        }
    }

    importCommentAuthor();

    //Hàm lưu thông tin người gửi comment vào local Storage
    let saveCommentAuthor = function () {
        let saveCommentAuthor = $('#saveCommentAuthor');
        if(saveCommentAuthor.is(':checked')) {
            const author = {
                name: $('.form-comment-post').find('input[name="name"]').val(),
                email: $('.form-comment-post').find('input[name="email"]').val(),
                phone: $('.form-comment-post').find('input[name="phone"]').val()
            }
            window.localStorage.setItem('comment_author', JSON.stringify(author));
        }else {
            window.localStorage.removeItem('comment_author');
        }
    }

    let imagesReviewBuffer = [];
    let setImagesFormReview = function (input) {
        const dT =
            new ClipboardEvent("").clipboardData || // Firefox < 62 workaround exploiting https://bugzilla.mozilla.org/show_bug.cgi?id=1422655
            new DataTransfer(); // specs compliant (as of March 2018 only Chrome)
        for (let file of imagesReviewBuffer) {
            dT.items.add(file);
        }
        input.files = dT.files;
        loadPreviewImage(input);
    };

    let loadPreviewImage = function (input) {
        let $uploadText = $(".image-upload__text");
        const maxFiles = $(input).data("max-files");
        let filesAmount = input.files.length;

        if (maxFiles) {
            if (filesAmount >= maxFiles) {
                $uploadText
                    .closest(".image-upload__uploader-container")
                    .addClass("d-none");
            } else {
                $uploadText
                    .closest(".image-upload__uploader-container")
                    .removeClass("d-none");
            }
            $uploadText.text(filesAmount + "/" + maxFiles);
        } else {
            $uploadText.text(filesAmount);
        }
        const viewerList = $(".image-viewer__list");
        const $template = $("#review-image-template").html();

        viewerList.addClass("is-loading");
        viewerList.find(".image-viewer__item").remove();

        if (filesAmount) {
            for (let i = filesAmount - 1; i >= 0; i--) {
                viewerList.prepend($template.replace("__id__", i));
            }
            for (let j = filesAmount - 1; j >= 0; j--) {
                let reader = new FileReader();
                reader.onload = function (event) {
                    viewerList
                        .find(".image-viewer__item[data-id=" + j + "]")
                        .find("img")
                        .attr("src", event.target.result);
                };
                reader.readAsDataURL(input.files[j]);
            }
        }
        viewerList.removeClass("is-loading");
    };

    $(document).on(
        "change",
        ".form-comment-post input[type=file]",
        function (event) {
            event.preventDefault();
            let input = this;
            let $input = $(input);
            let maxSize = $input.data("max-size");
            Object.keys(input.files).map(function (i) {
                if (maxSize && input.files[i].size / 1024 > maxSize) {
                    let message = $input
                        .data("max-size-message")
                        .replace("__attribute__", input.files[i].name)
                        .replace("__max__", maxSize);
                    window.showAlert("alert-danger", message);
                } else {
                    imagesReviewBuffer.push(input.files[i]);
                }
            });

            let filesAmount = imagesReviewBuffer.length;
            const maxFiles = $input.data("max-files");
            if (maxFiles && filesAmount > maxFiles) {
                imagesReviewBuffer.splice(
                    filesAmount - maxFiles - 1,
                    filesAmount - maxFiles
                );
            }

            setImagesFormReview(input);
        }
    );

    $(document).on(
        "click",
        ".form-comment-post .image-viewer__icon-remove",
        function (event) {
            event.preventDefault();
            const $this = $(event.currentTarget);
            let id = $this.closest(".image-viewer__item").data("id");
            imagesReviewBuffer.splice(id, 1);

            let input = $(".form-comment-post input[type=file]")[0];
            setImagesFormReview(input);
        }
    );

    //Gửi bình luận
    $(document).on('click', '.form-comment-post a.btn-send-comment', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(this).prop('disabled', true).addClass('btn-disabled').addClass('button-loading');
        const $form = $(this).closest('form');

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'POST',
            cache: false,
            url: $form.prop('action'),
            data: new FormData($form[0]),
            contentType: false,
            processData: false,
            success: res => {
                if (!res.error) {
                    saveCommentAuthor();
                    if(!$('#saveCommentAuthor').is(':checked')) {
                        $form.find('input[name="name"]').val('');
                        $form.find('input[name="email"]').val('');
                        $form.find('input[name="phone"]').val('');
                    }
                    $form.find('select').val(0);
                    $form.find('textarea').val('');
                    $form.find('input[type="file"]').val('');

                    let item = $(".image-viewer__item");
                    if(item.length > 0) {
                        imagesReviewBuffer.splice(0, item.length);
                        let input = $(".form-comment-post input[type=file]");
                        setImagesFormReview(input);
                    }
                    $('.be-comment-wrapper').prepend(res.data);
                    showSuccess('Thêm bình luận thành công!');
                } else {
                    showError(res.message);
                }

                $(this).prop('disabled', false).removeClass('btn-disabled').removeClass('button-loading');
            },
            error: res => {
                $(this).prop('disabled', false).removeClass('btn-disabled').removeClass('button-loading');
                handleError(res, $form);
            }
        });
    });

    //Bấm nút xem thêm
    $(document).on('click', 'a.btn-comment-readmore', function (event) {
        event.preventDefault();
        event.stopPropagation();
        const _this = $(this);
        $(this).prop('disabled', true).addClass('btn-disabled').addClass('button-loading');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: $(this).data('url'),
            success: res => {
                if (!res.error) {
                    $(this).remove();
                    $('.be-comment-wrapper').append(res.data);
                }

                $(this).prop('disabled', false).removeClass('btn-disabled').removeClass('button-loading');
            },
            error: res => {
                $(this).prop('disabled', false).removeClass('btn-disabled').removeClass('button-loading');
            }
        });
    });

    //Load bình luận ngay khi tải xong page
    var post_id = $('form.form-comment-post input[name="posts_id"]').val();
    if(post_id) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: `${window.location.origin}/ajax/comment/${post_id}`,
            beforeSend: () => {
                $('.be-comment-wrapper').addClass('comment-loadding');
            },
            success: res => {
                if (!res.error) {
                    $('.be-comment-wrapper').html(res.data);
                }

                $('.be-comment-wrapper').removeClass('comment-loadding');
            },
            error: res => {
                $('.be-comment-wrapper').removeClass('comment-loadding');
            }
        });
    }

    Fancybox.bind('[data-fancybox="gallery"]', {
        Image: {
            zoom: false,
        },
    });
});
