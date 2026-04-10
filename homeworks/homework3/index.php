<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use App\Models\Lists\DoctorsTable;

$APPLICATION->SetTitle("Управление врачами");

$doctors = DoctorsTable::getList([
    'select' => ['ID' => 'IBLOCK_ELEMENT_ID', 'NAME' => 'ELEMENT.NAME']
])->fetchAll();
?>

<div style="padding: 20px;">
    <a href="edit_doctor.php" style="padding: 10px; background: #eee; text-decoration: none; margin-right: 10px; border-radius: 4px;">Добавить врача</a>
    <a href="add_procedure.php" style="padding: 10px; background: #eee; text-decoration: none; border-radius: 4px;">Добавить процедуру</a>

    <div style="display: flex; flex-wrap: wrap; margin-top: 30px; gap: 20px;">
        <?php foreach ($doctors as $doctor): ?>
            <a href="edit_doctor.php?ID=<?= $doctor['ID'] ?>" style="display: block; width: 200px; padding: 30px; border: 1px solid #ddd; text-align: center; text-decoration: none; color: #0056b3; box-shadow: 2px 2px 5px rgba(0,0,0,0.1); border-radius: 8px;">
                <?= $doctor['NAME'] ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>