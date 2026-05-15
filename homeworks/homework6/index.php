<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("ДЗ #6: Собственный модуль otus.crmtab");

Asset::getInstance()->addCss('//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$moduleId = 'otus.crmtab';
$isInstalled = ModuleManager::isModuleInstalled($moduleId);
$isLoaded = Loader::includeModule($moduleId);
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Отчет по разработке модуля</h4>
                </div>
                <div class="card-body">
                    <h5>Статус модуля:</h5>
                    <div class="mb-4">
                        <?php if ($isInstalled): ?>
                            <span class="badge bg-success fs-6">Установлен</span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6">Не установлен</span>
                            <p class="text-muted mt-2">Зайдите в Настройки -> Настройки продукта -> Модули, чтобы установить.</p>
                        <?php endif; ?>
                        
                        <?php if ($isLoaded): ?>
                            <span class="badge bg-primary fs-6">Активен (Loaded)</span>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <h5>Что реализовано в рамках ДЗ:</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">✅ <b>Инсталлятор:</b> Регистрация в системе и создание таблиц БД.</li>
                        <li class="list-group-item">✅ <b>Интеграция с CRM:</b> Обработчик события для добавления вкладки в Сделки.</li>
                        <li class="list-group-item">✅ <b>Компонент:</b> Вывод данных в стандартном <code>Main.UI.Grid</code>.</li>
                        <li class="list-group-item">✅ <b>API контроллер:</b> Собственный роут для Ajax-запросов.</li>
                    </ul>

                    <div class="alert alert-info">
                        <strong>Как проверить работу:</strong><br>
                        1. Перейдите в раздел <a href="/crm/deal/list/" target="_blank" class="alert-link">CRM -> Сделки</a>.<br>
                        2. Откройте любую сделку.<br>
                        3. Сверху в меню вкладок найдите <b>"Журнал посещений"</b>.
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/bitrix/admin/settings.php?mid=<?=$moduleId?>&lang=ru" class="btn btn-outline-secondary">
                            Настройки модуля в админке
                        </a>
                        <a href="/bitrix/services/main/ajax.php?action=otus:crmtab.visit.getCount" target="_blank" class="btn btn-outline-primary">
                            Проверить API (getCount)
                        </a>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <a href="../index.php" class="text-decoration-none">← Назад к списку всех ДЗ</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>