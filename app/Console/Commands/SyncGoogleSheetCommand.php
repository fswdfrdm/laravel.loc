<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Models\GoogleSheetSetting;
use App\Services\SyncGoogleSheetService;

class SyncGoogleSheetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-google-sheet-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Синхронизация данных БД с Google Sheet.';

    protected $syncGoogleSheetService;

    public function __construct(SyncGoogleSheetService $syncGoogleSheetService)
    {
        parent::__construct();
        $this->syncGoogleSheetService = $syncGoogleSheetService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начало синхронизации в ' . now());

        $itemsWithExport = Item::get();
        $googleSheetSettings = GoogleSheetSetting::first();

        $this->info("Найдено {$itemsWithExport->count()} записей в БД.");

        try {
            $this->info("URL таблицы: {$googleSheetSettings->url}");
            
            $result = $this->syncGoogleSheetService->syncAll($googleSheetSettings->url, $googleSheetSettings->sheet);
            
            if ($result['success']) {
                $this->info("{$result['message']}");
            } else {
                $this->error("{$result['message']}");
            }

        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
        }

        $this->info("Синхронизация завершилась.");
    }
}
