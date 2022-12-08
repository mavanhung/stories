@foreach ($comments as $key => $comment)
    <div class="be-comment">
        <div class="be-img-comment">
            <img src="{{ Theme::asset()->url('images/avatar.jpg') }}" alt="avatar user" class="be-ava-comment">
            <span class="be-comment-name" title="{{ $comment->name }}">{{ \Illuminate\Support\Str::limit($comment->name, 25, $end='...') }}</span>
        </div>
        <div class="be-comment-content">
            <div class="be-comment-rate">
                <span class="be-comment-time">
                    <i class="fa fa-clock-o"></i>
                    {{ $comment->created_at->format('d/m/Y H:i') }}
                </span>
                <div class="comment-rate d-inline-block">
                    <div class="comment-rating" style="width: {{ $comment->star*20 }}%;"></div>
                </div>
            </div>
            <div class="be-comment-text">
                {{ $comment->comment }}
                @if ($comment->images)
                    <div class="block__images">
                        @foreach (json_decode($comment->images) as $image)
                            <a data-fancybox="comment-images-{{$comment->id}}" data-src="{{ RvMedia::getImageUrl($image) }}">
                                <img src="{{ RvMedia::getImageUrl($image) }}" alt="{{ pathinfo($image)['filename'] }}">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach
@if ($comments->nextPageUrl())
    <a class="btn btn-primary btn-comment-readmore" data-url="{{ $comments->nextPageUrl() }}">Xem thêm</a>
@endif
