const inpName = document.getElementById("inp-fullname"); 
const inpEmail = document.getElementById("inp-email"); 
const inpPhone = document.getElementById("inp-phone"); 
const inpComment = document.getElementById("inp-comment"); 

const inpForm = document.getElementById('request-form')

const btnSend = document.getElementById('btn-submit')
var validForms = 0b0000

btnSend.addEventListener('click', send_form)

async function send_form(){
    inpName.classList.remove('input-error')
    inpName.nextElementSibling.classList.remove('lbl-inp-err')

    inpEmail.classList.remove('input-error')
    inpEmail.nextElementSibling.classList.remove('lbl-inp-err')
    
    inpPhone.classList.remove('input-error')
    inpPhone.nextElementSibling.classList.remove('lbl-inp-err')
    
    inpComment.classList.remove('input-error')
    inpComment.nextElementSibling.classList.remove('lbl-inp-err')
    
    let isValid = true;

    // Проверка и распаковка имени
    let nameGroups = inpName.value.match(/^([a-zA-Zа-яА-Я]+) ?([a-zA-Zа-яА-Я]+)?(?<! ) ?([a-zA-Zа-яА-Я]+)?$/)
    if (!nameGroups){
        isValid = false
        inpName.classList.add('input-error')
        inpName.nextElementSibling.classList.add('lbl-inp-err')
    }

    // Проверка почты
    if (!inpEmail.value.match(/^[a-zA-Z]\w*(?:\.[a-zA-Z0-9]\w*)*@[a-zA-Z]+\.[a-zA-Z]+$/)){
        isValid = false
        inpEmail.classList.add('input-error')
        inpEmail.nextElementSibling.classList.add('lbl-inp-err')
    }


    // Проверка телефона
    if (inpPhone.value.replaceAll(/\D+/g, '').substring(0, 11).length < 11){
        isValid = false
        inpPhone.classList.add('input-error')
        inpPhone.nextElementSibling.classList.add('lbl-inp-err')
    }

    // Проверка комментария
    if (inpComment.value.length <= 0){
        isValid = false
        inpComment.classList.add('input-error')
        inpComment.nextElementSibling.classList.add('lbl-inp-err')
    }

    // Отправка посылки на бек
    if (!isValid) return
    await fetch('form.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json;charset=utf-8'
        },
        body: JSON.stringify({
            name: nameGroups[1],
            surname: nameGroups[2] ?? '',
            patronymic: nameGroups[3] ?? '',
            email: inpEmail.value,
            phone: inpPhone.value,
            comment: inpComment.value
        })})
        .then(r => r.json().then(d => {
            console.log(r)
            console.log(d)
            switch(r.status){
                case 500:
                    alert("Неожиданная ошибка\n");
                    break;
                case 200:
                    showResult(d);
                    break;
                case 400:
                    if ('multipleCollisions' in d){
                        inpEmail.classList.add('input-error')
                        inpPhone.classList.add('input-error')
                        alert('Неправильный почтовый адрес или номер телефона')
                    }
            }
        }))
        .catch((error) => {
            console.log(error)
            alert("Возникла непредвиденная ошибка")
        }
    )
}

async function showResult(data){
    document.getElementById('request-form').remove();
    let responseForm = document.getElementById('response-form').content.cloneNode(true);
    
    // Заполняем форму
    if ("resend_date" in data){
        // При повторной отправке формы
        responseForm.querySelector('h2').textContent = "Заявка уже была отправлена "
        responseForm.querySelector('h3').textContent = `Повторно отправить заявку можно после ${data['resend_date']}`
        let datafields = responseForm.querySelector('.record').remove();
    } else {
        responseForm.querySelector('h2').textContent = "Оставлено сообщение из формы обратной связи "
        responseForm.querySelector('h3').textContent = `С вами свяжутся после ${data['response_date']}`
        let datafields = responseForm.querySelectorAll('.record span')
        datafields[0].textContent = data['name']
        datafields[1].textContent = data['surname']
        datafields[2].textContent = data['patronymic']
        datafields[3].textContent = data['email']
        datafields[4].textContent = data['phone']
        datafields[5].textContent = data['comment']
    }
    document.querySelector('main').appendChild(responseForm);
}

function toggleSubmit(){
    if (!validForms){
        btnSend.setAttribute('disabled', null)
    }
    else {
        btnSend.removeAttribute('disabled')
    }
}

inpForm.addEventListener('submit', (event) => event.preventDefault())
inpName.addEventListener('input', (event) => {
    let val = inpName.value
    let m = val.match(/^(?:[a-zA-Zа-яА-Я]+\ ?){1,3}/)
    if (!m){
        event.target.value = ''
        validForms &= 0b0111
    } else {
        validForms |= 0b1000
        event.target.value = m[0].substring(0, 128)
    }
    toggleSubmit()
})

inpPhone.addEventListener('input', (event) => {
    let val = inpPhone.value.replaceAll(/\D+/g, '').substring(0, 11)
    let m = val.match(/(\d)(\d{1,3})?(\d{1,3})?(\d{1,2})?(\d{1,2})?/)
    if (!m){
        event.target.value = ''
        validForms &= 0b1011
    } else {
        validForms |= 0b0100
        event.target.value = `+${m[1]}`
        + (m[2] ? ` (${m[2]}` : '')
        + (m[3] ? `) ${m[3]}` : '')
        + (m[4] ? `-${m[4]}` : '')
        + (m[5] ? `-${m[5]}` : '')
    }
    toggleSubmit()
})

inpEmail.addEventListener('input', (event) => {
    let val = inpEmail.value
    let m = val.match(/^(?:[a-zA-Z]\w*)(?:\.[a-zA-Z0-9]\w*)*@?(?:(?<=@)[a-zA-Z]*)?(?:(?<=[a-zA-Z])\.?[a-zA-Z]*)?/)
    if (!m) {
        event.target.value = ''
        validForms &= 0b1101
    } else {
        validForms |= 0b0010
        event.target.value = m[0]
    }
    toggleSubmit()
})
