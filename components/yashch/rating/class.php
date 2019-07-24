<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Yashch;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UsersRating extends CBitrixComponent
{
    protected $errors = [];
    protected $disciplines = [];
    protected $usersResults = [];
    protected $usersList = [];

    public function onPrepareComponentParams($arParams)
    {
        Loc::loadMessages(__FILE__);
    }

    public function executeComponent()
    {
        try
        {
            $this->checkModules();
            $this->getResult();
            $this->includeComponentTemplate();
        }
        catch (SystemException $e)
        {
            ShowError($e->getMessage());
        }
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('iblock'))
            throw new SystemException(Loc::getMessage('CPS_MODULE_NOT_INSTALLED', array('#NAME#' => 'iblock')));
    }

    protected function getUsersList()
    {
        $rsUsersList = Yashch\ResultsTable::getList([
            'filter' => [],
            'order' => [],
            'select' => [
                'ID',
                'USER_ID'
            ],
            'limit' => 20
        ]);
        while($arUsersList = $rsUsersList->Fetch()) {
            $rsUser = CUser::GetByID($arUsersList['USER_ID']);
            $arUser = $rsUser->Fetch();
            $this->usersList[$arUsersList['USER_ID']] = $arUser['NAME'];
        }
    }

    protected function getDisciplinesList()
    {
        $rsDisciplines = Yashch\DisciplinesTable::getList([
            'filter' => [],
            'select' => [
                'ID',
                'NAME'
            ],
            'limit' => 3
        ]);

        while($arDiscipline = $rsDisciplines->Fetch()) {
            $this->disciplines[$arDiscipline['ID']] = $arDiscipline['NAME'];
        }
    }

    protected function getUsersResults()
    {
        $filter = [];

        $request = Application::getInstance()->getContext()->getRequest();

        $discipline = htmlspecialchars($request->getQuery("DISCIPLINE"));
        if(!empty($discipline))
            $filter[] = [
                'DISCIPLINE_ID' => $discipline
            ];

        $rsUserResults = Yashch\ResultsTable::getList([
            'filter' => $filter,
            'order' => [
                'DISCIPLINE_ID' => 'ASC',
                'SCORE' => 'DESC'
            ],
            'select' => [
                'ID',
                'USER_ID',
                'DISCIPLINE_ID',
                'SCORE'
            ],
            'limit' => 20
        ]);

        while($arUserResult = $rsUserResults->Fetch()) {
            $this->usersResults[$arUserResult['ID']] = $arUserResult;
        }
    }

    protected function getScoreRating($usersResults)
    {
        $arUsersScore = [];

        foreach ($this->disciplines as $discipline) {
            foreach ($usersResults as $userKey => $userResult) {
                if ($userResult['DISCIPLINE'] == $discipline) {
                    $arUsersScore[$userKey] = $userResult['SCORE'];
                }
            }

            $arCounts = array_count_values($arUsersScore);
            $count = count($arCounts);
            $place = 1;
            $counter = 0;

            foreach ($arUsersScore as $key => $userScore) {
                $usersResults[$key]['SCORE_RATING'] = $place . ' из ' . $count;
                $counter++;
                if($counter == $arCounts[$userScore]) {
                    $place++;
                    $counter = 0;
                }
            }

            $arUsersScore = [];
        }

        return $usersResults;
    }

    protected function getUserRating($usersResults)
    {
        $arUsersScore = [];

        foreach ($this->disciplines as $discipline) {
            foreach ($usersResults as $userKey => $userResult) {
                if ($userResult['DISCIPLINE'] == $discipline) {
                    $arUsersScore[$userKey] = $userResult['SCORE'];
                }
            }

            $count = count($arUsersScore);

            $counter = 0;
            $place = 1;
            $previous = null;
            $equalPlace = 1;

            foreach ($arUsersScore as $key => $userScore) {
                if($counter == 0) {
                    $previous = $userScore;
                    $usersResults[$key]['USER_RATING'] = $place . ' из ' . $count;
                    $counter++;
                } else {
                    if($userScore == $previous) {
                        $usersResults[$key]['USER_RATING'] = $place . ' из ' . $count;
                        $equalPlace++;
                    } else {
                        if($equalPlace > 1) {
                            $place += $equalPlace;
                            $equalPlace = 1;
                        } else $place++;
                        $usersResults[$key]['USER_RATING'] = $place . ' из ' . $count;
                        $previous = $userScore;
                    }
                    $counter++;
                }
            }

            $arUsersScore = [];
        }
        return $usersResults;
    }

    protected function createExcelFile($results)
    {
        $filePath = 'Rating.xlsx';
        unlink($filePath);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Имя');
        $sheet->setCellValue('B1', 'Предмет');
        $sheet->setCellValue('C1', 'Балл');
        $sheet->setCellValue('D1', 'Рейтинг результата');
        $sheet->setCellValue('E1', 'Рейтинг пользователя');

        $line = 2;
        foreach ($results as $result) {
            $sheet->setCellValue('A'.$line, $result['NAME']);
            $sheet->setCellValue('B'.$line, $result['DISCIPLINE']);
            $sheet->setCellValue('C'.$line, $result['SCORE']);
            $sheet->setCellValue('D'.$line, $result['SCORE_RATING']);
            $sheet->setCellValue('E'.$line, $result['USER_RATING']);
            $line++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        return $filePath;
    }

    protected function getResult()
    {
        if ($this->errors)
            throw new SystemException(current($this->errors));

        $this->getUsersList();
        $this->getDisciplinesList();
        $this->getUsersResults();

        $arResult['DISCIPLINES'] = $this->disciplines;
        $arResult['USERS_NAME'] = $this->usersList;

        $arResult['USER_RESULTS'] = [];

        foreach ($this->usersResults as $result) {
            $arUserResult = [];
            $arUserResult['USER_ID'] = $result['USER_ID'];
            $rsUser = CUser::GetByID($result['USER_ID']);
            $arUser = $rsUser->Fetch();
            $arUserResult['NAME'] = $arUser['NAME'];

            $arUserResult['DISCIPLINE'] = $this->disciplines[$result['DISCIPLINE_ID']];
            $arUserResult['SCORE'] = intval($result['SCORE']);

            $arResult['USER_RESULTS'][] = $arUserResult;
        }

        $arResult['USER_RESULTS'] = $this->getScoreRating($arResult['USER_RESULTS']);
        $arResult['USER_RESULTS'] = $this->getUserRating($arResult['USER_RESULTS']);

        $request = Application::getInstance()->getContext()->getRequest();

        $user = htmlspecialchars($request->getQuery("USER"));
        if(!empty($user)) {
            foreach ($arResult['USER_RESULTS'] as $key => $userResult) {
                if($userResult['USER_ID'] != $user)
                    unset($arResult['USER_RESULTS'][$key]);
            }
        }

        $diapozone = htmlspecialchars($request->getQuery("diapozone"));
        if(!empty($diapozone)) {
            $diapozone = explode('-',$diapozone);
            foreach ($arResult['USER_RESULTS'] as $key => $userResult) {
                if($userResult['SCORE'] < $diapozone[0] ||
                    $userResult['SCORE'] > $diapozone[1]) {
                    unset($arResult['USER_RESULTS'][$key]);
                }
            }
        }

        $arResult['FILE_PATH'] = $this->createExcelFile($arResult['USER_RESULTS']);

        $this->arResult = $arResult;
    }
}