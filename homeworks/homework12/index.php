<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Data\Cache;

$APPLICATION->SetTitle("ДЗ №12: Собственные обработчики REST (CRUD)");
Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

// Очистка кэша REST (критически важно для регистрации новых методов)
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
                <h4 class="card-title text-dark fw-bold mb-0">📌 Архитектура REST API</h4>
                <form method="POST" class="m-0">
                    <button type="submit" name="clear_rest_cache" class="btn btn-warning fw-bold shadow-sm">
                        🔄 Сбросить кэш REST (Обязательно 1 раз)
                    </button>
                </form>
            </div>
            
            <p class="card-text">
                Написан класс <code>\App\Rest\VisitRest</code>, который регистрирует 5 CRUD-методов для управления нашей ORM-сущностью «Журнал посещений» (VisitLogTable).
            </p>
            <ul class="list-group list-group-flush border rounded shadow-sm">
                <li class="list-group-item">🟢 <b>otus.visit.add</b> — Создание записи</li>
                <li class="list-group-item">🔵 <b>otus.visit.get</b> — Получение записи по ID</li>
                <li class="list-group-item">🟠 <b>otus.visit.update</b> — Редактирование записи</li>
                <li class="list-group-item">🔴 <b>otus.visit.delete</b> — Удаление записи</li>
                <li class="list-group-item">🟣 <b>otus.visit.list</b> — Вывод списка (с поддержкой filter, select, limit)</li>
            </ul>
        </div>
    </div>

    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold fs-5">
            🛠 Как протестировать (Инструкция для вебхука)
        </div>
        <div class="card-body bg-white p-4">
            <ol class="fs-6 mb-0" style="line-height: 1.8;">
                <li>Зайдите в портале в <b>Разработчикам -> Другое -> Входящий вебхук</b>.</li>
                <li>В блоке "Настройка прав" права выбирать <b>не нужно</b> (наши методы публичные в рамках портала). Сохраните вебхук.</li>
                <li>Перейдите в <b>Генератор запросов</b> (в настройках созданного вебхука).</li>
                <li>В поле "Метод" впишите <b>руками</b> название метода (например, <code>otus.visit.add</code>).</li>
                <li>В "Параметры" добавьте: ключ <code>FIELDS[PATIENT_NAME]</code>, значение <code>REST Тест</code>. Нажмите "Выполнить".</li>
                <li>Справа появится JSON ответ с <code>"result": ID_записи</code>. Повторите тест для <code>otus.visit.get</code> передав <code>ID = (ваш ID)</code>.</li>
            </ol>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fs-6 fw-bold">🖥 Лог входящих REST запросов (/local/logs/rest_crud.log)</h5>
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