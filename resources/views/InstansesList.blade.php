@include('nav')
<table border="1">
    <thead>
    <tr>
        <td style="text-align: center" colspan="7"><a href="/teamspeak/add">добавить инстанс</a></td>
    </tr>
    <tr>
        @foreach ($Instanses as $Instanse)
            @foreach ($Instanse as $key => $value)
                <td>{{ $key }}</td>
            @endforeach
            @break
        @endforeach
    </tr>
    </thead>
    @foreach ($Instanses as $Instanse)
        <tr>
            @foreach ($Instanse as $key => $value)
                <td>{{ $value }}</td>
            @endforeach
            <td><a href="/teamspeak/{{ $Instanse['id'] }}/delete">удалить</a></td>
            <td><a href="/teamspeak/{{ $Instanse['id'] }}/server/list">список виртуальных серверов</a></td>
        </tr>
    @endforeach
</table>
@yield('nav')