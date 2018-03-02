<table border="1">
    <thead>
    <tr>
        <td style="text-align: center" colspan="6"><a href="/teamspeak/{{ $Instanses[0]['instanse_id'] }}/server/add">добавить
                сервер</a></td>
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
            <td>
                <a href="/teamspeak/{{ $Instanse['instanse_id'] }}/{{base64_encode($Instanse['uid'])}}/delete">удалить</a>
            <td>
                <a href="/teamspeak/{{ $Instanse['instanse_id'] }}/{{base64_encode($Instanse['uid'])}}/module/list">управление
                    модулями</a>
            </td>
        </tr>
    @endforeach
</table>

