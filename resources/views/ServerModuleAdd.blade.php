<form method="post">
    <table border="1">
        @foreach ($Instanses as $Instanse)
            <tr>
                <td>
                    <input style="width: 500px;" type="text" name="{{$Instanse['name']}}" title="{{$Instanse['name']}}"
                           placeholder="{{$Instanse['name']}}" required>
                </td>
            </tr>
        @endforeach
        <tr>
            <td>
                <input type="submit" name="submitButton" value="добавить модуль"/>
            </td>
        </tr>
    </table>
</form>

