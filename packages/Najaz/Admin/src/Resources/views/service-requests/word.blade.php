<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>
        <!-- meta tags -->
        <meta
            http-equiv="Cache-control"
            content="no-cache"
        >

        <meta
            http-equiv="Content-Type"
            content="text/html; charset=utf-8"
        />

        @php
            $fontPath = [];

            // Get the locale code from request or fallback to app locale.
            $getLocale = $requestLocale ?? app()->getLocale();

            if (in_array($getLocale, ['ar', 'he', 'fa', 'tr', 'ru', 'uk'])) {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            } elseif ($getLocale == 'zh_CN') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansSC-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansSC-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans SC',
                    'bold'    => 'Noto Sans SC Bold',
                ];
            } elseif ($getLocale == 'ja') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansJP-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansJP-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans JP',
                    'bold'    => 'Noto Sans JP Bold',
                ];
            } elseif ($getLocale == 'hi_IN') {
                $fontPath = [
                    'regular' => asset('fonts/Hind-Regular.ttf'),
                    'bold'    => asset('fonts/Hind-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Hind',
                    'bold'    => 'Hind Bold',
                ];
            } elseif ($getLocale == 'bn') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansBengali-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansBengali-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans Bengali',
                    'bold'    => 'Noto Sans Bengali Bold',
                ];
            } elseif ($getLocale == 'sin') {
                $fontPath = [
                    'regular' => asset('fonts/NotoSansSinhala-Regular.ttf'),
                    'bold'    => asset('fonts/NotoSansSinhala-Bold.ttf'),
                ];

                $fontFamily = [
                    'regular' => 'Noto Sans Sinhala',
                    'bold'    => 'Noto Sans Sinhala Bold',
                ];
            } else {
                $fontFamily = [
                    'regular' => 'DejaVu Sans',
                    'bold'    => 'DejaVu Sans',
                ];
            }
        @endphp

        <!-- lang supports inclusion -->
        <style type="text/css">
            @if (! empty($fontPath['regular']))
                @font-face {
                    src: url({{ $fontPath['regular'] }}) format('truetype');
                    font-family: {{ $fontFamily['regular'] }};
                }
            @endif

            @if (! empty($fontPath['bold']))
                @font-face {
                    src: url({{ $fontPath['bold'] }}) format('truetype');
                    font-family: {{ $fontFamily['bold'] }};
                    font-style: bold;
                }
            @endif

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: {{ $fontFamily['regular'] }};
            }

            body {
                font-size: 12pt;
                color: #091341;
                font-family: "{{ $fontFamily['regular'] }}";
                padding: 20px;
            }

            b, th, strong {
                font-family: "{{ $fontFamily['bold'] }}";
            }

            .page-content {
                padding: 12px;
            }

            .page-header {
                border-bottom: 1px solid #E9EFFC;
                text-align: center;
                font-size: 24px;
                text-transform: uppercase;
                color: #000DBB;
                padding: 24px 0;
                margin: 0 0 20px 0;
            }

            .document-header-container {
                position: absolute;
                top: 20px;
                left: 20px;
                right: 20px;
            }

            .document-header-container.rtl {
                left: 20px;
                right: 20px;
            }

            .document-header-row {
                display: table;
                width: 100%;
                table-layout: fixed;
            }

            .document-header-left,
            .document-header-center,
            .document-header-right {
                display: table-cell;
                vertical-align: top;
                padding: 0 10px;
            }

            .document-header-left {
                text-align: left;
                width: 33.33%;
            }

            .document-header-center {
                text-align: center;
                width: 33.33%;
            }

            .document-header-right {
                text-align: right;
                width: 33.33%;
            }

            .document-header-logo {
                max-width: 200px;
                max-height: 100px;
                margin: 0 auto;
                display: block;
            }

            .document-header-placeholder {
                color: #999;
                font-style: italic;
                font-size: 9pt;
            }

            .logo-container {
                position: absolute;
                top: 20px;
                left: 20px;
            }

            .logo-container.rtl {
                left: auto;
                right: 20px;
            }

            .logo-container img {
                max-width: 200px;
                height: auto;
            }

            .page-header b {
                display: inline-block;
                vertical-align: middle;
            }

            .document-content {
                line-height: 1.6;
                margin: 20px 0;
            }

            .document-content p {
                margin-bottom: 10px;
            }

            .document-content h1 {
                font-size: 18pt;
                margin: 20px 0 10px 0;
            }

            .document-content h2 {
                font-size: 16pt;
                margin: 15px 0 8px 0;
            }

            .document-content h3 {
                font-size: 14pt;
                margin: 12px 0 6px 0;
            }

            .footer-text {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
                text-align: center;
                font-size: 10pt;
                color: #666;
            }
        </style>
    </head>

    <body dir="{{ $localeModel->direction ?? core()->getCurrentLocale()->direction }}">
        @php
            // Get request locale
            $requestLocale = $requestLocale ?? app()->getLocale();
            $channelCode = core()->getRequestedChannelCode();
            
            // Get header settings from system config (same way as invoice)
            $headerLeft = core()->getConfigData('documents.official.header.header_left', $channelCode, $requestLocale) ?? '';
            $headerCenter = core()->getConfigData('documents.official.header.header_center', $channelCode, $requestLocale) ?? '';
            $headerRight = core()->getConfigData('documents.official.header.header_right', $channelCode, $requestLocale) ?? '';
        @endphp

        <!-- Document Header (Left, Center, Right) - Same structure as invoice logo -->
        <div class="document-header-container {{ $localeModel->direction ?? core()->getCurrentLocale()->direction }}">
            <div class="document-header-row">
                <!-- Left Part -->
                <div class="document-header-left">
                    @if (!empty($headerLeft))
                        {!! $headerLeft !!}
                    @else
                        <span class="document-header-placeholder">
                            {{ trans('Admin::app.configuration.index.documents.official.header.header-left-placeholder', [], $requestLocale) }}
                        </span>
                    @endif
                </div>
                
                <!-- Center Part (Logo) - Same way as invoice -->
                <div class="document-header-center">
                    @if (!empty($headerCenter))
                        @php
                            $logoPath = public_path('storage/' . $headerCenter);
                            if (!file_exists($logoPath)) {
                                $logoPath = storage_path('app/public/' . $headerCenter);
                            }
                        @endphp
                        @if (file_exists($logoPath))
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" class="document-header-logo"/>
                        @else
                            <span class="document-header-placeholder">
                                {{ trans('Admin::app.configuration.index.documents.official.header.header-center-placeholder', [], $requestLocale) }}
                            </span>
                        @endif
                    @elseif ($template->header_image)
                        @php
                            $logoPath = public_path('storage/' . $template->header_image);
                            if (!file_exists($logoPath)) {
                                $logoPath = storage_path('app/public/' . $template->header_image);
                            }
                        @endphp
                        @if (file_exists($logoPath))
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" class="document-header-logo"/>
                        @else
                            <span class="document-header-placeholder">
                                {{ trans('Admin::app.configuration.index.documents.official.header.header-center-placeholder', [], $requestLocale) }}
                            </span>
                        @endif
                    @else
                        <span class="document-header-placeholder">
                            {{ trans('Admin::app.configuration.index.documents.official.header.header-center-placeholder', [], $requestLocale) }}
                        </span>
                    @endif
                </div>
                
                <!-- Right Part -->
                <div class="document-header-right">
                    @if (!empty($headerRight))
                        {!! $headerRight !!}
                    @else
                        <span class="document-header-placeholder">
                            {{ trans('Admin::app.configuration.index.documents.official.header.header-right-placeholder', [], $requestLocale) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="page">
            <!-- Header -->
            <div class="page-header">
                <b>@lang('Admin::app.service-requests.view.document')</b>
            </div>

            <div class="page-content">
                <!-- Document Content -->
                <div class="document-content">
                    {!! $content !!}
                </div>

                <!-- Footer Content - Same way as invoice -->
                @if (core()->getConfigData('documents.official.footer.footer_text', $channelCode, $requestLocale))
                    <div class="footer-text">
                        {{ core()->getConfigData('documents.official.footer.footer_text', $channelCode, $requestLocale) }}
                    </div>
                @elseif (!empty($footerText ?? $template->footer_text))
                    @php
                        $templateTranslation = $template->translate($requestLocale);
                        $templateFooterText = $footerText ?? ($templateTranslation?->footer_text ?? $template->footer_text);
                    @endphp
                    @if ($templateFooterText)
                        <div class="footer-text">
                            {!! $templateFooterText !!}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </body>
</html>

