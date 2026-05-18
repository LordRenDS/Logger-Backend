<form action="{{ route('dashboard.per-page') }}" method="POST" class="flex items-center space-x-2">
    @csrf
    <label for="per_page" class="text-sm font-medium text-gray-700">Per page:</label>
    <select name="per_page" id="per_page" onchange="this.form.submit()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
        @foreach([10, 15, 25, 50, 100] as $val)
            <option value="{{ $val }}" {{ session('per_page', 15) == $val ? 'selected' : '' }}>{{ $val }}</option>
        @endforeach
    </select>
</form>