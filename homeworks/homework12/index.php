<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Data\Cache;

$APPLICATION->SetTitle("ДЗ №12: Собственные обработчики REST (CRUD)");
Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$cacheCleared = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_rest_cache'])) {
    Cache::clearCache(true, "/rest/scope/");
    $cacheCleared = true;
}

$logFilePath = $_SERVER["DOCUMENT_ROOT"] . '/local/logs/rest_crud.log';
$logExists = file_exists($logFilePath);
$logContent = $logExists ? file_get_contents($logFilePath) : '';
?>

<div class="container mt-4 mb-5">
    
    <?php if ($cacheCleared): ?>
        <div class="alert alert-success shadow-sm">
            <strong>✅ Кэш REST успешно сброшен!</strong> Новые методы <code>otus.visit.*</code> теперь доступны в системе.
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4 border-0 bg-light">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title text-dark fw-bold mb-0">📌 Суть задания и архитектура решения</h4>
                <form method="POST" class="m-0">
                    <button type="submit" name="clear_rest_cache" class="btn btn-warning fw-bold shadow-sm">
                        🔄 Сбросить кэш REST
                    </button>
                </form>
            </div>
            
            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="p-3 bg-white border border-primary rounded h-100">
                        <h6 class="text-primary fw-bold">🎯 Что требовалось сделать:</h6>
                        <p class="small text-muted mb-0">
                            Научиться расширять стандартное REST API Битрикс24. Если внешней системе (1С, мобильному приложению, сайту) нужно обмениваться данными с порталом, она не должна ходить напрямую в БД. Нужно было создать свои методы (CRUD) для пользовательской сущности и зарегистрировать их в ядре, соблюдая стандарты безопасности и логирования.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-white border border-success rounded h-100">
                        <h6 class="text-success fw-bold">💡 Как мы это реализовали:</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li><b>Сущность:</b> Взята наша ORM-таблица «Журнал посещений» (<code>VisitLogTable</code>) из ДЗ №4.</li>
                            <li><b>Контроллер:</b> Создан класс <code>\App\Rest\VisitRest</code>, обрабатывающий входящие данные.</li>
                            <li><b>Регистрация:</b> Через событие <code>OnRestServiceBuildDescription</code> в ядро добавлен новый scope <code>otus.visit</code> и 5 методов.</li>
                            <li><b>Логирование:</b> Подключен кастомный логгер из ДЗ №2. Все запросы пишутся в <code>rest_crud.log</code>.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h6 class="fw-bold text-dark">Доступные методы (Scope: <code>otus.visit</code>):</h6>
                <ul class="list-group list-group-flush border rounded shadow-sm mt-2">
                    <li class="list-group-item">🟢 <b>otus.visit.add</b> — Создание записи <i>(Обязателен FIELDS[PATIENT_NAME])</i></li>
                    <li class="list-group-item">🔵 <b>otus.visit.get</b> — Получение записи <i>(Обязателен ID)</i></li>
                    <li class="list-group-item">🟠 <b>otus.visit.update</b> — Обновление записи <i>(Обязательны ID и FIELDS)</i></li>
                    <li class="list-group-item">🔴 <b>otus.visit.delete</b> — Удаление записи <i>(Обязателен ID)</i></li>
                    <li class="list-group-item">🟣 <b>otus.visit.list</b> — Вывод списка <i>(Поддерживает filter, select, limit)</i></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold fs-5">
            🛠 Как протестировать методы через готовый Входящий вебхук
        </div>
        <div class="card-body bg-white p-4">
            <ol class="fs-6 mb-0" style="line-height: 1.8;">
                <li>Перейдите в раздел портала <a href="/devops/section/standard/" target="_blank" class="fw-bold text-decoration-none">Разработчикам ➔ Другое ➔ Входящий вебхук</a>.</li>
                <li>Откройте уже созданный тестовый вебхук (у него в правах выдан доступ к <code>otus.visit</code>).</li>
                <li>Перейдите в блок <b>«Генератор запросов»</b>.</li>
                <li>В поле «Метод» впишите вручную: <code>otus.visit.add</code></li>
                <li>В блоке «Параметры» нажмите «Добавить параметр»: ключ <code>FIELDS[PATIENT_NAME]</code>, значение <code>REST Тест</code>. Нажмите <b>«Выполнить»</b>.</li>
                <li>Справа появится успешный ответ <code>"result": ID_записи</code>. Вы можете аналогично проверить чтение, вписав метод <code>otus.visit.get</code> и передав параметр <code>ID</code>.</li>
            </ol>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fs-6 fw-bold">🖥 Телеметрия API (/local/logs/rest_crud.log)</h5>
            <?php if ($logExists): ?>
                <span class="badge bg-success">Файл лога активен</span>
            <?php else: ?>
                <span class="badge bg-danger">Файл лога пуст</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0 bg-dark rounded-bottom">
            <?php if ($logExists && !empty($logContent)): ?>
                <pre class="mb-0 p-3 text-light" style="max-height: 500px; overflow-y: auto; font-size: 13px; font-family: 'Courier New', Courier, monospace; line-height: 1.4; background: #1e1e1e; border-radius: 0 0 6px 6px;"><?= htmlspecialchars(substr($logContent, -5000)) ?></pre>
            <?php else: ?>
                <div class="p-4 text-muted text-center bg-light">
                    Здесь будут логироваться все параметры и ответы ваших кастомных REST-запросов. Вызовите метод через Входящий вебхук, чтобы лог наполнился.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>