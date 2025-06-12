const inpForm = document.getElementById('request-form')
inpForm.addEventListener('submit', (event) => event.preventDefault())

const inpAddress = document.getElementById('inp-address')


const btnSend = document.getElementById('btn-submit')
btnSend.addEventListener('click', send_form)

async function send_form(){
    const params = new URLSearchParams();
    params.append('partial_adress', inpAddress.value)

    await fetch(`geocoder.php?${params.toString()}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json;charset=utf-8'
        },
    })
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
            }
        }
    ))
    .catch((error) => {
        console.log(error)
        alert("Возникла непредвиденная ошибка")
    })
}

async function showResult(data){
    let oldResp = document.querySelector('main .record'); 
    if (oldResp) oldResp.remove();
    let responseForm = document.getElementById('resp-template').content.cloneNode(true);
    
    // Заполняем форму
    let datafields = responseForm.querySelectorAll('.record span')
    datafields[0].textContent = data['full_address']
    datafields[1].textContent = data['longitude']
    datafields[2].textContent = data['latitude']
    datafields[3].textContent = data['closestMetro']
    datafields[4].textContent = data['metroLongitude']
    datafields[5].textContent = data['metroLatitude']
    document.querySelector('main').appendChild(responseForm);
}

