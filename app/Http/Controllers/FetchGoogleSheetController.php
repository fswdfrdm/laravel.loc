<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GoogleSheetSetting;
use App\Services\SyncGoogleSheetService;

class FetchGoogleSheetController extends Controller
{
    protected $syncGoogleSheetService;

    public function __construct(SyncGoogleSheetService $syncGoogleSheetService)
    {
        $this->syncGoogleSheetService = $syncGoogleSheetService;
    }

    public function fetch($count = null)
    {
        $googleSheetSettings = GoogleSheetSetting::first();

        if (!$googleSheetSettings) {
            return response()->json([
                'success' => false,
                'message' => 'Настройки Google Sheet не найдены.'
            ], 404);
        }

        $result = $this->syncGoogleSheetService->fetchData(
            $googleSheetSettings->url,
            $googleSheetSettings->sheet,
            $count ? (int)$count : null
        );

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        // Форматируем вывод для браузера
        $output = "<pre>";
        $output .= "Начало получения данных из Google Sheet\n";
        $output .= "URL таблицы: {$googleSheetSettings->url}\n";
        $output .= "Найдено {$result['total_rows']} строк в таблице.\n";
        
        if ($count) {
            $output .= "Выводится {$result['displayed_rows']} строк.\n";
        }

        $output .= "\n";
        $output .= str_pad('ID', 10) . " | " . str_pad('Контент', 46) . " | " . str_pad('Создано', 27) . " | " . str_pad('Обновлено', 29) . " | " .  str_pad('Комментарий', 50) . "\n";
        $output .= str_repeat('-', 162) . "\n";

        foreach ($result['data'] as $item) {
            $output .= str_pad($item['id'], 10) . " | " . str_pad(substr($item['content'], 0, 48), 50) . " | " . str_pad($item['created_at'], 20) . " | " . str_pad($item['updated_at'], 20) . " | " . str_pad(substr($item['comment'], 0, 48), 50) . "\n";
        }

        $output .= "\nДанные успешно получены.\n";
        $output .= "</pre>";

        return $output;
    }
}
