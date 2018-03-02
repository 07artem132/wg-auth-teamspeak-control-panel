<table border="1">
    <thead>
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
                        href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$modulesID}}/{{$Instanse['module_option_id']}}/edit">изменить
                    значение</a></td>
        </tr>
    @endforeach
</table>
