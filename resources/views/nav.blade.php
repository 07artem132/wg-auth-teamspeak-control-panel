@section('nav')
    <br/>
    <br/>
    <table border="1">
        <thead>
        <tr>
            <td>Навигация</td>
        </tr>
        </thead>
        <tr>
            <td><a href="/teamspeak/uid/detachment">Отвязать UID</a></td>
        </tr>
        <tr>
            <td><a href="/teamspeak/uid/list">Список всех UID</a></td>
        </tr>
        <tr>
            <td><a href="/teamspeak/wot/list">Список всех танковых аккаунтов</a></td>
        </tr>
            <tr>
                <td><a href="/teamspeak/list">Список инстансов</a></td>
            </tr>
        @if(isset($ServerUID) && !empty($InstanseID))
            <tr>
                <td><a href="/teamspeak/{{$InstanseID}}/server/list">Список виртуальных серверов</a></td>
            </tr>
        @endif
        @if(isset($ServerUID) && isset($InstanseID) && !empty($modulesID))
            <tr>
                <td><a href="/teamspeak/{{$InstanseID}}/{{$ServerUID}}/module/list">Список модулей виртуального
                        сервера</a></td>
            </tr>
        @endif
    </table>
@endsection
