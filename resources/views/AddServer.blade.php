<form method="post">
    <select name="virtualServer">
        @foreach ($Instanses as $ServerUID => $ServerName)
            <option value="{{ $ServerUID }}">{{ $ServerName }}</option>
        @endforeach
    </select>
    <button>добавить</button>
</form>