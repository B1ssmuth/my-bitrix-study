<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("ДЗ №10: Синхронизация Сделок и Заявок (События)");
Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
?>

<div class="container mt-5">
    <div class="alert alert-info shadow-sm">
        <h4 class="alert-heading">✅ ДЗ №10 выполнено! Двусторонняя синхронизация работает.</h4>
        <p>Бизнес-логика реализована на D7 с использованием статического флага-предохранителя для защиты от бесконечной рекурсии (зацикливания обработчиков).</p>
        <hr>
        <h5>Где посмотреть архитектуру кода:</h5>
        <ul>
            <li><code>local/php_interface/init.php</code> — очищен от "портянок" кода, оставлены только автозагрузчики.</li>
            <li><code>local/php_interface/events.php</code> — выделен в единый реестр (роутер) подписок на события.</li>
            <li><code>local/App/Events/SyncDealIblock.php</code> — инкапсулированная логика самой синхронизации (ООП подход).</li>
        </ul>
        <hr>
        <h5>Как проверить (Используется Инфоблок Списков "Заявки", ID = 23):</h5>
        <ol>
            <li><strong>Из Заявки в Сделку:</strong> <a href="/services/lists/23/view/0/" target="_blank">Откройте список Заявок</a>. Создайте новую или измените существующую. В самой форме Заявки выберите Сделку, вручную укажите новую Сумму, Ответственного и Клиента. Нажмите «Сохранить». После этого перейдите в эту Сделку в CRM — вы увидите, что её Бюджет, Ответственный и Контакт обновились <b>автоматически</b> благодаря нашему обработчику событий.</li>
            <li><strong>Из Сделки в Заявку:</strong> Откройте привязанную Сделку в CRM и вручную измените её Бюджет. Вернитесь в Заявку (в Списках) — значение поля "Сумма" подтянется из CRM само.</li>
        </ol>
        <div class="mt-4">
            <a href="/services/lists/23/view/0/" class="btn btn-primary fw-bold" target="_blank">Перейти в Списки (Заявки) для проверки</a>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>