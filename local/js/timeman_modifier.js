BX.ready(function() {
    BX.addCustomEvent("onTimeManInit", function(timeManObject) {
        
        if (timeManObject && timeManObject.Id === 'timeman_main') {
            
            const tmButton = document.getElementById('timeman-block');
            
            if (tmButton) {
                tmButton.addEventListener('click', function(e) {
                    
                    const currentState = timeManObject.currentState;
                    
                    if (currentState === 'CLOSED' || currentState === 'EXPIRED') {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        const messageBox = new BX.UI.Dialogs.MessageBox({
                            title: "Начало рабочего дня",
                            message: "Вы собираетесь подтвердить начало рабочего дня. Запустить счетчик таймера?",
                            buttons: [
                                new BX.UI.Button({
                                    text: "Подтверждаю",
                                    color: BX.UI.Button.Color.SUCCESS,
                                    onclick: function(button) {
                                        timeManObject.OpenForm();
                                        messageBox.close();
                                    }
                                }),
                                new BX.UI.Button({
                                    text: "Отмена",
                                    color: BX.UI.Button.Color.LINK,
                                    onclick: function(button) {
                                        messageBox.close();
                                    }
                                })
                            ]
                        });
                        
                        messageBox.show();
                    }
                }, true);
            }
        }
    });
});