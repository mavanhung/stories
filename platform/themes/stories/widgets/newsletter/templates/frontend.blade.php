<div class="col-lg-4 col-md-6">
    <div class="sidebar-widget widget_newsletter mb-30">
        <div class="widget-header-2 position-relative mb-30">
            <h5 class="mt-5 mb-30">{{ $config['name'] }}</h5>
        </div>
        <div class="newsletter">
            <p class="font-medium">{{ __('Subscribe to our newsletter and get our newest updates right on your inbox.') }}</p>
            <form class="input-group form-subcriber mt-30 d-flex newsletter-form" action="{{ route('public.newsletter.subscribe') }}" method="post">
                @csrf
                @if (setting('enable_captcha') && is_plugin_active('captcha'))
                    <div class="form-group">
                        {!! Captcha::display() !!}
                    </div>
                @endif
                <input type="email" name="email" class="form-control bg-white font-small" placeholder="{{ __('Enter your email') }}">
                <button class="btn bg-primary text-white" type="submit" aria-label="{{ __('Subscribe') }}">{{ __('Subscribe') }}</button>
            </form>
        </div>
    </div>
</div>
