<?php

namespace Najaz\Admin\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

trait PDFHandler
{
    protected function downloadPDF(string $html, ?string $fileName = null)
    {
        $fileName ??= Str::random(32);

        $direction = core()->getCurrentLocale()->direction ?? 'ltr';

        // IMPORTANT:
        // - mPDF: keep UTF-8 (do NOT convert to HTML-ENTITIES)
        // - DomPDF: HTML-ENTITIES is sometimes helpful
        if ($direction === 'rtl') {
            $mPDF = $this->makeMpdfForRtl();

            $mPDF->SetDirectionality('rtl');
            $mPDF->SetDisplayMode('fullpage');

            // For mPDF: do not use ArPHP glyph conversion (better typography + official fonts)
            $mPDF->WriteHTML($html);

            return response()->streamDownload(
                fn () => print($mPDF->Output('', 'S')),
                $fileName . '.pdf'
            );
        }

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        return PDF::loadHTML($this->adjustArabicAndPersianContent($html))
            ->setPaper('A4', 'portrait')
            ->set_option('defaultFont', 'DejaVu Sans')
            ->download($fileName . '.pdf');
    }

    /**
     * mPDF config for RTL Arabic documents with "official" Arabic fonts.
     */
    protected function makeMpdfForRtl(): Mpdf
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs      = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData          = $defaultFontConfig['fontdata'];

        $customFontDir = public_path('fonts');

        $hasXBRiyaz = file_exists($customFontDir . '/XB Riyaz.ttf')
            && file_exists($customFontDir . '/XB RiyazBd.ttf');

        $hasXBZar = file_exists($customFontDir . '/XB Zar.ttf')
            && file_exists($customFontDir . '/XB ZarBd.ttf');

        $hasAmiri = file_exists($customFontDir . '/Amiri-Regular.ttf')
            && file_exists($customFontDir . '/Amiri-Bold.ttf');

        $hasNoto = file_exists($customFontDir . '/NotoNaskhArabic-Regular.ttf')
            && file_exists($customFontDir . '/NotoNaskhArabic-Bold.ttf');

        $extraFontData = [];
        $defaultFont   = 'dejavusans';

        if ($hasXBRiyaz) {
            $defaultFont = 'xbriyaz';
            $extraFontData['xbriyaz'] = [
                'R' => 'XB Riyaz.ttf',
                'B' => 'XB RiyazBd.ttf',
                // Improve Arabic shaping/typography in mPDF:
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        } elseif ($hasXBZar) {
            $defaultFont = 'xbzar';
            $extraFontData['xbzar'] = [
                'R' => 'XB Zar.ttf',
                'B' => 'XB ZarBd.ttf',
                // Improve Arabic shaping/typography in mPDF:
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        } elseif ($hasAmiri) {
            $defaultFont = 'amiri';
            $extraFontData['amiri'] = [
                'R' => 'Amiri-Regular.ttf',
                'B' => 'Amiri-Bold.ttf',
                // Improve Arabic shaping/typography in mPDF:
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        } elseif ($hasNoto) {
            $defaultFont = 'notonaskharabic';
            $extraFontData['notonaskharabic'] = [
                'R' => 'NotoNaskhArabic-Regular.ttf',
                'B' => 'NotoNaskhArabic-Bold.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        }

        $tempDir = storage_path('app/mpdf-temp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',

            // keep margins 0 to let your CSS @page margins control layout
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,

            'tempDir' => $tempDir,

            'fontDir'  => array_merge($fontDirs, [$customFontDir]),
            'fontdata' => $fontData + $extraFontData,

            'default_font' => $defaultFont,

            // Auto-select correct font/language when mixed text exists
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);
    }

    /**
     * Keep this for DomPDF branch only.
     */
    protected function adjustArabicAndPersianContent(string $html): string
    {
        $arabic = new \ArPHP\I18N\Arabic;
        $p = $arabic->arIdentify($html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $html = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        return $html;
    }
}
