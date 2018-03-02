<table border="1">
    <thead>
    <tr>
        <td style="text-align: center" colspan="17"><a
                    href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$modulesID}}/clan/add">
                добавить клан</a></td>
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
                @if (is_array($value))
                    <td>{{ $value['name'] }}</td>
                @else
                    <td>{{ $value }}</td>
                @endif
            @endforeach
            <td style="text-align: center"><a
                        href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$modulesID}}/clan/{{$Instanse['id']}}/edit">изменить
                    значение (после нажатия текущая запись удалится)</a></td>
                <td style="text-align: center"  ><a
                            href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$modulesID}}/clan/{{$Instanse['id']}}/delete">удалить
                        </a></td>

        </tr>
    @endforeach
</table>
