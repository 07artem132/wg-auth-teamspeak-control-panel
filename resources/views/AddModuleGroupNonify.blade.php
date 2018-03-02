<form method="post">
    <table border="1">
        <tr>
            <td>
                <select name="commander" title="commander" required>
                    <option selected disabled>Командующий</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="executive_officer" title="executive_officer" required>
                    <option selected disabled>Заместитель командующего</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="personnel_officer" title="personnel_officer" required>
                    <option selected disabled>Офицер штаба</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="combat_officer" title="combat_officer" required>
                    <option selected disabled>Командир подразделения</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="intelligence_officer" title="intelligence_officer" required>
                    <option selected disabled>Офицер разведки</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="quartermaster" title="quartermaster" required>
                    <option selected disabled>Офицер снабжения</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="recruitment_officer" title="recruitment_officer" required>
                    <option selected disabled>Офицер по кадрам</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="junior_officer" title="junior_officer" required>
                    <option selected disabled>Младший офицер</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="private" title="private" required>
                    <option selected disabled>Боец</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="recruit" title="recruit" required>
                    <option selected disabled>Новобранец</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <select name="reservist" title="reservist" required>
                    <option selected disabled>Резервист</option>
                    <option value="0">Отключено</option>
                    <option value="1">Включено</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <input type="submit" name="submitButton" value="добавить настройки"/>
            </td>
        </tr>
    </table>
</form>
