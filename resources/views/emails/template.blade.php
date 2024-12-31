<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $templateName }}</title>
</head>
<body>
    @foreach ($body as $component)
        @switch($component['type'])
            @case('text')
                <p style="font-family: {{ $component['attributes']['font'] }};
                           font-size: {{ $component['attributes']['size'] }};
                           color: {{ $component['attributes']['font_color'] }};">
                    {!! nl2br(e($component['attributes']['content'])) !!}
                </p>
                @break

            @case('image')
                <img src="{{ $message->embed(public_path('storage/' . $component['attributes']['choose_image'])) }}" 
                alt="Logo"
                style="height: {{ $component['attributes']['height'] }}px;
                width: {{ $component['attributes']['width'] }}px;
                display: block;
                margin: 0 auto;">
                @break

            @case('button')
                <a href="{{ $component['attributes']['url'] }}" 
                   style="display: inline-block;
                          font-family: {{ $component['attributes']['font'] }};
                          color: {{ $component['attributes']['font_color'] }};
                          text-align: {{ $component['attributes']['align'] }};
                          padding: 10px 20px;
                          text-decoration: none;
                          background-color: #007BFF;">
                    {{ $component['attributes']['button_text'] }}
                </a>
                @break

            @case('logo')
            <img src="{{ $message->embed(public_path('storage/' . $component['attributes']['choose_image'])) }}" 
                alt="Logo"
                style="height: {{ $component['attributes']['height'] }}px;
                width: {{ $component['attributes']['width'] }}px;
                display: block;
                margin: 0 auto;">
                @break

            @case('divider')
                <hr style="border: none;
                           border-top: {{ $component['attributes']['height'] }}px {{ $component['attributes']['line_type'] }} #000;
                           width: {{ $component['attributes']['width'] }}%;">
                @break

            @case('social')
                <div style="text-align: center;">
                    @if (isset($component['attributes']['facebook']))
                        <a href="{{ $component['attributes']['facebook'] }}"><img src="facebook-icon.png" alt="Facebook"></a>
                    @endif
                    @if (isset($component['attributes']['twitter']))
                        <a href="{{ $component['attributes']['twitter'] }}"><img src="twitter-icon.png" alt="Twitter"></a>
                    @endif
                    @if (isset($component['attributes']['instagram']))
                        <a href="{{ $component['attributes']['instagram'] }}"><img src="instagram-icon.png" alt="Instagram"></a>
                    @endif
                </div>
                @break

            @case('footer')
                <footer style="text-align: center; font-size: 12px;">
                <p>&copy; {{ $component['attributes']['year'] }} {{ $component['attributes']['location'] }}. Contact: {{ $component['attributes']['email'] }}</p>
            </footer>
                @break

            @case('code')
                {!! $component['attributes']['own_html_code'] !!}
                @break

            @case('video')
            @if($component['attributes']['video_type'] === 'youtube')
            <p style="text-align: center;">
            <a href="https://www.youtube.com/watch?v={{ $component['attributes']['url'] }}" target="_blank">
                Watch the video on YouTube
            </a>
            </p>
            @endif
                @break
        @endswitch
    @endforeach
</body>
</html>
