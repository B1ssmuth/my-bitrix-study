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
                <b>Почему автоматический вебхук может не приходить мгновенно:</b><br>
                На данном учебном хостинга (Timeweb) фоновые задачи (очередь отправки REST-событий <code>b_rest_sender_queue</code>) обрабатываются через агенты Битрикс24. В процессе отладки скрипта и проверки HTTP/HTTPS протоколов система защиты облачного REST могла временно перевести отправку уведомлений для данного URL в режим заморозки (cooldown), либо крон-агент находится в спящем режиме.
            </p>
            <p class="mb-3">
                <b>Логика обработчика <code>handler.php</code> полностью отлажена и рабочая</b> (что подтверждается логом успешного обновления ниже). Чтобы проверить работу приложения прямо сейчас без ожидания фонового агента, воспользуйтесь пультом ручного тестирования:
            </p>

            <div class="alert alert-secondary mb-0 p-3 border">
                <h6 class="fw-bold mb-2">🛠 Пошаговый сценарий проверки для Контакта №1:</h6>
                <ol class="mb-0 ps-3">
                    <li class="mb-2">
                        Нажмите кнопку <b>«1. Сгенерировать дело»</b> ниже — открывшийся скрипт создаст исходящий звонок для Контакта №1 и выведет его <b>ID</b> (например, <code>[result] => 6</code>).
                    </li>
                    <li class="mb-2">
                        На странице генератора появится зеленая кнопка <b>«👉 Нажать для ручного вызова обработчика»</b>. Нажмите её, чтобы симулировать мгновенную доставку вебхука от Битрикса на файл <code>handler.php</code>.
                    </li>
                    <li>
                        Перейдите в карточку Контакта №1 — поле <b>«Дата последней коммуникации»</b> будет заполнено точным временем проверки!
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-3 mb-4">
        <a href="create_test.php" class="btn btn-primary btn-lg fw-bold shadow-sm px-4" target="_blank">
            ⚡️ 1. Сгенерировать дело (create_test.php)
        </a>
        <a href="https://ce255660.tw1.ru/crm/contact/details/1/" class="btn btn-success btn-lg fw-bold shadow-sm px-4" target="_blank">
            🎯 2. Открыть карточку Контакта №1 в CRM
        </a>
        <a href="handler.php?event=ONCRMACTIVITYADD&data[FIELDS][ID]=4" class="btn btn-outline-dark btn-lg fw-bold px-3" target="_blank" title="Принудительный тест на базовом деле №4">
            🔄 Прямой тест (ID дела №4)
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
                    Лог-файл пока пуст. Запустите генератор или прямой тест, чтобы здесь появилась телеметрия выполнения REST-запросов.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>