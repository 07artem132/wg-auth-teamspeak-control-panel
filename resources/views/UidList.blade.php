@include('nav')
<table border="1">
    <thead>
    <tr>
        @foreach ($UidLists as $UidList)
            @foreach ($UidList as $key => $value)
                <td>{{ $key }}</td>
            @endforeach
            @break
        @endforeach
    </tr>
    </thead>
    @foreach ($UidLists as $UidList)
        <tr>
            @foreach ($UidList as $key => $value)
                @if (is_array($value))
                    <td>{{ $value['name'] }}</td>
                @else
                    <td>{{ $value }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
</table>
@yield('nav')