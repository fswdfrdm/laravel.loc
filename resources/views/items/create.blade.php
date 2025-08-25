@extends('layouts.items')

@section('title', 'Новая строка')

@section('content')
<div class="container">
    <h1>Таблица | @yield('title')</h1>
    
    <form action="{{ route('items.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="name">Контент</label>
            <input type="text" name="content" id="name" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="status">Статус</label>
            <select name="status" id="status" class="form-control" required>
                @foreach($status as $value => $label)
                    <option value="{{ $value }}">@if ($label === "Prohibited") Не публикуется @else Публикуется @endif</option>
                @endforeach
            </select>
        </div>

        <br>
        
        <button type="submit" class="btn btn-primary">Создать</button>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">Назад</a>
    </form>
</div>
@endsection