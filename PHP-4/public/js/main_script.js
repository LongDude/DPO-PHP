jQuery(function(){
    // При добавлении книги генерим форму из шаблона и вешаем обработчики
    $(document).on('click', '#add-book', function(){
        var clone = document.querySelector('template').content.cloneNode(true);
        clone.querySelector('h1').innerText = "Добавление книги";
        document.querySelector('body').appendChild(clone);

        // From bookForm_handler.js.twig
        // Выглядит кривовато, но пока работает
        loadBookHandler();
    })

    
})