<?php

namespace App\Services;

use App\Models\Item;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Log;

class SyncGoogleSheetService
{
    public function extractSheetId($url): ?string
    {
        preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? null;
    }

    public function syncAll(string $url, string $sheet)
    {
        if (!$url) {
            return ['success' => false, 'message' => 'Не указан URL.'];
        }

        $spreadsheetId = $this->extractSheetId($url);
        if (!$spreadsheetId) {
            return ['success' => false, 'message' => 'Неправильный формат URL.'];
        }

        try {
            // Берём ID таблицы
            Sheets::spreadsheet($spreadsheetId);

            // Получаем все публикуемые строки (scope status выборка)
            $allowedItems = Item::allowed()->get();

            // Получаем текущие данные из таблицы для подсчета
            $existingData = Sheets::sheet($sheet)->all();

            // Извлекаем комментарии из существующих данных
            $comments = $this->extractComments($existingData);

            // Извлекаем ID из существующих данных
            $existingIds = $this->extractExistingIds($existingData);
            
            // Находим ID элементов, которые нужно удалить
            $itemsToRemoveIds = array_diff($existingIds, $allowedItems->pluck('id')->toArray());
            $removedCount = count($itemsToRemoveIds);

            if ($allowedItems->isEmpty()) {
                // Полностью очищаем лист в гугл таблице, если в БД нет строк
                $this->clearSheet($sheet);
                return [
                    'success' => true, 
                    'message' => 'Таблица очищена. Нет строк для экспорта.',
                    'removed_count' => $removedCount
                ];
            }

            // Подготавливаем данные с заголовками
            $data = $this->prepareSheetData($allowedItems, true);

            // Сохраняем комментарии для соответствующих ID
            $dataWithComments = $this->preserveComments($data, $comments, $allowedItems->pluck('id')->toArray());

            // Полностью очищаем лист и записываем новые данные с комментами
            $this->clearSheet($sheet);
            Sheets::sheet($sheet)->update($dataWithComments);

            return [
                'success' => true,
                'exported_count' => $allowedItems->count(),
                'removed_count' => $removedCount,
                'message' => 'Успешно синхронизировано ' . $allowedItems->count() . 
                            ' строк, удалено ' . $removedCount . ' неактуальных строк, ' .
                            'сохранено ' . count($comments) . ' комментариев'
            ];

        } catch (\Exception $e) {
            Log::error('Ошибка синхронизации: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Синхронизация провалена: ' . $e->getMessage()];
        }
    }

    protected function prepareSheetData($items, $withHeaders = true)
    {
        $data = [];

        if ($withHeaders) {
            $data[] = ['ID', 'Контент', 'Создано', 'Обновлено', 'Комментарий'];
        }

        foreach ($items as $item) {
            $data[] = [
                $item->id,
                $item->content,
                $item->created_at->format('Y-m-d H:i:s'),
                $item->updated_at->format('Y-m-d H:i:s'),
                '' // Пустое место для комментариев
            ];
        }

        return $data;
    }

    protected function extractExistingIds($sheetData)
    {
        $ids = [];
        foreach ($sheetData as $index => $row) {
            if ($index === 0) continue;
            if (!empty($row[0]) && is_numeric($row[0])) {
                $ids[] = (int)$row[0];
            }
        }
        return $ids;
    }

    protected function extractComments($sheetData)
    {
        $comments = [];
        foreach ($sheetData as $index => $row) {
            if ($index === 0) continue;
            
            $id = !empty($row[0]) && is_numeric($row[0]) ? (int)$row[0] : null;
            $comment = $row[4] ?? ''; // Комментарии в 5-м столбце (индекс 4)
            
            if ($id && !empty(trim($comment))) {
                $comments[$id] = $comment;
            }
        }
        return $comments;
    }

    protected function preserveComments($newData, $existingComments, $newIds)
    {
        $result = [];
        
        foreach ($newData as $index => $row) {
            if ($index === 0) {
                $result[] = $row;
                continue;
            }
            
            $id = $row[0] ?? null;
            $comment = '';
            
            // Если есть комментарий для этого ID, сохраняем его
            if ($id && isset($existingComments[$id])) {
                $comment = $existingComments[$id];
            }
            
            // Заменяем последний столбец (комментарии) сохраненным значением
            $row[4] = $comment;
            $result[] = $row;
        }
        
        return $result;
    }

    protected function clearSheet($sheetName)
    {
        try {
            Sheets::sheet($sheetName)->clear();
        } catch (\Exception $e) {
            Log::warning('Не удалось очистить таблицу: ' . $e->getMessage());
        }
    }

    public function fetchData(string $url, string $sheet, ?int $count = null)
    {
        if (!$url) {
            return ['success' => false, 'message' => 'Не указан URL.'];
        }

        $spreadsheetId = $this->extractSheetId($url);
        if (!$spreadsheetId) {
            return ['success' => false, 'message' => 'Неправильный формат URL.'];
        }

        try {
            Sheets::spreadsheet($spreadsheetId);

            $data = Sheets::sheet($sheet)->all();

            if (empty($data)) {
                return ['success' => true, 'data' => [], 'message' => 'Таблица пуста.'];
            }

            array_shift($data);
            $totalRows = count($data);

            // Ограничиваем количество строк если указан параметр
            if ($count) {
                $data = array_slice($data, 0, $count);
            }

            $results = [];
            foreach ($data as $row) {
                $results[] = [
                    'id' => $row[0] ?? 'N/A',
                    'content' => $row[1] ?? '',
                    'created_at' => $row[2] ?? '',
                    'updated_at' => $row[3] ?? '',
                    'comment' => $row[4] ?? ''
                ];
            }

            return [
                'success' => true,
                'data' => $results,
                'total_rows' => $totalRows,
                'displayed_rows' => count($results),
                'message' => 'Данные успешно получены.'
            ];

        } catch (\Exception $e) {
            Log::error('Ошибка получения данных: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка получения данных: ' . $e->getMessage()];
        }
    }
}