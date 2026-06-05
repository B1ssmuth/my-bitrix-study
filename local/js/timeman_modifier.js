(function() {
    console.log('OTUS: Модификатор timeman успешно внедрен в DOM!');

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.tm-control-panel button, .tm-control-panel [type="button"], .tm-control-panel-actions-list button');
        if (!btn) return;

        if (btn.getAttribute('data-otus-intercepted') === 'Y') {
            return;
        }

        const btnText = btn.innerText ? btn.innerText.trim() : 'Изменить статус';

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        let modalTitle = "Управление рабочим днем";
        let modalMessage = `Вы собираетесь выполнить действие "${btnText}". Подтверждаете изменение статуса?`;

        if (typeof BX !== 'undefined' && BX.UI && BX.UI.Dialogs && BX.UI.Dialogs.MessageBox) {
            
            const messageBox = new BX.UI.Dialogs.MessageBox({
                title: modalTitle,
                message: modalMessage,
                buttons: [
                    new BX.UI.Button({
                        text: "Подтверждаю",
                        color: BX.UI.Button.Color.SUCCESS,
                        onclick: function() {
                            messageBox.close();

                            btn.setAttribute('data-otus-intercepted', 'Y');
                            
                            btn.click();

                            setTimeout(() => {
                                btn.removeAttribute('data-otus-intercepted');
                            }, 500);
                        }
                    }),
                    new BX.UI.Button({
                        text: "Отмена",
                        color: BX.UI.Button.Color.LINK,
                        onclick: function() {
                            messageBox.close();
                        }
                    })
                ]
            });

            messageBox.show();
            
        } else {
            if (confirm(`Вы действительно хотите выполнить действие "${btnText}"?`)) {
                btn.setAttribute('data-otus-intercepted', 'Y');
                btn.click();
                setTimeout(() => {
                    btn.removeAttribute('data-otus-intercepted');
                }, 500);
            }
        }
    }, true);
})();