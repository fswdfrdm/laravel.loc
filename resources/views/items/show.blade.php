@extends('layouts.items')

@section('title', 'Подробнее')

@section('content')
<div class="container">
    <h1>Таблица | @yield('title')</h1>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $item->content }}</h5>
            <p class="card-text">
                <strong>Статус:</strong> @if ($item->status === "Prohibited") <span style="color: red">Не публикуется</span> @else <span style="color: green">Публикуется</span> @endif<br>
                <strong>Создано:</strong> {{ $item->created_at }}<br>
                <strong>Обновлено:</strong> {{ $item->updated_at }}
            </p>
            
            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-warning">Редактировать</a>
            <form action="{{ route('items.destroy', $item->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Удалить</button>
            </form>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Назад</a>
        </div>
    </div>
</div>
@endsection