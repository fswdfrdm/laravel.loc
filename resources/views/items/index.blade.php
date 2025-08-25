@extends('layouts.items')

@section('title', 'Список')

@section('content')
<div class="container">
    <h1>Таблица | @yield('title')</h1>
    <a href="{{ route('items.create') }}" class="btn btn-primary mb-3">Добавить строку</a>
    
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
</div>
@endsection