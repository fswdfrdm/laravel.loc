@extends('layouts.items')

@section('title', 'Редактирование строки')

@section('content')
<div class="container">
    <h1>Таблица | @yield('title')</h1>
    
    <form action="{{ route('items.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name">Контент</label>
            <input type="text" name="content" id="name" class="form-control" value="{{ $item->content }}" required>
        </div>
        
        <div class="form-group">
            <label for="status">Статус</label>
            <select name="status" id="status" class="form-control" required>
                @foreach($status as $value => $label)
                    <option value="{{ $value }}" {{ $item->status == $value ? 'selected' : '' }}>@if ($label === "Prohibited") Не публикуется @else Публикуется @endif</option>
                @endforeach
            </select>
        </div>

        <br>
        
        <button type="submit" class="btn btn-primary">Обновить</button>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">Назад</a>
    </form>
</div>
@endsection