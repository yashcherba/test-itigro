<?php

Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
    '\Yashch\DisciplinesTable' => '/local/php_interface/Yashch/Disciplines.php',
    '\Yashch\ResultsTable' => '/local/php_interface/Yashch/Results.php',
));

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/vendor/autoload.php';