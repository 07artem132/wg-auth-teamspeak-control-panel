<form method="post">
    <table border="1">
        <tr>
            <td>
                <input type="text" name="name" title="name" placeholder="name server" required>
            </td>
        </tr>
        <tr>
            <td>
                <select name="virtualServer">
                    @foreach ($Instanses as $ServerUID => $ServerName)
                        <option value="{{ $ServerUID }}">{{ $ServerName }}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="submitButton" value="добавить сервер"/>
            </td>
        </tr>
    </table>
</form>