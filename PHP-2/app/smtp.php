<?php
        $to = 'test@mail.com';
        $subject = 'Оставленная заявка';
        $message = "Вами была оставлена заявка со следующим содержанием:\n
            Имя: name\n
            Фамилия: surname\n
            Отчество: patronymic\n
            Номер телефона: phone\n
            Почтовый адрес: email\n
            Комментарий: comment\n
            С вами свяжутся после response_date
        ";
        $headers = 'From: sender@example.com' . "\r\n" .
                'Reply-To: sender@example.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
            