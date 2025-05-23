document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('book-form');
    const coverFile = document.getElementById('coverFile');
    const coverPreview = document.getElementById('cover-preview');
    const bookFile = document.getElementById('bookFile');
    
    // Preview cover image
    coverFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                coverPreview.src = event.target.result;
            };
            reader.readAsDataURL(file);
            
            // Update file input label
            document.querySelector('.custom-file-label[for="coverFile"]').textContent = file.name;
        }
    });
    
    // Update PDF file input label
    bookFile.addEventListener('change', function(e) {
        if (e.target.files[0]) {
            document.querySelector('.custom-file-label[for="bookFile"]').textContent = e.target.files[0].name;
        }
    });
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        // Prepare FormData
        const formData = new FormData();
        formData.append('name', document.getElementById('name').value);
        formData.append('author', document.getElementById('author').value);
        formData.append('read_date', document.getElementById('read_date').value);
        formData.append('allow_download', document.getElementById('allow_download').checked ? '1' : '0');
        
        if (coverFile.files[0]) {
            formData.append('cover', coverFile.files[0]);
        }
        
        if (bookFile.files[0]) {
            formData.append('file', bookFile.files[0]);
        }
        
        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Success handling
                window.location.href = data.redirect || '{{ path('book_index') }}';
            } else {
                // Error handling
                alert(data.message || 'Error saving book');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving the book');
        }
    });
});