<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GoogleSheetSetting;
use App\Services\SyncGoogleSheetService;
use Revolution\Google\Sheets\Facades\Sheets;

class FetchGoogleSheetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-google-sheet-command {count? : Количество выводимых строк}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение данных из Google Sheet и вывод в консоль';

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
        $count = $this->argument('count');
        $googleSheetSettings = GoogleSheetSetting::first();

        if (!$googleSheetSettings) {
            $this->error('Настройки Google Sheet не найдены.');
            return 1;
        }

        $this->info('Начало получения данных из Google Sheet в ' . now());
        $this->info("URL таблицы: {$googleSheetSettings->url}");

        try {
            $spreadsheetId = $this->syncGoogleSheetService->extractSheetId($googleSheetSettings->url);
            
            if (!$spreadsheetId) {
                $this->error('Неправильный формат URL.');
                return 1;
            }

            // Устанавливаем spreadsheet
            Sheets::spreadsheet($spreadsheetId);

            // Получаем все данные из листа
            $data = Sheets::sheet($googleSheetSettings->sheet)->all();

            if (empty($data)) {
                $this->info('Таблица пуста.');
                return 0;
            }

            // Убираем заголовки
            array_shift($data);
            $totalRows = count($data);

            // Ограничиваем количество строк если указан параметр
            if ($count && is_numeric($count)) {
                $data = array_slice($data, 0, (int)$count);
            }

            $this->info("Найдено {$totalRows} строк в таблице.");
            if ($count) {
                $this->info("Выводится {$count} строк.");
            }

            // Создаем progressbar
            $progressBar = $this->output->createProgressBar(count($data));
            $progressBar->start();

            $this->info("\n");
            $this->info(str_pad('ID', 10) . ' | ' . str_pad('Контент', 46) . ' | ' . str_pad('Создано', 27) . ' | ' . str_pad('Обновлено', 29) . ' | ' . str_pad('Комментарий', 50));
            $this->info(str_repeat('-', 162));

            $results = [];

            foreach ($data as $index => $row) {
                $id = $row[0] ?? 'N/A';
                $content = $row[1] ?? '';
                $created_at = $row[2] ?? '';
                $updated_at = $row[3] ?? '';
                $comment = $row[4] ?? '';

                // Сохраняем для вывода в браузере
                $results[] = [
                    'id' => $id,
                    'content' => $content,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at,
                    'comment' => $comment
                ];

                // Выводим в консоль
                $this->info(str_pad($id, 10) . " | " . str_pad(substr($content, 0, 48), 50) . " | " . str_pad($created_at, 20) . " | " . str_pad($updated_at, 20) . " | " . str_pad(substr($comment, 0, 48), 50));

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->info("\n\nДанные успешно получены.");

            // Возвращаем результаты для использования в роуте
            return $results;

        } catch (\Exception $e) {
            $this->error("Ошибка: " . $e->getMessage());
            return 1;
        }
    }
}
