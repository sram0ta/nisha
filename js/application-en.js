
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
            const currentFormEn = this;
            const btnEn = formFeedback.querySelector('.application__btn__en');

            btnEn.disabled = true;

            let alertEn = currentFormEn.closest('.application').querySelector('.alert');

            if (alertEn) {
                alertEn.remove();
            }

            fetch('mail.php', {
                method: 'POST',
                body: new FormData(currentFormEn)
            })
                .then(status)
                // .then(json)
                .then((response) => response.text())
                .then((data) => {
                    console.log(data)
                    if (data === 'ok') {
                        text = 'Your application has been successfully sent!';
                        messageMail(currentFormEn, text, 'success');
                        currentFormEn.reset();
                    } else if (data == 'fields') {
                        text = 'Fill in all the fields';
                        messageMail(currentFormEn, text, 'danger');
                    } else {
                        text = 'There was an error sending, please try again!';
                        messageMail(currentFormEn, text, 'danger');
                    }
                    btnEn.disabled = false;
                })
                .catch(function (error) {
                    alert('There was an error sending, please try again!');
                    btnEn.disabled = false;
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