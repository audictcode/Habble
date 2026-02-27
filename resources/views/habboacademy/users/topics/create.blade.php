@extends('layouts.app')

@section('title', 'Crear tema')

@section('content')
<div class="container mt-4 mb-4">
    <div class="default-box full p-4">
        <h2 class="mb-3">Crear nuevo tema</h2>
        <form action="{{ route('web.topics.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="title">Título</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror">
                @error('title')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="category">Categoría</label>
                <select id="category" name="category" class="form-control @error('category') is-invalid @enderror">
                    <option value="">Selecciona una categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="content">Contenido</label>
                <textarea id="content" name="content" rows="6" class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                @error('content')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Publicar tema</button>
        </form>
    </div>
</div>
@endsection
