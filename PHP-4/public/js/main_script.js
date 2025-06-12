jQuery(function(){
    updateTable();
    $('#select-pagination-limit, #sorting-order-by, select[name="orderBy"], select[name="orderRule"], select[name="books_uploaders"]').change(function() {
        updateTable();
    });

    $(document).on('click', '.addBookButton', function() {
        const modal = new bootstrap.Modal($('#addBookModal').first());
        $('#bookId').val('');
        $('#name').val('');
        $('#author').val('');
        $('#read_date').val('');
        $('#allow_download').prop('checked', false);
        $('#addBookModal-label').text('Добавить книгу');
        modal.show();
    })

    let filterTimeout;
    $('#filter-name, #filter-author').keyup(function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(updateTable, 500);
    });
})


function updateTable(offset = 0){
    $('body main #card-table *').remove();

    const filters = {
        limit: $('#select-pagination-limit').val(),
        offset: offset,
        orderField: $('select[name="orderBy"').val(),
        orderBy: $('select[name="orderRule"').val(),
        qname: $('#filter-name').val(),
        qauthor: $('#filter-author').val(),
        userFilter: $('select[name="books_uploaders"]').val(),
    };

        $.ajax({
        url: '/list',
        method: 'GET',
        data: filters,
        dataType: 'json',
        success: function(response){
            const cardTemplate = $('#book-card-template').html();
            response.content.forEach(book => {
                const card = $(cardTemplate);
                $(card).attr('data-book-id', book.id);

                $(card).find('.book-name').text(book.name);
                $(card).find('.book-author').text(book.author);
                $(card).find('.book-upload-date').text(book.uploadDate);
                $(card).find('.book-read-date').text(book.readDate);

                if (book.linkCover) {
                    $(card).find('#cover-preview').attr('src', `/book/${book.id}/cover`);
                }

                if (book.linkFile) {
                    $(card).find('.book-download-btn').attr('href', `/book/${book.id}/file`);
                    $(card).find('.book-download-btn').removeAttr('hidden');
                }

                if (book.canEdit) {
                    $(card).find('.book-edit-btn').removeAttr('hidden');
                    $(card).on('click', '.book-edit-btn', function(){
                        const modal = new bootstrap.Modal($('#addBookModal').first());
                        $('#bookId').val(book.id);
                        $('#name').val(book.name);
                        $('#author').val(book.author);
                        $('#allow_download').prop('checked', book.allowPublicDownload == 'true');

                        const [d,m,y] = book.readDate.split('-');
                        $('#read_date').val(`${y}-${m}-${d}`);
                        $('#addBookModal-label').text('Редактировать книгу');
                        modal.show();
                    })
                    $(card).find('.book-delete-btn').removeAttr('hidden');
                    $(card).on('click', '.book-delete-btn', function(){
                        const isConfirmed = confirm("Вы уверены что хотите удалить книгу?");
                        if (!isConfirmed) {
                            return
                        }

                        $.ajax({
                            url: `/book/${book.id}`,
                            type: 'DELETE',
                            success: function(response){
                                alert("Книга была успешно удалена!");
                            },
                            error: function(xhr, status, error){
                                if (status == 400){
                                    alert("Книга возможно была удалена ранее");
                                }
                                else{
                                    alert("Ошибка при удалении книги: " + error)
                                }
                            },
                        });
                        updateTable();
                    })
                }

                $('#card-table').append(card);
            });
            updatePagination(response.total, filters.limit);
        },
        error: function(xhr, status, error){
            console.error('Ошибка запроса книг: ', error);
        }
    })
}

function updatePagination(totalItems, itemsPerPage){
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const $pagination = $('#pagination ul');

    $pagination.empty();

    for (let i = 1; i <= totalPages; i++){
        $pagination.append(`
            <li class="page-item">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
    }

    $pagination.on('click', '.page-link', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        const offset = (page - 1) * itemsPerPage;
        updateTable(offset);
    });
}
