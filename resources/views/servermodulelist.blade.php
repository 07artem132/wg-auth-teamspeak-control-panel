@include('nav')
<table border="1">
    <thead>
    <tr>
        <td style="text-align: center" colspan="7"><a href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/add">добавить
                модуль</a></td>
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
                        href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/disabled">отключить
                    модуль</a></td>
            <td style="text-align: center"><a
                        href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/enabled">включить
                    модуль</a></td>
            <td style="text-align: center"><a
                        href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/edit">изменить
                    модуль</a></td>
            @foreach ($Instanse as $key => $value)
                @if (is_array($value))
                    @if ($value['name'] == 'verify_game_nickname')
                        <td style="text-align: center"><a
                                    href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/verifygamenickname/list">изменить
                                группы</a></td>
                    @endif
                    @if ($value['name'] == 'wot_players')
                        <td style="text-align: center"><a
                                    href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/wotplayers/list">изменить
                                группы</a></td>
                    @endif
                    @if ($value['name'] == 'wn8')
                        <td style="text-align: center"><a
                                    href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/wn8/list">изменить
                                группы</a></td>
                    @endif
                    @if ($value['name'] == 'wg_auth_bot')
                        <td style="text-align: center"><a
                                    href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/clan/list">Просмотр
                                списка кланов
                            </a></td>
                        <td style="text-align: center"><a
                                    href="/teamspeak/{{ $InstanseID }}/{{$ServerUID}}/module/{{$Instanse['id']}}/group/notify/list">изменить
                                политику уведомлений</a></td>
                    @endif
                @endif
            @endforeach
        </tr>
    @endforeach
</table>
@yield('nav')