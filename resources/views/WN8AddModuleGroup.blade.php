<form method="post">
    <table border="1">
        <tr>
            <td>
                <select name="red_sg_id" title="red_sg_id" required>
                    <option selected disabled>red</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="yellow_sg_id" title="yellow_sg_id" required>
                    <option selected disabled>yellow</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="green_sg_id" title="green_sg_id" required>
                    <option selected disabled>green</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="turquoise_sg_id" title="turquoise_sg_id" required>
                    <option selected disabled>turquoise</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="purple_sg_id" title="purple_sg_id" required>
                    <option selected disabled>purple</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="purple_sg_id" title="terkin_sg_id" required>
                    <option selected disabled>terkin_sg_id</option>
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
