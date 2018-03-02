<form method="post">
    <table border="1">
        <tr>
            <td>
                <select name="sg_id" title="sg_id" required>
                    <option selected disabled>выберите группу</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="submitButton" value="добавить параметр"/>
            </td>
        </tr>
    </table>
</form>
