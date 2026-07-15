<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("ДЗ №11: Локальное REST приложение (Дата последней коммуникации)");
Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$logFilePath = __DIR__ . '/webhook_log.txt';
$logExists = file_exists($logFilePath);
$logContent = $logExists ? file_get_contents($logFilePath) : '';
?>

<div class="container mt-5">
    <div class="alert alert-success shadow-sm">
        <h4 class="alert-heading">✅ ДЗ №11 выполнено! Связка вебхуков работает.</h4>
        <p>Реализовано серверное REST-приложение без использования внешних библиотек (на чистом cURL и REST API Битрикс24), отвечающее за автоматическое обновление даты последней коммуникации.</p>
        <hr>
        <h5>Архитектура интеграции:</h5>
        <ul>
            <li><strong>Исходящий вебхук (Битрикс24):</strong> Отслеживает системное событие <code>ONCRMACTIVITYADD</code> (создание любого дела, звонка, встречи или задачи в CRM) и отправляет POST-запрос на наш обработчик.</li>
            <li><strong>Обработчик (<code>handler.php</code>):</strong> Принимает ID дела, делает REST-запрос <code>crm.activity.get</code>, проверяет привязку к сущности Контакт (<code>OWNER_TYPE_ID = 3</code> во всех возможных массивах привязок) и отправляет встречную команду на обновление пользовательского поля в Контакте.</li>
            <li><strong>Входящий вебхук:</strong> Используется для выполнения REST-методов <code>crm.activity.get</code> и <code>crm.contact.update</code>.</li>
        </ul>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Лог работы обработчика (webhook_log.txt)</h5>
            <?php if ($logExists): ?>
                <span class="badge bg-success">Файл активен</span>
            <?php else: ?>
                <span class="badge bg-danger">Файл пуст или еще не создан</span>
            <?php endif; ?>
        </div>
        <div class="card-body bg-light">
            <?php if ($logExists && !empty($logContent)): ?>
                <pre class="mb-0" style="max-height: 350px; overflow-y: auto; font-size: 13px; background: #2b2b2b; color: #a9b7c6; padding: 15px; border-radius: 5px;"><?= htmlspecialchars(substr($logContent, -3000)) ?></pre>
                <div class="form-text mt-2">Показаны последние записи из лога событий.</div>
            <?php else: ?>
                <p class="text-muted mb-0">Лог пуст. Создайте любое дело (звонок/встречу) в карточке Контакта в CRM, чтобы здесь появилась запись о работе скрипта.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 mb-5 d-flex gap-3">
        <a href="/crm/contact/" class="btn btn-primary fw-bold" target="_blank">Перейти к Контактам в CRM</a>
        <a href="create_test.php" class="btn btn-outline-secondary" target="_blank">Запустить тестовый генератор дела (create_test.php)</a>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>