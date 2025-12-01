<?php

namespace Najaz\Service\Enums;

enum FileExtensionEnum: string
{
    // Documents
    case PDF = 'pdf';
    case DOC = 'doc';
    case DOCX = 'docx';
    case XLS = 'xls';
    case XLSX = 'xlsx';
    case PPT = 'ppt';
    case PPTX = 'pptx';
    case TXT = 'txt';
    case RTF = 'rtf';
    case CSV = 'csv';
    
    // Images
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case GIF = 'gif';
    case BMP = 'bmp';
    case WEBP = 'webp';
    case SVG = 'svg';
    case ICO = 'ico';
    
    // Archives
    case ZIP = 'zip';
    case RAR = 'rar';
    case SEVENZ = '7z';
    case TAR = 'tar';
    case GZ = 'gz';

    /**
     * Get label for the extension.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PDF => 'PDF (.pdf)',
            self::DOC => 'Word (.doc)',
            self::DOCX => 'Word (.docx)',
            self::XLS => 'Excel (.xls)',
            self::XLSX => 'Excel (.xlsx)',
            self::PPT => 'PowerPoint (.ppt)',
            self::PPTX => 'PowerPoint (.pptx)',
            self::TXT => 'Text (.txt)',
            self::RTF => 'Rich Text (.rtf)',
            self::CSV => 'CSV (.csv)',
            self::JPG => 'JPEG (.jpg)',
            self::JPEG => 'JPEG (.jpeg)',
            self::PNG => 'PNG (.png)',
            self::GIF => 'GIF (.gif)',
            self::BMP => 'BMP (.bmp)',
            self::WEBP => 'WebP (.webp)',
            self::SVG => 'SVG (.svg)',
            self::ICO => 'ICO (.ico)',
            self::ZIP => 'ZIP (.zip)',
            self::RAR => 'RAR (.rar)',
            self::SEVENZ => '7Z (.7z)',
            self::TAR => 'TAR (.tar)',
            self::GZ => 'GZIP (.gz)',
        };
    }

    /**
     * Get all available file extensions with their labels.
     *
     * @return array
     */
    public static function getAll(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases()
        );
    }

    /**
     * Get document extensions only.
     *
     * @return array
     */
    public static function getDocuments(): array
    {
        $documentCases = [
            self::PDF, self::DOC, self::DOCX, self::XLS, self::XLSX,
            self::PPT, self::PPTX, self::TXT, self::RTF, self::CSV,
        ];
        
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            $documentCases
        );
    }

    /**
     * Get image extensions only.
     *
     * @return array
     */
    public static function getImages(): array
    {
        $imageCases = [
            self::JPG, self::JPEG, self::PNG, self::GIF, self::BMP,
            self::WEBP, self::SVG, self::ICO,
        ];
        
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            $imageCases
        );
    }

    /**
     * Get archive extensions only.
     *
     * @return array
     */
    public static function getArchives(): array
    {
        $archiveCases = [
            self::ZIP, self::RAR, self::SEVENZ, self::TAR, self::GZ,
        ];
        
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            $archiveCases
        );
    }
}

