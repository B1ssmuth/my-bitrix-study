<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use App\Models\Lists\DoctorsTable;
$APPLICATION->SetTitle("ДЗ №3: Управление врачами");

$doctors = DoctorsTable::getList([
    'select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']
])->fetchAll();
?>
<div style="padding: 20px; font-family: sans-serif;">
    <a href="edit_doctor.php" style="display:inline-block; padding: 10px 20px; background: #f0f0f0; border: 1px solid #ccc; text-decoration: none; border-radius: 4px; color: #000; margin-right: 10px;">Добавить врача</a>
    <a href="add_procedure.php" style="display:inline-block; padding: 10px 20px; background: #f0f0f0; border: 1px solid #ccc; text-decoration: none; border-radius: 4px; color: #000;">Добавить процедуру</a>

    <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 30px;">
        <?php foreach ($doctors as $doctor): ?>
            <a href="edit_doctor.php?ID=<?= $doctor['ID'] ?>" style="display: flex; align-items: center; justify-content: center; width: 220px; height: 120px; border: 1px solid #ddd; text-align: center; text-decoration: none; color: #0056b3; font-weight: bold; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); background: #fff;">
                <?= htmlspecialchars($doctor['NAME']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>