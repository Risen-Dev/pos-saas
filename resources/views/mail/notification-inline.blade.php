@php
    // Render the full GrowPOS email HTML, then inline the compiled utilities.
    // The notification MailMessage supplies greeting, action, intro/outro, etc.
    $markup = view('vendor.notifications.email', get_defined_vars())->render();
    $stylesheet = public_path('build/mail.css');

    if (! is_file($stylesheet) || filesize($stylesheet) === 0) {
        throw new RuntimeException('GrowPOS email CSS missing. Run npm run build:mail.');
    }

    $css = file_get_contents($stylesheet);
    $inliner = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
@endphp
{!! $inliner->convert($markup, $css) !!}
