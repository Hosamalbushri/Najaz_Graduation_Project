<?php

namespace Najaz\Admin\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

trait PDFHandler
{
    /**
     * Download PDF.
     *
     * @param  string      $contentHtml  المحتوى فقط (بدون هيدر/فوتر/HTML)
     * @param  string|null $fileName
     * @return \Illuminate\Http\Response
     */
    protected function downloadPDF(string $contentHtml, ?string $fileName = null)
    {
        if (is_null($fileName)) {
            $fileName = Str::random(32);
        }

        $direction = core()->getCurrentLocale()->direction;

        if ($direction === 'rtl') {
            // RTL: نبني صفحة كاملة بـ mPDF (هيدر + فوتر + ختم + محتوى)
            $fullHtml = $this->buildRtlLayout($contentHtml);
            $fullHtml = mb_convert_encoding($fullHtml, 'HTML-ENTITIES', 'UTF-8');

            $mPDF = new Mpdf([
                'margin_left'   => 40,
                'margin_right'  => 40,
                'margin_top'    => 140,
                'margin_bottom' => 120,
            ]);

            $mPDF->SetDirectionality($direction);
            $mPDF->SetDisplayMode('fullpage');

            // لا نستخدم SetHTMLHeaderByName / SetHTMLFooterByName
            // mPDF سيقرأ htmlpageheader/htmlpagefooter و sethtmlpageheader/sethtmlpagefooter من داخل الـ HTML نفسه

            $mPDF->WriteHTML($this->adjustArabicAndPersianContent($fullHtml));

            return response()->streamDownload(
                fn () => print($mPDF->Output('', 'S')),
                $fileName . '.pdf'
            );
        }

        // LTR: نبني صفحة كاملة بسيطة لـ DomPDF
        $fullHtml = $this->buildLtrLayout($contentHtml);
        $fullHtml = mb_convert_encoding($fullHtml, 'HTML-ENTITIES', 'UTF-8');

        return Pdf::loadHTML($fullHtml)
            ->setPaper('A4', 'portrait')
            ->set_option('defaultFont', 'Courier')
            ->download($fileName . '.pdf');
    }

    /**
     * تصميم صفحة ثابتة للغات RTL (mPDF) بهيدر وفوتر وخانة ختم.
     * هنا يُمرَّر فقط المحتوى (بدون أي هيدر أو فوتر).
     */
    protected function buildRtlLayout(string $content): string
    {
        $locale      = app()->getLocale();
        $channelCode = core()->getRequestedChannelCode();

        $headerLeft   = core()->getConfigData('documents.official.header.header_left',   $channelCode, $locale) ?? '';
        $headerCenter = core()->getConfigData('documents.official.header.header_center', $channelCode, $locale) ?? '';
        $headerRight  = core()->getConfigData('documents.official.header.header_right',  $channelCode, $locale) ?? '';

        $headerLogo = $this->buildHeaderLogo($headerCenter);

        $footerText = core()->getConfigData('documents.official.footer.footer_text', $channelCode, $locale) ?? '';

        $stampImageConfig = core()->getConfigData('documents.official.footer.stamp_image', $channelCode, $locale);
        $stampHtml = $this->buildStampHtml($stampImageConfig);

        return <<<HTML
<!DOCTYPE html>
<html lang="{$locale}" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background:#ffffff;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: rtl;
            color:#000000;
        }

        .page-header-wrapper {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }

        .page-header-wrapper table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .page-header-wrapper td {
            width: 33.33%;
            vertical-align: top;
            padding: 0 8px;
        }

        .header-section {
            font-size: 15px;
            line-height: 1.8;
        }

        .header-section p,
        .header-section div,
        .header-section span {
            margin: 0;
            padding: 0;
        }

        .header-section-right { text-align: right; }
        .header-section-center { text-align: center; }
        .header-section-left  { text-align: left; direction:ltr; }

        .header-logo {
            max-width: 200px;
            max-height: 100px;
            display:block;
            margin:0 auto;
        }

        .page-content {
            margin: 0 40px;
            font-size: 14px;
            line-height: 1.9;
        }

        .document-content {
            margin: 0;
            padding: 0;
        }

        .document-content > *:first-child {
            margin-top: 0 !important;
        }

        .page-footer-wrapper {
            border-top: 2px solid #000;
            padding-top: 6px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-table td {
            vertical-align: middle;
            padding: 0 6px;
        }

        .footer-text-merged {
            font-size: 11px;
            line-height: 1.5;
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
            width: 80px;
            height: 80px;
            border: 2px solid #000;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<htmlpageheader name="page-header">
    <div class="page-header-wrapper">
        <table>
            <tr>
                <td class="header-section-right">
                    <div class="header-section">{$headerRight}</div>
                </td>
                <td class="header-section-center">
                    <div class="header-section">{$headerLogo}</div>
                </td>
                <td class="header-section-left">
                    <div class="header-section">{$headerLeft}</div>
                </td>
            </tr>
        </table>
    </div>
</htmlpageheader>

<htmlpagefooter name="page-footer">
    <div class="page-footer-wrapper">
        <table class="footer-table">
            <tr>
                <td class="footer-text-merged" colspan="2">
                    {$footerText}
                </td>
                <td class="footer-stamp-col">
                    <div class="stamp-label">مكان الختم</div>
                    {$stampHtml}
                </td>
            </tr>
        </table>
    </div>
</htmlpagefooter>

<!-- تفعيل الهيدر والفوتر لكل الصفحات -->
<sethtmlpageheader name="page-header" value="on" show-this-page="1" />
<sethtmlpagefooter name="page-footer" value="on" />

<div class="page-content">
    <div class="document-content">
        {$content}
    </div>
</div>

</body>
</html>
HTML;
    }

    /**
     * تصميم صفحة ثابتة للغات LTR (DomPDF).
     */
    protected function buildLtrLayout(string $content): string
    {
        $locale      = app()->getLocale();
        $direction   = 'ltr';
        $channelCode = core()->getRequestedChannelCode();

        $headerLeft   = core()->getConfigData('documents.official.header.header_left',   $channelCode, $locale) ?? '';
        $headerCenter = core()->getConfigData('documents.official.header.header_center', $channelCode, $locale) ?? '';
        $headerRight  = core()->getConfigData('documents.official.header.header_right',  $channelCode, $locale) ?? '';
        $footerText   = core()->getConfigData('documents.official.footer.footer_text',   $channelCode, $locale) ?? '';

        $headerLogo = $this->buildHeaderLogo($headerCenter);

        $headerHtml = '
        <div class="header-row">
            <div class="header-left">' . $headerLeft . '</div>
            <div class="header-center">' . $headerLogo . '</div>
            <div class="header-right">' . $headerRight . '</div>
        </div>';

        return <<<HTML
<!DOCTYPE html>
<html lang="{$locale}" dir="{$direction}">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <style>
        body {
            margin: 40px;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: {$direction};
            color:#000000;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .header-left,
        .header-center,
        .header-right {
            display: table-cell;
            vertical-align: top;
            padding: 0 10px;
        }

        .header-left  { text-align: left;  width: 33.33%; }
        .header-center{ text-align: center;width: 33.33%; }
        .header-right { text-align: right; width: 33.33%; }

        .header-logo {
            max-width: 200px;
            max-height: 100px;
            display:block;
            margin:0 auto;
        }

        .content {
            font-size: 14px;
            line-height: 1.9;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        {$headerHtml}
    </div>
    <div class="content">
        {$content}
    </div>
    <div class="footer">
        {$footerText}
    </div>
</body>
</html>
HTML;
    }

    /**
     * توليد HTML لشعار الهيدر من مسار/إعداد.
     */
    protected function buildHeaderLogo(?string $headerCenter): string
    {
        if (empty($headerCenter)) {
            return '';
        }

        try {
            if (filter_var($headerCenter, FILTER_VALIDATE_URL)) {
                return '<img src="' . e($headerCenter) . '" class="header-logo">';
            }

            $filePath = null;

            if (substr($headerCenter, 0, 1) === '/') {
                $filePath = public_path($headerCenter);
            } else {
                $storagePath = storage_path('app/public/' . $headerCenter);
                if (file_exists($storagePath)) {
                    $filePath = $storagePath;
                } else {
                    $publicPath = public_path('storage/' . $headerCenter);
                    if (file_exists($publicPath)) {
                        $filePath = $publicPath;
                    }
                }
            }

            if ($filePath && file_exists($filePath)) {
                $imageData = base64_encode(file_get_contents($filePath));
                $imageInfo = getimagesize($filePath);
                $mimeType  = $imageInfo ? $imageInfo['mime'] : 'image/png';

                return '<img src="data:' . $mimeType . ';base64,' . $imageData . '" class="header-logo">';
            }

            return '<img src="' . asset($headerCenter) . '" class="header-logo">';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * توليد HTML للختم (صورة أو مربع فارغ).
     */
    protected function buildStampHtml(?string $stampImageConfig): string
    {
        if (empty($stampImageConfig)) {
            return '<div class="stamp-box"></div>';
        }

        try {
            if (filter_var($stampImageConfig, FILTER_VALIDATE_URL)) {
                return '<img src="' . e($stampImageConfig) . '" style="max-width:80px;max-height:80px;">';
            }

            $filePath = null;

            if (substr($stampImageConfig, 0, 1) === '/') {
                $filePath = public_path($stampImageConfig);
            } else {
                $storagePath = storage_path('app/public/' . $stampImageConfig);
                if (file_exists($storagePath)) {
                    $filePath = $storagePath;
                } else {
                    $publicPath = public_path('storage/' . $stampImageConfig);
                    if (file_exists($publicPath)) {
                        $filePath = $publicPath;
                    }
                }
            }

            if ($filePath && file_exists($filePath)) {
                $imageData = base64_encode(file_get_contents($filePath));
                $imageInfo = getimagesize($filePath);
                $mimeType  = $imageInfo ? $imageInfo['mime'] : 'image/png';

                return '<img src="data:' . $mimeType . ';base64,' . $imageData . '" style="max-width:80px;max-height:80px;">';
            }

            return '<img src="' . asset($stampImageConfig) . '" style="max-width:80px;max-height:80px;">';
        } catch (\Exception $e) {
            return '<div class="stamp-box"></div>';
        }
    }

    /**
     * Adjust arabic and persian content.
     */
    protected function adjustArabicAndPersianContent(string $html)
    {
        $arabic = new \ArPHP\I18N\Arabic;

        $p = $arabic->arIdentify($html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        return $html;
    }
}
