<?php

namespace Yashch;

use Bitrix\Main\Entity;

class ResultsTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'i_results';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            new Entity\IntegerField('USER_ID', array(
                'required' => true
            )),
            new Entity\IntegerField('DISCIPLINE_ID', array(
                'required' => true
            )),
            new Entity\IntegerField('SCORE', array(
                'required' => true
            )),
        );
    }
}