<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Mulish:ital,wght@0,200..1000;1,200..1000&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">
    <script src="script.js" defer></script>
    <title>Запрос к геокодеру</title>
</head>
<body>
    <div class="bg-image"></div>
    <main>
        <form id="request-form">
            <div>
                <label for="address" class="lbl-input roboto-info-labels">Адрес: </label>
                <input type="text" placeholder="г. Липецк" name="adress" id="inp-address" class="roboto-info-input" maxlength="256">
            </div>
            <button type="submit" id="btn-submit" class="roboto-submit">Отправить</button>
        </form>
    </main>
    <template id="resp-template">
        <form class="record">
            <p>Полный адрес:</p><span></span>
            <p>Широта:</p><span></span>
            <p>Долгота:</p><span></span>
            <p>Ближайшее метро:</p><span></span>
            <p>Широта:</p><span></span>
            <p>Долгота:</p><span></span>
        </form>
    </template>
</body>
</html>