@include('nav')
<table border="1">
    <thead>
    <tr>
        @foreach ($WotLists as $WotList)
            @foreach ($WotList as $key => $value)
                <td>{{ $key }}</td>
            @endforeach
            @break
        @endforeach
    </tr>
    </thead>
    @foreach ($WotLists as $WotList)
        <tr>
            @foreach ($WotList as $key => $value)
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