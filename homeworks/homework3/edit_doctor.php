<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// 1. ПОДКЛЮЧАЕМ МОДУЛИ И КЛАССЫ
\Bitrix\Main\Loader::includeModule('iblock');
use App\Models\Lists\DoctorsTable;
use App\Models\Lists\ProceduresTable;

$el = new CIBlockElement;
$doctorId = (int)$_GET['ID'];
$doctorData = ['CODE' => '', 'F' => '', 'I' => '', 'O' => '', 'SERVICES' => []];

// 2. ЗАГРУЗКА ДАННЫХ ПРИ РЕДАКТИРОВАНИИ
if ($doctorId > 0) {
    $res = CIBlockElement::GetByID($doctorId)->Fetch();
    if ($res) {
        $doctorData['CODE'] = $res['CODE'];
        $nameParts = explode(' ', $res['NAME']);
        $doctorData['F'] = $nameParts[0]; 
        $doctorData['I'] = $nameParts[1]; 
        $doctorData['O'] = $nameParts[2];
        
        // Получаем привязанные услуги (SERVICES)
        $dbProps = CIBlockElement::GetProperty(DoctorsTable::IBLOCK_ID, $doctorId, [], ["CODE" => "SERVICES"]);
        while($p = $dbProps->Fetch()) {
            if($p['VALUE']) $doctorData['SERVICES'][] = $p['VALUE'];
        }
    }
}

// 3. ОБРАБОТКА СОХРАНЕНИЯ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['save'] == 'Y') {
    $fullName = trim($_POST['f']." ".$_POST['i']." ".$_POST['o']);
    
    $fields = [
        "IBLOCK_ID" => DoctorsTable::IBLOCK_ID, // Должно быть 19
        "NAME" => $fullName,
        "CODE" => $_POST['code'],
        "ACTIVE" => "Y",
        "PROPERTY_VALUES" => ["SERVICES" => $_POST['services']]
    ];

    if ($doctorId > 0) {
        $resAction = $el->Update($doctorId, $fields);
    } else {
        $resAction = $el->Add($fields);
    }

    if ($resAction) {
        LocalRedirect("index.php");
    } else {
        // ЕСЛИ НЕ СОХРАНИЛОСЬ - ВЫВОДИМ ОШИБКУ
        echo '<div style="background:red; color:white; padding:20px; margin:20px 0;">';
        echo '<b>ОШИБКА БИТРИКСА:</b> ' . $el->LAST_ERROR;
        echo '</div>';
    }
}

$allProcs = ProceduresTable::getList(['select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']])->fetchAll();
?>

<form method="POST" style="max-width: 500px; margin: 20px auto; font-family: sans-serif;">
    <h2 style="text-align: center;">Данные врача</h2>
    
    <label>Символьный код (латиницей):</label>
    <input type="text" name="code" value="<?=$doctorData['CODE']?>" style="width:100%; padding:10px; margin-bottom:10px;">
    
    <label>Фамилия:</label>
    <input type="text" name="f" value="<?=$doctorData['F']?>" required style="width:100%; padding:10px; margin-bottom:10px;">
    
    <label>Имя:</label>
    <input type="text" name="i" value="<?=$doctorData['I']?>" required style="width:100%; padding:10px; margin-bottom:10px;">
    
    <label>Отчество:</label>
    <input type="text" name="o" value="<?=$doctorData['O']?>" style="width:100%; padding:10px; margin-bottom:10px;">
    
    <label>Выберите услуги (Ctrl + клик):</label>
    <select name="services[]" multiple style="width:100%; height:150px; margin-bottom:20px; padding:10px;">
        <?php foreach ($allProcs as $p): ?>
            <option value="<?=$p['ID']?>" <?=in_array($p['ID'], $doctorData['SERVICES']) ? 'selected' : ''?>>
                <?=htmlspecialchars($p['NAME'])?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="hidden" name="save" value="Y">
    <button type="submit" style="width: 100%; padding: 15px; background: #0078d7; color: #fff; border: none; cursor: pointer; border-radius: 4px;">
        СОХРАНИТЬ
    </button>
</form>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>