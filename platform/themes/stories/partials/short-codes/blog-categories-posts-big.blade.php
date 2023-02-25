<div class="bg-grey pb-30">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @if (!empty($categories))
                    @foreach ($categories as $category)
                        <div class="post-module-2">
                            <div class="widget-header-1 position-relative mb-30">
                                <h5 class="mt-5 mb-30">{{ $category->name }}</h5>
                                <a class="post-module-2-readmore" href="{{ $category->slugable->key }}">Xem thêm</a>
                            </div>
                            <div class="loop-list loop-list-style-1">
                                <div class="row">
                                    @foreach($category->posts->sortByDesc('id')->take(6) as $post)
                                        <article class="col-md-4 mb-40">
                                            <div class="post-card-1 border-radius-10 hover-up">
                                                {!! Theme::partial('components.post-card', compact('post')) !!}
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
