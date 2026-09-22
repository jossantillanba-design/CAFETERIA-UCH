<x-layouts.app>
    <h1 class="text-3xl font-bold mb-6">Nuevo producto</h1>

    <form action="/admin/productos" method="POST" class="bg-white p-6 rounded shadow max-w-md">
        @csrf

        <label class="block mb-2 font-semibold">Nombre</label>
        <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full border rounded p-2 mb-4">

        <label class="block mb-2 font-semibold">Descripción</label>
        <textarea name="descripcion" class="w-full border rounded p-2 mb-4">{{ old('descripcion') }}</textarea>

        <label class="block mb-2 font-semibold">Precio</label>
        <input type="number" step="0.01" name="precio" value="{{ old('precio') }}" class="w-full border rounded p-2 mb-4">

        @error('nombre')
            <p class="text-red-600 mb-2">{{ $message }}</p>
        @enderror
        @error('precio')
            <p class="text-red-600 mb-2">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-blue-300 text-white px-4 py-2 rounded">Guardar</button>
        <a href="/admin/productos" class="ml-3 text-gray-600">Cancelar</a>
    </form>
</x-layouts.app>