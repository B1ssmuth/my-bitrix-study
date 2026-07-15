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
                        <h6 class="text-primary fw-bold">🎯 Реализация бизнес-логики:</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li><b>Сущность:</b> ORM-таблица «Журнал посещений» (<code>VisitLogTable</code>) из ДЗ №4.</li>
                            <li><b>Контроллер:</b> Создан класс <code>\App\Rest\VisitRest</code>, реализующий все 5 операций CRUD.</li>
                            <li><b>Регистрация:</b> Через событие <code>OnRestServiceBuildDescription</code> добавлен scope <code>otus.visit</code>.</li>
                            <li><b>Логирование:</b> Подключен кастомный логгер. Все входящие параметры и результаты пишутся в <code>rest_crud.log</code>.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-white border border-success rounded h-100">
                        <h6 class="text-success fw-bold">✅ Соответствие критериям сдачи:</h6>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Зарегистрирован обработчик расширения REST-методов.</li>
                            <li>Написаны полноценные методы: Create, Read, Update, Delete, List.</li>
                            <li><b>Создано 5 отдельных входящих вебхуков</b>.</li>
                            <li>В каждом вебхуке заранее <b>настроен генератор запросов</b> для тестирования в один клик.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-primary shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold fs-5">
            🛠 Как протестировать методы (Генераторы запросов)
        </div>
        <div class="card-body bg-white p-4">
            <p class="fs-6 mb-3">
                В системе создано 5 отдельных вебхуков для тестирования каждой операции. В каждом из них параметры уже предзаполнены.
            </p>
            <ol class="fs-6 mb-0" style="line-height: 1.8;">
                <li>Перейдите в раздел портала <a href="/devops/list/" target="_blank" class="fw-bold text-decoration-none">Разработчикам ➔ Интеграции</a>.</li>
                <li>В списке вы увидите 5 вебхуков (вида "REST CRUD: Создание", "REST CRUD: Чтение" и т.д.).</li>
                <li>Откройте нужный вебхук и перейдите во вкладку <b>«Генератор запросов»</b>.</li>
                <li>Там уже выбран корректный метод (например, <code>otus.visit.add</code>) и подставлены тестовые параметры.</li>
                <li>Просто нажмите кнопку <b>«Выполнить»</b>. В правой части экрана отобразится JSON-ответ с результатом.</li>
                <li><i>Примечание: для методов update, get и delete не забудьте указать в генераторе актуальный ID записи, которую вернул метод add.</i></li>
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
                    Здесь логируются все параметры и ответы кастомных REST-запросов. Выполните любой метод через генератор вебхука, чтобы лог обновился.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>