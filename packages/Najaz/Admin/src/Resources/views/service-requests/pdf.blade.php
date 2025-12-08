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

        @page { margin-bottom: 150px; }

        body {
            margin: 0;
            padding: 40px;
            background: #fff;
            font-family: "{{ $fontFamily['regular'] }}", "DejaVu Sans", sans-serif;
        }

        .page {
            width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
        }

        b, th {
            font-family: "{{ $fontFamily['bold'] }}";
        }

        .page-header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .page-header table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .page-header td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 8px;
        }

        .header-section {
            font-size: 15px;
            line-height: 1.8;
            color: #000;
        }

        .header-section p,
        .header-section div,
        .header-section span {
            margin: 0;
            padding: 0;
        }

        .header-section-right { text-align: right; }
        .header-section-center { text-align: center; }
        .header-section-left { text-align: left; direction:ltr; }

        .header-section img {
            max-width: 100%;
            max-height: 80px;
            display: block;
            margin: 0;
        }

        .page-content {
            padding-top: 5px;
            font-size: 14px;
            color: #000;
            line-height: 1.9;
        }

        .document-content {
            margin: 0;
            padding: 0;
        }

        .document-content > *:first-child {
            margin-top: 0 !important;
        }

        .page-footer-fixed {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background:#fff;
            border-top: 2px solid #000;
            padding: 6px 0;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            vertical-align: middle;
            padding: 0 8px;
        }

        .footer-text-merged {
            font-size: 11px;
            line-height: 1.5;
            color:#000;
            text-align: center;
        }

        .footer-stamp-col {
            width: 25%;
            text-align: center;
        }

        .stamp-label {
            font-size: 11px;
            margin-bottom: 4px;
        }

        .stamp-box {
            width:80px;
            height:80px;
            border:2px solid #000;
            margin:0 auto;
        }
    </style>
</head>

<body dir="{{ core()->getCurrentLocale()->direction }}">

@php
    $footerText = core()->getConfigData('documents.official.footer.footer_text');
@endphp

<div class="page-footer-fixed">
    <table class="footer-table">
        <tr>
            <td class="footer-text-merged" colspan="2">
                @if ($footerText) {!! $footerText !!} @endif
            </td>

            <td class="footer-stamp-col">
                <div class="stamp-label">مكان الختم</div>
                @php
                    $stampImage = core()->getConfigData('documents.official.footer.stamp_image');
                    $stampFinal = null;
                    if ($stampImage) {
                        $path = storage_path('app/public/' . $stampImage);
                        if (file_exists($path)) {
                            $stampFinal = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
                        } else {
                            $stampFinal = $stampImage;
                        }
                    }
                @endphp

                @if ($stampFinal)
                    <img src="{{ $stampFinal }}" style="max-width:80px; max-height:80px;">
                @else
                    <div class="stamp-box"></div>
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="page">
    <div class="page-header">
        <table>
            <tr>
                <td class="header-section-right">
                    <div class="header-section">
                        @php
                            $headerRight = core()->getConfigData('documents.official.header.header_right');
                        @endphp
                        @if ($headerRight) {!! $headerRight !!} @endif
                    </div>
                </td>

                <td class="header-section-center">
                    <div class="header-section">
                        @php
                            $logoImage = null;
                            if ($template->header_image) {
                                $path = storage_path('app/public/' . $template->header_image);
                                if (file_exists($path)) {
                                    $logoImage = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
                                }
                            }
                            if (!$logoImage) {
                                $configLogo = core()->getConfigData('documents.official.header.header_center');
                                if ($configLogo) {
                                    $path = storage_path('app/public/' . $configLogo);
                                    if (file_exists($path)) {
                                        $logoImage = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
                                    } else {
                                        $logoImage = $configLogo;
                                    }
                                }
                            }
                        @endphp
                        @if ($logoImage) <img src="{{ $logoImage }}"> @endif
                    </div>
                </td>

                <td class="header-section-left">
                    <div class="header-section">
                        @php
                            $headerLeft = core()->getConfigData('documents.official.header.header_left');
                        @endphp
                        @if ($headerLeft) {!! $headerLeft !!} @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-content">
        <div class="document-content">
            {!! $content !!}
        </div>
    </div>
</div>

</body>
</html>
