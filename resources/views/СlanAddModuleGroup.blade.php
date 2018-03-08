<form method="post">
    <table border="1">
        <tr>
            <td>
                <input title="clan_id" size="33" type="text" name="clan_id" value="ID клана" required></td>
        </tr>
        <tr>
            <td>
                <select name="commander" title="commander" required>
                    <option selected disabled>Командующий (измените)</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="executive_officer" title="executive_officer" required>
                    <option selected disabled>Заместитель командующего (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="personnel_officer" title="personnel_officer" required>
                    <option selected disabled>Офицер штаба (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="combat_officer" title="combat_officer" required>
                    <option selected disabled>Командир подразделения (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="intelligence_officer" title="intelligence_officer" required>
                    <option selected disabled>Офицер разведки (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="quartermaster" title="quartermaster" required>
                    <option selected disabled>Офицер снабжения (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="recruitment_officer" title="recruitment_officer" required>
                    <option selected disabled>Офицер по кадрам (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="junior_officer" title="junior_officer" required>
                    <option selected disabled>Младший офицер (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="private" title="private" required>
                    <option selected disabled>Боец (измените)</option>

                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="recruit" title="recruit" required>
                    <option selected disabled>Новобранец (измените)</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="reservist" title="reservist" required>
                    <option selected disabled>Резервист (измените)</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="clan_tag" title="clan_tag" required>
                    <option selected disabled>тег клана (измените)</option>
                    @foreach($groupList as $id => $name)
                        <option value="{{$id}}">{{$name}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="submitButton" value="добавить клан"/>
            </td>
        </tr>
    </table>
</form>
