<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="{{ app()->getLocale() }}" dir="{{ core()->getCurrentLocale()->direction }}">
<head>
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    @php
        $fontPath = [];
        $getLocale = app()->getLocale();

        if (in_array($getLocale, ['ar','he','fa','tr','ru','uk'])) {
            $fontPath = [
                'regular' => asset('fonts/static/NotoNaskhArabic-Regular.ttf'),
                'bold'    => asset('fonts/static/NotoNaskhArabic-Bold.ttf')
            ];
            $fontFamily = [
                'regular' => 'Noto Naskh Arabic',
                'bold'    => 'Noto Naskh Arabic'
            ];
        } else {
            $fontFamily = [
                'regular' => 'DejaVu Sans',
                'bold'    => 'DejaVu Sans'
            ];
        }
    @endphp

    <style>
        @if (!empty($fontPath['regular']))
        @font-face {
            font-family: '{{ $fontFamily['regular'] }}';
            src: url('{{ $fontPath['regular'] }}') format('truetype');
        }
        @endif

        @if (!empty($fontPath['bold']))
        @font-face {
            font-family: '{{ $fontFamily['bold'] }}';
            src: url('{{ $fontPath['bold'] }}') format('truetype');
            font-weight: bold;
        }
        @endif

        body {
            margin: 0;
            padding: 0;
            background:#fff;
            font-family: "{{ $fontFamily['regular'] }}", "DejaVu Sans", sans-serif;
            color:#000;
        }

        main.page-content {
            font-size: 14px;
            line-height: 1.9;
            padding: 0;
        }

        .document-content {
            margin: 0;
            padding: 0;
        }

        .document-content > *:first-child {
            margin-top: 0 !important;
        }

        .document-content p {
            page-break-inside: avoid;
            page-break-after: avoid;
            orphans: 2;
            widows: 2;
        }

        .document-content p:empty,
        .document-content p.empty-paragraph {
            page-break-after: avoid !important;
            page-break-before: avoid !important;
            line-height: 0.1 !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 0.1em !important;
            min-height: 0 !important;
            overflow: hidden;
        }
    </style>
</head>

<body dir="{{ core()->getCurrentLocale()->direction }}">
<main class="page-content">
    <div class="document-content">
        {!! $content !!}
    </div>
</main>

</body>
</html>
