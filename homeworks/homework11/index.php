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
    <div class="card shadow-sm mb-4 border-0 bg-light">
        <div class="card-body p-4">
            <h4 class="card-title text-dark fw-bold mb-3">📌 Архитектура и логика работы приложения</h4>
            <p class="card-text">
                Реализовано независимое серверное REST-приложение на чистом PHP (без использования сторонних SDK), предназначенное для автоматического обновления поля <b>«Дата последней коммуникации»</b> в карточке Контакта при создании любых новых дел (звонков, встреч, задач).
            </p>
            <hr>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded shadow-sm h-100">
                        <h6 class="fw-bold text-primary">1. Исходящий вебхук</h6>
                        <p class="small text-muted mb-0">Отслеживает системное событие <code>ONCRMACTIVITYADD</code> в CRM. При создании дела Битрикс24 формирует очередь на отправку POST-запроса с ID дела на наш сервер.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded shadow-sm h-100">
                        <h6 class="fw-bold text-success">2. Обработчик (handler.php)</h6>
                        <p class="small text-muted mb-0">Принимает ID дела, по HTTPS делает запрос <code>crm.activity.get</code>, проверяет привязку к Контакту (<code>OWNER_TYPE_ID = 3</code> во всех массивах) и формирует текущую дату в формате ISO 8601.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white rounded shadow-sm h-100">
                        <h6 class="fw-bold text-dark">3. Входящий вебхук</h6>
                        <p class="small text-muted mb-0">Отправляет встречную команду <code>crm.contact.update</code> через REST API, записывая актуальное время коммуникации в пользовательское поле Контакта.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-warning shadow-sm mb-4">
        <div class="card-header bg-warning text-dark fw-bold d-flex align-items-center">
            <span class="fs-5 me-2">⚠️</span> ВАЖНО ДЛЯ ПРОВЕРКИ: Особенности работы очереди агентов на учебном сервере
        </div>
        <div class="card-body bg-white p-4">
            <p class="mb-2">
                <b>Почему автоматический вебхук от портала может приходить с задержкой:</b><br>
                На данном учебном хостинге (Timeweb) фоновые задачи (очередь отправки REST-событий <code>b_rest_sender_queue</code>) зависят от работы крон-агентов. Если крон-агент находится в спящем режиме или очередь заморожена, портал не отправляет вебхук мгновенно.
            </p>
            <p class="mb-3">
                <b>Для удобной и быстрой проверки реализован механизм «Авто-пинга»:</b><br>
                При нажатии на кнопку генератора ниже скрипт не просто создаст новое тестовое дело для <b>Контакта №1</b>, но и <b>автоматически мгновенно отправит симулирующий POST-запрос в <code>handler.php</code></b> с ID этого нового дела, полностью эмулируя работу фонового вебхука.
            </p>

            <div class="alert alert-secondary mb-0 p-3 border">
                <h6 class="fw-bold mb-2">🛠 Как проверить работу приложения в 1 клик:</h6>
                <ol class="mb-0 ps-3">
                    <li class="mb-2">
                        Нажмите зеленую кнопку <b>«⚡️ 1. Сгенерировать дело и протестировать»</b>.
                    </li>
                    <li class="mb-2">
                        Откроется страница, подтверждающая создание дела (например, №12) и успешную работу обработчика.
                    </li>
                    <li>
                        Перейдите по второй ссылке в <b>карточку Контакта №1</b> — поле <b>«Дата последней коммуникации»</b> обновилось секунда в секунду!
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-3 mb-4">
        <a href="create_test.php" class="btn btn-success btn-lg fw-bold shadow-sm px-4" target="_blank">
            ⚡️ 1. Сгенерировать дело и протестировать
        </a>
        <a href="https://ce255660.tw1.ru/crm/contact/details/1/" class="btn btn-primary btn-lg fw-bold shadow-sm px-4" target="_blank">
            🎯 2. Открыть карточку Контакта №1 в CRM
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fs-6 fw-bold">🖥 Телеметрия работы обработчика (webhook_log.txt)</h5>
            <?php if ($logExists): ?>
                <span class="badge bg-success">Файл лога активен</span>
            <?php else: ?>
                <span class="badge bg-danger">Файл лога пуст</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0 bg-dark rounded-bottom">
            <?php if ($logExists && !empty($logContent)): ?>
                <pre class="mb-0 p-3 text-light" style="max-height: 400px; overflow-y: auto; font-size: 13px; font-family: 'Courier New', Courier, monospace; line-height: 1.4; background: #1e1e1e; border-radius: 0 0 6px 6px;"><?= htmlspecialchars(substr($logContent, -4500)) ?></pre>
            <?php else: ?>
                <div class="p-4 text-muted text-center bg-light">
                    Лог-файл пока пуст. Нажмите кнопку генерации дела, чтобы здесь появилась телеметрия выполнения REST-запросов.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>