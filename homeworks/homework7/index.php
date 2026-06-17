<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("ДЗ №7: Бронирование процедур");
Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
?>

<div class="container mt-5">
    <div class="alert alert-success shadow-sm">
        <h4 class="alert-heading">✅ ДЗ переведено в нативный формат Битрикс!</h4>
        <p>Согласно замечаниям преподавателя, функционал кастомного свойства был полностью интегрирован в системную архитектуру.</p>
        <hr>
        <ul class="mb-0">
            <li>Добавлен метод <code>GetAdminListViewHTML</code> для поддержки стандартных гридов.</li>
            <li>JS-логика вынесена в само свойство через <code>Asset::getInstance()</code>.</li>
            <li>Сохранение брони переведено на ajax-вызов API контроллера из модуля <code>otus.crmtab</code>.</li>
        </ul>
        <div class="mt-4">
            <a href="/services/lists/19/view/0/" class="btn btn-primary fw-bold">Перейти к списку Врачей в CRM для проверки</a>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>