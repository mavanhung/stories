<div class="site-bottom pt-30 bg-grey">
    <div class="container">
        <div class="carausel-3-columns-wrapper sidebar-widget widget-latest-posts">
            <div class="widget-header-2 position-relative mb-30">
                <h5 class="mt-5 mb-30">{!! clean($title) !!}</h5>
            </div>
            <div class="carausel-3-columns">
                @foreach (get_featured_categories(10, ['slugable', 'image']) as $category)
                    <a href="{{ $category->url }}" title="{{ $category->name}}">
                        {{-- <div class="carausel-3-columns-item d-flex bg-grey has-border p-25 hover-up-2 transition-normal border-radius-5">
                            <div class="post-thumb post-thumb-64 d-flex mr-15 border-radius-5 img-hover-scale">
                                @if (count($category->image->meta_value) > 0)
                                <img src="{{ RvMedia::getImageUrl($category->image->meta_value[0], 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $category->name }}" width="64" height="64" loading="lazy">
                                @endif
                            </div>
                            <div class="post-content media-body">
                                <h6 class="font-weight-bold">{{ $category->name }}</h6>
                                <p class="text-muted font-medium">{{ Str::limit($category->description, 65) }}</p>
                            </div>
                        </div> --}}

                        {{-- <div class="carausel-3-columns-item bg-white has-border p-15 hover-up-2 transition-normal border-radius-5">
                            <div class="post-thumb post-thumb-64 mb-15 border-radius-5 img-hover-scale d-flex justify-content-center">
                                @if (count($category->image->meta_value) > 0)
                                <img src="{{ RvMedia::getImageUrl($category->image->meta_value[0], 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $category->name }}" width="64" height="64" loading="lazy">
                                @endif
                            </div>
                            <div class="post-content media-body text-center">
                                <h6 class="font-weight-bold mb-0">{{ $category->name }}</h6>
                            </div>
                        </div> --}}
                        <div class="carausel-3-columns-item d-flex align-items-center has-border hover-up-2 transition-normal border-radius-5 p-25 mb-15 discount__code-item">
                            <div class="mr-15 img-hover-scale">
                                <img class="border-radius-5" src="{{ RvMedia::getImageUrl($category->image->meta_value[0], 'thumb', false, RvMedia::getDefaultImage()) }}" width="70" height="70" alt="{{ $category->name }}" loading="lazy">
                            </div>
                            <div class="discount__code-body">
                                <h5>{{ $category->name }}</h5>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
