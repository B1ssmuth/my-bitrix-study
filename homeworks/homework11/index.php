<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("ДЗ №11: Локальное REST-приложение (Дата последней коммуникации)");
Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$logFilePath = __DIR__ . '/webhook_log.txt';
$logExists = file_exists($logFilePath);
$logContent = $logExists ? file_get_contents($logFilePath) : '';
?>

<div class="container mt-4 mb-5">
    <!-- ОПИСАНИЕ ЛОГИКИ РАБОТЫ -->
    <div class="card shadow-sm mb-4 border-0 bg-light">
        <div class="card-body p-4">
            <h4 class="card-title text-dark fw-bold mb-3">📌 Архитектура и логика работы приложения</h4>
            <p class="card-text">
                Реализовано независимое серверное REST-приложение на чистом PHP, предназначенное для автоматического обновления пользовательского поля <b>«Дата последней коммуникации»</b> в карточке Контакта при создании новых дел в CRM.
            </p>
            <hr>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded shadow-sm h-100">
                        <h6 class="fw-bold text-primary">1. Исходящий вебхук</h6>
                        <p class="small text-muted mb-0">Отслеживает системное событие <code>ONCRMACTIVITYADD</code>. При создании дела в CRM Битрикс24 отправляет POST-запрос на наш сервер.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded shadow-sm h-100">
                        <h6 class="fw-bold text-success">2. Обработчик (handler.php)</h6>
                        <p class="small text-muted mb-0">Принимает ID дела, запрашивает данные через REST <code>crm.activity.get</code>, определяет ID привязанного Контакта и формирует текущую метку времени.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded shadow-sm h-100">
                        <h6 class="fw-bold text-dark">3. Входящий вебхук</h6>
                        <p class="small text-muted mb-0">Выполняет метод <code>crm.contact.update</code>, записывая актуальное время последней коммуникации в карточку клиента.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ССЫЛКА НА CRM -->
    <div class="d-flex flex-wrap gap-3 mb-4">
        <a href="https://ce255660.tw1.ru/crm/contact/details/1/" class="btn btn-success btn-lg fw-bold shadow-sm px-4" target="_blank">
            🎯 Открыть карточку Контакта №1 в CRM для проверки
        </a>
    </div>

    <!-- ЛОГ СЕРВЕРА -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fs-6 fw-bold">🖥 Телеметрия работы обработчика (webhook_log.txt)</h5>
            <?php if ($logExists): ?>
                <span class="badge bg-success">Лог активен</span>
            <?php else: ?>
                <span class="badge bg-danger">Лог пуст</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0 bg-dark rounded-bottom">
            <?php if ($logExists && !empty($logContent)): ?>
                <pre class="mb-0 p-3 text-light" style="max-height: 400px; overflow-y: auto; font-size: 13px; font-family: 'Courier New', Courier, monospace; line-height: 1.4; background: #1e1e1e; border-radius: 0 0 6px 6px;"><?= htmlspecialchars(substr($logContent, -4500)) ?></pre>
            <?php else: ?>
                <div class="p-4 text-muted text-center bg-light">
                    Лог-файл пока пуст. Создайте дело в CRM, чтобы увидеть входящий запрос от Битрикс24.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>