<?php
    header('Content-type: application/json;charset=utf-8');
    date_default_timezone_set("Europe/Moscow");

    function send_letter($arr): void{
        $to = $arr['email'];
        $subject = 'Оставленная заявка';
        $message = "Вами была оставлена заявка со следующим содержанием:\n
            Имя: {$arr["name"]}\n
            Фамилия: {$arr["surname"]}\n
            Отчество: {$arr["patronymic"]}\n
            Номер телефона: {$arr["phone"]}\n
            Почтовый адрес: {$arr["email"]}\n
            Комментарий: {$arr["comment"]}\n
            С вами свяжутся после {$arr["response_date"]}
        ";
        $headers = 'From: sender@example.com' . "\r\n" .
                'Reply-To: sender@example.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
        mail($to, $subject, $message, $headers);
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST'){
        $host = 'db';
        $dbname = $_ENV['DB_NAME'];
        $dbuser = $_ENV['DB_USER'];
        $dbpass = $_ENV['DB_PASSWORD'];
        $port = '5432';
        $conn = pg_connect("host=$host port=$port dbname=$dbname user=$dbuser password=$dbpass");
        if (!$conn){
            http_response_code(500);
            echo json_encode(array('err' => 'Error connecting to database'));
        }
        

        $req = json_decode(file_get_contents("php://input"), true);
        $validation = array();
        // Валидация переданных данных
        $name = $req['name'];
        
        if (!preg_match("/^[a-zA-Zа-яА-Я]+\$/", $req['name'])) {
            $validation += ['invalidName' => null];
        }

        $surname = $req['surname'];
        if (!preg_match("/^[a-zA-Zа-яА-Я]+\$/", $req['surname'])) {
            $validation += ['invalidSurname' => null];
        }

        $patronymics = $req['patronymic'];
        if (!preg_match("/^[a-zA-Zа-яА-Я]+\$/", $req['patronymic'])) {
            $validation += ['invalidPatronymic' => $patronymics];
        }

        $email = $req['email'];
        if (!preg_match('/^[a-zA-Z]\w*(?:\.[a-zA-Z0-9]\w*)*@[a-zA-Z]+\.[a-zA-Z]+$/', $req['email'])) {
            $validation += ['invalidEmail' => null];
        }

        
        $phone = $req['phone'];
        if (strlen(preg_replace('/\D+/', '', $req['phone'])) < 11) {
            $validation += ['invalidPhone' => null];
        }

        $comment = $req['comment'];
        if (strlen($req['comment']) == 0 or strlen($req['comment']) > 255){
            $validation += ["invalidComment" => null];
        }

        if (count($validation) > 0){
            http_response_code(400);
            echo json_encode($validation);
            exit;
        }

        $res = pg_query_params($conn, 'SELECT * from feedback f where f.email=$1 or f.phone=$2', array($email, $phone));
        if (!$res){
            http_response_code(500);
            echo json_encode(array("err"=> pg_last_error()));
        }
        else{
            $arr = pg_fetch_all($res);
            if (isset($arr[0]) and ($arr[0]['email'] != $email or $arr[0]['phone'] != $phone)){
                // В нормальной ситуации почта и телефон - вторичные ключи
                http_response_code(400);
                echo json_encode(array("multipleCollisions"=> "", "msg"=> "Указанные данные принадлежат нескольким заявкам"));
            }
            elseif (isset($arr[0]) and time() - strtotime($arr[0]['updated_at']) < 3600){
                // Если запись была, проверяем срок истечения повторной записи
                // Сообщение об запрете на повторную посылку
                http_response_code(200);
                echo json_encode(array(
                    "name" =>     $arr[0]['user_name'],
                    "surname" =>  $arr[0]['user_surname'],
                    "patronymics" => $arr[0]['user_patronymic'],
                    "email" => $arr[0]['email'],
                    "phone" => $arr[0]['phone'],
                    "comment" => $arr[0]['comment'],
                    "resend_date" => date('H:i d-m-Y',strtotime($arr[0]['updated_at'])+3600)
                ));
            } elseif (isset($arr[0])){
                // Для повторной записи
                $upd_query = pg_query_params($conn, 'UPDATE feedback SET user_name=$1, user_surname=$2, user_patronymic=$3, comment=$6 WHERE email=$4 and phone=$5 RETURNING *', array(
                    $name,
                    $surname,
                    $patronymics,
                    $email,
                    $phone,
                    $comment
                ));
                if (!$upd_query){
                    // В случае ошибки
                    http_response_code(500);
                    echo json_encode(array('err' => pg_last_error()));
                }
                else{
                    $update_ret = pg_fetch_assoc($upd_query);
                    $response_array = array(
                        "name" => $update_ret["user_name"],
                        "surname" => $update_ret["user_surname"],
                        "patronymic" => $update_ret["user_patronymic"],
                        "email" => $update_ret["email"],
                        "phone" => $update_ret["phone"],
                        "comment" => $update_ret["comment"],
                        "response_date" => date('H:i d-m-Y',strtotime($update_ret['updated_at'])+5400),
                    );
                    send_letter($response_array);
                    http_response_code(200);
                    echo json_encode($response_array);
                }
                pg_free_result( $upd_query );
            } else {
                // Для первой записи
                $ins_query = pg_query_params($conn, 'INSERT INTO feedback (user_name, user_surname, user_patronymic, email, phone, comment) VALUES ($1, $2, $3, $4, $5, $6) RETURNING *', array(
                    $name,
                    $surname,
                    $patronymics,
                    $email,
                    $phone,
                    $comment
                ));
                if (!$ins_query){
                    // В случае ошибки
                    http_response_code(500);
                    echo json_encode(array('err' => pg_last_error()));
                }
                else{
                    $insert_ret = pg_fetch_assoc($ins_query);
                    $response_array = array(
                        "name" => $insert_ret["user_name"],
                        "surname" => $insert_ret["user_surname"],
                        "patronymic" => $insert_ret["user_patronymic"],
                        "email" => $insert_ret["email"],
                        "phone" => $insert_ret["phone"],
                        "comment" => $insert_ret["comment"],
                        "response_date" => date('H:i d-m-Y',strtotime($insert_ret['updated_at'])+5400),
                    );
                    send_letter($response_array);
                    http_response_code(200);
                    echo json_encode($response_array);
                }
                pg_free_result( $ins_query );
            }
        }
        pg_free_result($res);
        pg_close($conn);
    }
    else {
        http_response_code(405);
    }
?>