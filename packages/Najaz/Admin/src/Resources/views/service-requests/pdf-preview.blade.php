<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="{{ app()->getLocale() }}" dir="{{ core()->getCurrentLocale()->direction }}">
<head>
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    @php
        $locale    = app()->getLocale();
        $direction = core()->getCurrentLocale()->direction ?? 'rtl';
        $isRtl     = ($direction === 'rtl');

        // Use XB Riyaz font - mPDF will load it via fontdata configuration
        // The font name in mPDF fontdata is 'xbriyaz' (lowercase)
        $xbRiyazRegular = str_replace('\\', '/', public_path('fonts/XB Riyaz.ttf'));
        $xbRiyazBold    = str_replace('\\', '/', public_path('fonts/XB RiyazBd.ttf'));

        $hasXBRiyaz = file_exists($xbRiyazRegular) && file_exists($xbRiyazBold);

        // mPDF uses lowercase font name from fontdata: 'xbriyaz'
        $baseFont = $hasXBRiyaz ? 'xbriyaz' : 'dejavusans';
    @endphp

    <style>
        @page {
            margin-top: 44mm;
            margin-bottom: 34mm;
            margin-left: 15mm;
            margin-right: 15mm;

            odd-header-name: page-header;
            even-header-name: page-header;
            odd-footer-name: page-footer;
            even-footer-name: page-footer;
        }

        /* XB Riyaz font is loaded via mPDF fontdata configuration - no need for @font-face */
        /* The font name in CSS should match the fontdata key: 'xbriyaz' */

        * { box-sizing: border-box; }

        body{
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: "{{ $baseFont }}", "DejaVu Sans", Arial, sans-serif;
            direction: {{ $direction }};
            font-size: 11pt;
            line-height: 1.75;
        }

        /* LTR numbers inside RTL */
        .ltr-override{
            direction: ltr;
            unicode-bidi: bidi-override;
            white-space: nowrap;
            display: inline-block;
        }

        /* =======================
           HEADER (FIXED HEIGHT)
           ======================= */
        .page-header-wrapper{
            height: 34mm;
            min-height: 34mm;
            max-height: 34mm;
            padding: 4mm 0 3mm 0;
            overflow: hidden;
        }

        .header-table{
            width: 100%;
            height: 21mm;
            min-height: 21mm;
            max-height: 21mm;
            border-collapse: collapse;
            table-layout: fixed;
            overflow: hidden;
        }
        .header-table td{
            height: 21mm;
            vertical-align: middle;
            padding: 0 3mm;
            overflow: hidden;
        }

        .header-col{ width: 36%; }
        .header-col.center{ width: 28%; text-align: center; }

        .header-section{
            line-height: 1.18;
            max-height: 21mm;
            overflow: hidden;
        }
        .header-section p,
        .header-section div,
        .header-section span{
            margin: 0;
            padding: 0;
        }

        .header-right{ text-align: right; }
        .header-left{ text-align: left; direction:ltr; unicode-bidi: plaintext; }

        /* تكبير شعار الهيدر */
        .header-logo{
            display:inline-block;
            max-height: 19mm;   /* كان 16mm */
            max-width: 68mm;    /* كان 52mm */
        }

        /* Divider lines */
        .header-divider{
            margin-top: 1.5mm;
            border-top: 0.7mm solid #000;
            border-bottom: 0.2mm solid #000;
            height: 0;
        }

        /* =======================
           Content
           ======================= */
        .document-content{ padding-top: 2mm; }

        .document-content p{
            margin: 0 0 4mm 0;
            text-align: justify;
            orphans: 3;
            widows: 3;
        }

        .document-content h1{
            font-size: 15pt;
            font-weight: bold;
            margin: 0 0 5mm 0;
            text-align: center;
            page-break-after: avoid;
            padding-bottom: 2mm;
            border-bottom: 0.35mm solid #111;
        }

        .document-content h2{
            font-size: 12.5pt;
            font-weight: bold;
            margin: 6mm 0 3mm 0;
            text-align: right;
            page-break-after: avoid;
            padding-bottom: 1.5mm;
            border-bottom: 0.2mm solid #333;
        }

        .document-content h3{
            font-size: 11.5pt;
            font-weight: bold;
            margin: 5mm 0 2mm 0;
            text-align: right;
            page-break-after: avoid;
        }

        .document-content table{
            width: 100%;
            border-collapse: collapse;
            margin: 5mm 0;
            border: 0.22mm solid #000;
        }
        .document-content thead{ display: table-header-group; }
        .document-content th,
        .document-content td{
            border: 0.18mm solid #000;
            padding: 2.5mm 3mm;
            vertical-align: top;
        }
        .document-content th{
            font-weight: bold;
            text-align: center;
            background: #f2f2f2;
        }
        .document-content tr{ page-break-inside: avoid; }

        /* =======================
           FOOTER (FIXED HEIGHT + CENTERED PAGE NUMBER)
           ======================= */
        .page-footer-wrapper{
            height: 26mm;
            min-height: 26mm;
            max-height: 26mm;
            padding: 3mm 0 0 0;
            overflow: hidden;
        }

        .footer-divider{
            border-top: 0.7mm solid #000;
            border-bottom: 0.2mm solid #000;
            height: 0;
            margin-bottom: 2mm;
        }

        .footer-table{
            width: 100%;
            height: 18mm;
            min-height: 18mm;
            max-height: 18mm;
            border-collapse: collapse;
            table-layout: fixed;
            overflow: hidden;
        }
        .footer-table td{
            height: 18mm;
            vertical-align: middle;
            padding: 0 2.5mm;
            overflow: hidden;
        }

        .footer-text{
            width: 50%;
            line-height: 1.25;
            text-align: right;
            max-height: 18mm;
            overflow: hidden;
        }

        .footer-center{
            width: 50%;
            text-align: center;
            font-size: 7.2pt;
            line-height: 1.15;
            max-height: 18mm;
            overflow: hidden;
        }
        .footer-center .line{ margin: 0.5mm 0; }
        .footer-center .label{ font-weight: bold; }
    </style>
</head>

<body>
@php
    $channelCode = core()->getRequestedChannelCode();

    $headerLeft   = core()->getConfigData('documents.official.header.header_left',   $channelCode, $locale) ?? '';
    $headerCenter = core()->getConfigData('documents.official.header.header_center', $channelCode, $locale) ?? '';
    $headerRight  = core()->getConfigData('documents.official.header.header_right',  $channelCode, $locale) ?? '';

    $footerText = core()->getConfigData('documents.official.footer.footer_text', $channelCode, $locale) ?? '';

    /* Auto font scaling (Header + Footer) */
    $plainLen = function ($html) {
        $t = strip_tags((string) $html);
        $t = preg_replace('/\s+/u', ' ', trim($t));
        return function_exists('mb_strlen') ? mb_strlen($t, 'UTF-8') : strlen($t);
    };

    $pickFont = function ($len, $basePt, $minPt, array $steps) {
        $size = $basePt;
        foreach ($steps as $step) {
            if ($len >= $step[0]) $size = $step[1];
        }
        if ($size < $minPt) $size = $minPt;
        return $size;
    };

    $lenHeaderRight = $plainLen($headerRight);
    $lenHeaderLeft  = $plainLen($headerLeft);

    $headerRightPt = $pickFont($lenHeaderRight, 10.0, 7.8, [
        [110, 9.4],
        [160, 8.8],
        [220, 8.2],
        [300, 7.8],
    ]);
    $headerLeftPt  = $pickFont($lenHeaderLeft,  10.0, 7.8, [
        [110, 9.4],
        [160, 8.8],
        [220, 8.2],
        [300, 7.8],
    ]);

    $lenFooterText = $plainLen($footerText);
    $footerTextPt = $pickFont($lenFooterText, 8.6, 6.8, [
        [120, 8.0],
        [180, 7.4],
        [260, 7.0],
        [340, 6.8],
    ]);

    /* Build header logo */
    $headerLogo = '';
    if (!empty($headerCenter)) {
        try {
            if (filter_var($headerCenter, FILTER_VALIDATE_URL)) {
                $headerLogo = '<img src="' . e($headerCenter) . '" class="header-logo" alt="logo">';
            } else {
                $filePath = null;

                if (substr($headerCenter, 0, 1) == '/') {
                    $filePath = public_path($headerCenter);
                } else {
                    $storagePath = storage_path('app/public/' . $headerCenter);
                    $publicPath  = public_path('storage/' . $headerCenter);

                    if (file_exists($storagePath)) $filePath = $storagePath;
                    elseif (file_exists($publicPath)) $filePath = $publicPath;
                }

                if ($filePath && file_exists($filePath)) {
                    $imageData = base64_encode(file_get_contents($filePath));
                    $imageInfo = getimagesize($filePath);
                    $mimeType  = $imageInfo ? $imageInfo['mime'] : 'image/png';
                    $headerLogo = '<img src="data:' . $mimeType . ';base64,' . $imageData . '" class="header-logo" alt="logo">';
                } else {
                    $maybePublic = public_path(ltrim($headerCenter, '/'));
                    if (file_exists($maybePublic)) {
                        $headerLogo = '<img src="' . asset($headerCenter) . '" class="header-logo" alt="logo">';
                    }
                }
            }
        } catch (\Exception $e) {
            $headerLogo = '';
        }
    }
@endphp

<htmlpageheader name="page-header">
    <div class="page-header-wrapper">
        <table class="header-table">
            <tr>
                <td class="header-col header-right">
                    <div class="header-section" style="font-size: {{ $headerRightPt }}pt;">
                        {!! $headerRight !!}
                    </div>
                </td>

                <td class="header-col center">
                    <div class="header-section">
                        {!! $headerLogo !!}
                    </div>
                </td>

                <td class="header-col header-left">
                    <div class="header-section" style="font-size: {{ $headerLeftPt }}pt;">
                        {!! $headerLeft !!}
                    </div>
                </td>
            </tr>
        </table>

        <div class="header-divider"></div>
    </div>
</htmlpageheader>

<htmlpagefooter name="page-footer">
    <div class="page-footer-wrapper">
        <div class="footer-divider"></div>

        <table class="footer-table">
            <tr>
                <td class="footer-text" style="font-size: {{ $footerTextPt }}pt;">
                    {!! $footerText !!}
                </td>

                <td class="footer-center">
                    <div class="line">
                        <span class="label">الصفحة:</span>
                        <span class="ltr-override">{PAGENO}</span>
                        <span class="ltr-override">/</span>
                        <span class="ltr-override">{nbpg}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</htmlpagefooter>

<sethtmlpageheader name="page-header" value="on" show-this-page="1" />
<sethtmlpagefooter name="page-footer" value="on" show-this-page="1" />

<div class="document-content">
    {!! $content !!}
</div>

</body>
</html>

