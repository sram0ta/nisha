
document.addEventListener("DOMContentLoaded", () => {

    const formFeedback = document.querySelector('.application__form');

    if (formFeedback) {
        let input = formFeedback.querySelectorAll('input');

        formFeedback.querySelector('textarea').required = true;

        for (let i = 0; i < input.length; i++) {
            input[i].required = true;

            if (input[i].getAttribute('name') === 'tel') {
                input[i].setAttribute("pattern", "([\\+]*[7-8]{1}\\s?[\\(]*9[0-9]{2}[\\)]*\\s?\\d{3}[-]*\\d{2}[-]*\\d{2})");
            }
        }

        formFeedback.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const currentForm = this;
                    const btn = formFeedback.querySelector('.application__btn');
        
                    btn.disabled = true;
        
                    let alert = currentForm.closest('.application').querySelector('.alert');
        
                    if (alert) {
                        alert.remove();
                    }
        
                    fetch('mail.php', {
                        method: 'POST',
                        body: new FormData(currentForm)
                    })
                        .then(status)
                        // .then(json)
                        .then((response) => response.text())
                        .then((data) => {
                            console.log(data)
                            if (data === 'ok') {
                                text = 'Ваша заявка успешно отправлена!';
                                messageMail(currentForm, text, 'success');
                                currentForm.reset();
                            } else if (data == 'fields') {
                                text = 'Заполните все поля';
                                messageMail(currentForm, text, 'danger');
                            } else {
                                text = 'Произошла ошибка отправки, попробуйте ещё раз!';
                                messageMail(currentForm, text, 'danger');
                            }
                            btn.disabled = false;
                        })
                        .catch(function (error) {
                            alert('Произошла ошибка отправки, попробуйте ещё раз!');
                            btn.disabled = false;
                        });
                });
    }


    
    function status(response) {
        if (response.status >= 200 && response.status < 300) {
            return Promise.resolve(response)
        } else {
            return Promise.reject(new Error(response.statusText))
        }
    }

    function messageMail(form, text, className) {
        let div = document.createElement('div');
        div.className = "alert  alert-" + className;
        div.innerHTML = text;
        form.before(div);
    }
});