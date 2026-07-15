<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use App\Models\VisitLogTable;
use Bitrix\Iblock\Iblock;

$APPLICATION->SetTitle("Записать пациента на прием");

// 1. Динамически инициализируем сущности для инфоблоков Врачи (16) и Процедуры (17)
// Это решает проблему с ошибкой Table b_iblock_element_prop_s19 doesn't exist
$iblockDoctors = Iblock::wakeUp(16); 
$entityDoctors = $iblockDoctors->getEntityDataClass();

$iblockProcedures = Iblock::wakeUp(17); 
$entityProcedures = $iblockProcedures->getEntityDataClass();

// Выбираем списки активных врачей и процедур
$docs = $entityDoctors::getList(['select' => ['ID', 'NAME'], 'filter' => ['=ACTIVE' => 'Y']])->fetchAll();
$procs = $entityProcedures::getList(['select' => ['ID', 'NAME'], 'filter' => ['=ACTIVE' => 'Y']])->fetchAll();

// Ловим ID врача из GET-параметра (если перешли по кнопке из ДЗ 7)
$currentDoctorId = intval($_GET['doctor_id']);

// 2. Логика сохранения записи
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['patient'])) {
    $result = VisitLogTable::add([
        'PATIENT_NAME' => $_POST['patient'],
        'DOCTOR_ID'    => $_POST['doctor_id'],
        'PROCEDURE_ID' => $_POST['procedure_id'],
        'VISIT_PRICE'  => $_POST['price'],
    ]);

    if ($result->isSuccess()) {
        LocalRedirect("index.php");
    } else {
        echo "<div style='color:red; max-width:400px; margin:20px auto; font-weight:bold;'>Ошибка: " . implode(', ', $result->getErrorMessages()) . "</div>";
    }
}
?>

<form method="POST" style="max-width: 400px; margin: 20px auto; font-family: sans-serif; padding: 20px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">
    <h2 style="margin-top: 0; margin-bottom: 20px; font-size: 22px;">Записать пациента</h2>
    
    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">Имя пациента:</label>
        <input type="text" name="patient" placeholder="Имя пациента" required style="width:100%; padding:10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
    </div>
    
    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">Выберите врача:</label>
        <select name="doctor_id" required style="width:100%; padding:10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <option value="">Выберите врача</option>
            <?php foreach($docs as $d): ?>
                <option value="<?=$d['ID']?>" <?=($d['ID'] == $currentDoctorId) ? 'selected' : ''?>>
                    <?=htmlspecialchars($d['NAME'])?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">Выберите процедуру:</label>
        <select name="procedure_id" required style="width:100%; padding:10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            <option value="">Выберите процедуру</option>
            <?php foreach($procs as $p): ?>
                <option value="<?=$p['ID']?>"><?=htmlspecialchars($p['NAME'])?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 20px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">Цена:</label>
        <input type="number" name="price" placeholder="Цена" required style="width:100%; padding:10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
    </div>
    
    <button type="submit" style="width:100%; padding:15px; background:#2fc6f6; border:none; color:#fff; font-weight:bold; font-size:16px; border-radius:4px; cursor:pointer;">Записать на прием</button>
</form>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>