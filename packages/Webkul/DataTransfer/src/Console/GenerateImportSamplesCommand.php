<?php

namespace Webkul\DataTransfer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls as XLSWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XLSXWriter;

class GenerateImportSamplesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-transfer:generate-samples
                            {--type=all : citizens, services, or all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate XLS and XLSX sample files for citizens and services import';

    /**
     * Citizens sample data (columns + rows).
     */
    protected array $citizensData = [
        ['national_id', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'email', 'phone', 'status'],
        ['1234567890', 'أحمد', 'محمد', 'علي', 'Male', '1990-01-15', 'ahmed@example.com', '731234567', '1'],
        ['0987654321', 'فاطمة', 'عبدالله', 'حسن', 'Female', '1992-05-20', 'fatima@example.com', '772345678', '1'],
        ['1122334455', 'خالد', 'سعد', 'إبراهيم', 'Male', '1988-11-10', 'khalid@example.com', '783456789', '1'],
    ];

    /**
     * Services sample data (columns + rows).
     */
    protected array $servicesData = [
        ['service_number', 'locale', 'category_id', 'status', 'images', 'sort_order', 'name', 'description'],
        ['SVC-001', 'ar', '1', '1', 'service-image-1.jpg', '0', 'خدمة تجديد الهوية', 'خدمة تجديد بطاقة الهوية الوطنية'],
        ['SVC-001', 'en', '1', '1', 'service-image-1.jpg', '0', 'Identity Renewal Service', 'Service for renewing national identity card'],
        ['SVC-002', 'ar', '1', '1', 'service-image-2.jpg', '1', 'خدمة استخراج شهادة الميلاد', 'خدمة استخراج شهادة الميلاد'],
        ['SVC-002', 'en', '1', '1', 'service-image-2.jpg', '1', 'Birth Certificate Service', 'Service for obtaining birth certificate'],
        ['SVC-003', 'ar', '1', '0', 'service-image-3.jpg', '2', 'خدمة تحديث', 'خدمة تحديث البيانات'],
        ['SVC-003', 'en', '1', '0', 'service-image-3.jpg', '2', 'Update Service', 'Service for updating data'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');

        $disk = Storage::disk('public');
        $basePath = 'data-transfer/samples';

        foreach (['xls', 'xlsx'] as $format) {
            $dir = "{$basePath}/{$format}";
            if (! $disk->exists($dir)) {
                $disk->makeDirectory($dir);
            }
        }

        if (in_array($type, ['all', 'citizens'])) {
            $this->generateSample('citizens', $this->citizensData, $disk, $basePath);
        }

        if (in_array($type, ['all', 'services'])) {
            $this->generateSample('services', $this->servicesData, $disk, $basePath);
        }

        $this->info('Sample files generated successfully.');

        return Command::SUCCESS;
    }

    /**
     * Generate XLS and XLSX files for the given entity.
     */
    protected function generateSample(string $entity, array $data, $disk, string $basePath): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data, null, 'A1');

        $xlsPath = "{$basePath}/xls/{$entity}.xls";
        $xlsxPath = "{$basePath}/xlsx/{$entity}.xlsx";

        $fullPathXls = $disk->path($xlsPath);
        $fullPathXlsx = $disk->path($xlsxPath);

        (new XLSWriter($spreadsheet))->save($fullPathXls);
        (new XLSXWriter($spreadsheet))->save($fullPathXlsx);

        $this->line("  - {$xlsPath}");
        $this->line("  - {$xlsxPath}");
    }
}
