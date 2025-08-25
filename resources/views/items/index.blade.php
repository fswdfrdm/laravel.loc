@extends('layouts.items')

@section('title', 'Список')

@section('content')
<div class="container">
    <h1>Таблица | @yield('title')</h1>
    <a href="{{ route('items.create') }}" class="btn btn-primary mb-3">Добавить строку</a>

    <form action="{{ route('items.generate') }}" method="POST" class="mb-3" onsubmit="return confirm('Вы уверены, что хотите сгенерировать 1000 строк?')">
        @csrf
        <button type="submit" class="btn btn-success">Сгенерировать 1000 строк</button>
    </form>

    <form action="{{ route('items.clear') }}" method="POST" class="mb-3" onsubmit="return confirm('Вы уверены, что хотите очистить таблицу?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Очистить таблицу</button>
    </form>

    <a href="#" class="btn btn-warning mb-3" data-bs-toggle="modal" data-bs-target="#googleSheetSettingsModal">Привязать к Google Sheet</a>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif

    <br>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Контент</th>
                <th>Статус</th>
                <th>Управление</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->content }}</td>
                <td>@if ($item->status === "Prohibited") <span style="color: red">Не публикуется</span> @else <span style="color: green">Публикуется</span> @endif</td>
                <td>
                    <a href="{{ route('items.show', $item->id) }}" class="btn btn-info">Подробнее</a>
                    <a href="{{ route('items.edit', $item->id) }}" class="btn btn-warning">Редактировать</a>
                    <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="modal fade" id="googleSheetSettingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Привязать к Google Sheet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('items.google-sheet-settings') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Ссылка на гугл таблицу</label>
                            <input type="url" name="url" class="form-control" required placeholder="https://docs.google.com/spreadsheets/d/..." value="{{ $settings->url ?? '' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Название листа</label>
                            <input type="text" name="sheet" class="form-control" required placeholder="Лист1" value="{{ $settings->sheet ?? '' }}">
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Доступно для выгрузки:</strong> 
                            {{ App\Models\Item::allowed()->count() }} из {{ App\Models\Item::count() }} строк <br>
                            (В выгрузку берутся строки со статусом "Публикуется")
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection