const form_template = document.querySelector('template');
const body = document.querySelector('body')

document.getElementById("add-book").onclick = () => {
    var clone = form_template.content.cloneNode(true);
    clone.querySelector('h1').innerText = "Добавление книги";
    body.appendChild(clone);
}
