<form method="post">
    <table border="1">
        <tr>
            <td>
                <select name="bad_player_sg_id" title="bad_player_sg_id" required>
                    <option selected disabled>bad_player_sg_id</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="player_below_average_sg_id" title="player_below_average_sg_id" required>
                    <option selected disabled>player_below_average_sg_id</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="good_player_sg_id" title="good_player_sg_id" required>
                    <option selected disabled>good_player_sg_id</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="average_player_sg_id" title="average_player_sg_id" required>
                    <option selected disabled>average_player_sg_id</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="great_player_sg_id" title="great_player_sg_id" required>
                    <option selected disabled>great_player_sg_id</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="unicum_player_sg_id" title="unicum_player_sg_id" required>
                    <option selected disabled>unicum_player_sg_id</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="submitButton" value="добавить настройки модуля"/>
            </td>
        </tr>
    </table>
</form>
