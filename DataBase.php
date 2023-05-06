<?php
class  DataBase {
    private $conn;

    private $id;
    private $firstname;
    private $lastname;
    private $birthday;
    private $gender;
    private $city;

    public function __construct($pdo, $post = null) { //5. Конструктор класса либо создает человека в БД с заданной информацией, либо берет информацию из БД по id (предусмотреть валидацию данных);
        $this->conn = $pdo;
        $this->id = $post['id'];

        if ($this->id) {
            $userdata = $this->take_userdata_from_DB();
            $this->firstname = $userdata['firstname'];
            $this->lastname = $userdata['lastname'];
            $this->birthday = self::calculate_age($userdata['birthday']);
            $this->gender = self::gender_as_text($userdata['gender']);
            $this->city = $userdata['city'];
        } elseif (isset($post)) {
            $this->firstname = $post['firstname'];
            $this->lastname = $post['lastname'];
            $this->birthday = $post['birthday'];
            $this->gender = $post['gender'];
            $this->city = $post['city'];
            if ($this->create_user()) {
                $this->birthday = self::calculate_age($post['birthday']);
                $this->gender = self::gender_as_text($post['gender']);
            }
        } else {
            return false;
        }
    }
    public function create_user() { //1. Сохранение полей экземпляра класса в БД;
        $query = "INSERT INTO `users`
            VALUES (NULL, :firstname, :lastname, :birthday, :gender, :city) ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam('firstname', $this->firstname, PDO::PARAM_STR);
        $stmt->bindParam('lastname', $this->lastname, PDO::PARAM_STR);
        $stmt->bindParam('birthday', $this->birthday, PDO::PARAM_STR);
        $stmt->bindParam('gender', $this->gender, PDO::PARAM_STR);
        $stmt->bindParam('city', $this->city, PDO::PARAM_STR);
        $stmt->execute();
        $this->id = $this->conn->lastInsertId();
        $userdata = $this->user_validation();
        if ($userdata->check != 0) { // подтверждение сохранения данных пользователя в БД
            return true;
        } else {
            return false;
        }
    }
    public function delete_user($id) { //2. Удаление человека из БД в соответствии с id объекта;
        $query = "DELETE FROM `users` WHERE (`id` = :id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id' => $id]);
        $userdata = $this->user_validation();
        //return $userdata->check;
        if ($userdata->check == 0) { // подтверждение удаления данных пользователя в БД
            return true;
        } else {
            return false;
        }
    }
    public function formatting_userdata($obj) { //6. Форматирование человека с преобразованием возраста и (или) пола (п.3 и п.4) в зависимости от параметров (возвращает новый экземпляр stdClass со всеми полями изначального класса).
        return (object)get_mangled_object_vars($obj);
    }
    private function user_validation() {
        $query = "SELECT COUNT(*) AS 'check' FROM users WHERE (id = ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    private function take_userdata_from_DB() {
        $query = "SELECT * FROM users WHERE (id = ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function calculate_age($birthday) { //3. static преобразование даты рождения в возраст (полных лет);
        $birthday_timestamp = strtotime($birthday);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }
        return $age;
    }
    public static function gender_as_text ($gender) { //4. static преобразование пола из двоичной системы в текстовую (муж, жен);
        if ($gender) {
            return "man";
        } else {
            return "woman";
        }
    }
 }

/*Создать класс для работы с базой данных людей

БД содержит поля:
id, имя(только буквы), фамилия(только буквы), дата рождения, пол(0,1), город рождения.

Класс должен иметь поля:
id, имя, фамилия, дата рождения, пол(0,1), город рождения.

Класс должен иметь методы:
1. Сохранение полей экземпляра класса в БД;
2. Удаление человека из БД в соответствии с id объекта;
3. static преобразование даты рождения в возраст (полных лет);
4. static преобразование пола из двоичной системы в текстовую (муж, жен);
5. Конструктор класса либо создает человека в БД с заданной информацией, либо берет информацию из БД по id (предусмотреть валидацию данных);
6. Форматирование человека с преобразованием возраста и (или) пола (п.3 и п.4) в зависимости от параметров (возвращает новый экземпляр stdClass со всеми полями изначального класса).*/
