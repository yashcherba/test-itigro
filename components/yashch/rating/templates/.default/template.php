<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div style="margin-bottom:20px;">
    <form action="">
        <div class="form-group">
            <label for="name">Имя</label>
            <select class="form-control" name="USER" id="name">
                <option value="">-</option>
                <?foreach ($arResult['USERS_NAME'] as $key => $name):?>
                    <option value="<?=$key?>"><?=$name?></option>
                <?endforeach;?>
            </select>
        </div>
        <div class="form-group">
            <label for="discipline">Дисциплина</label>
            <select class="form-control" name="DISCIPLINE" id="discipline">
                <option value="">-</option>
                <?foreach ($arResult['DISCIPLINES'] as $key => $discipline):?>
                    <option value="<?=$key?>"><?=$discipline?></option>
                <?endforeach;?>
            </select>
        </div>
        <div class="form-group">
            <label for="diapozone">Промежуток баллов</label>
            <select class="form-control" name="diapozone" id="diapozone">
                <option value="">-</option>
                <option value="0-20">0-20</option>
                <option value="21-40">21-40</option>
                <option value="41-60">41-60</option>
                <option value="61-80">61-80</option>
                <option value="81-100">81-100</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Посмотреть</button>
        <a class="btn btn-secondary" href="<?=$arResult['FILE_PATH']?>" download>Скачать в Excel</a>
    </form>
</div>

<div>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Имя</th>
            <th>Предмет</th>
            <th>Балл</th>
            <th>Рейтинг результата</th>
            <th>Рейтинг пользователя</th>
        </tr>
        </thead>
        <tbody>
        <?
        foreach ($arResult['USER_RESULTS'] as $result) {
            echo '<tr>';
            echo '<th>' . $result['NAME'] . '</th>';
            echo '<th>' . $result['DISCIPLINE'] . '</th>';
            echo '<th>' . $result['SCORE'] . '</th>';
            echo '<th>' . $result['SCORE_RATING'] . '</th>';
            echo '<th>' . $result['USER_RATING'] . '</th>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</div>
