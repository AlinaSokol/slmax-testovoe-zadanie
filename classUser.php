<?php
class  User {
    private $id;
    private $firstname;
    private $lastname;
    private $birthday;
    private $gender;
    private $city;
    protected $conn;

    public function __construct($pdo, $post = null) { //5. Конструктор класса либо создает человека в БД с заданной информацией, либо берет информацию из БД по id (предусмотреть валидацию данных);
        foreach ($post as $key => $param) {
            if ($key == 'firstname' || $key == 'lastname' || $key == 'city') {
                preg_match_all('/^[a-zА-Я]{1,50}$/iu', $param, $word);
                if (empty($word[0])) {
                    return false;
                }
            } elseif ($key == 'gender' && !filter_var($param, FILTER_VALIDATE_INT)) {
                return false;
            }
        }
        $this->conn = $pdo;
        $this->id = $post['id'];
        if ($post != null && $this->verify_param($post)) {
            if ($this->id) {
                $userdata = $this->take_userdata_from_DB();
                if ($userdata) {
                    $this->firstname = $userdata['firstname'];
                    $this->lastname = $userdata['lastname'];
                    $this->birthday = self::calculate_age($userdata['birthday']);
                    $this->gender = self::gender_as_text($userdata['gender']);
                    $this->city = $userdata['city'];
                }
            } else {
                $this->firstname = $post['firstname'];
                $this->lastname = $post['lastname'];
                $this->birthday = $post['birthday'];
                $this->gender = $post['gender'];
                $this->city = $post['city'];
                if ($this->create_user()) {
                    $this->birthday = self::calculate_age($post['birthday']);
                    $this->gender = self::gender_as_text($post['gender']);
                }
            }
        }
    }

    public function formatting_userdata() { //6. Форматирование человека с преобразованием возраста и (или) пола (п.3 и п.4) в зависимости от параметров (возвращает новый экземпляр stdClass со всеми полями изначального класса).
        return (object)get_mangled_object_vars($this);
    }

    public function delete_user($id) { //2. Удаление человека из БД в соответствии с id объекта;
        $query = "DELETE FROM `users` WHERE (`id` = :id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id' => $id]);
        $userdata = $this->user_validation($id);
        if ($userdata->check == 0) { // подтверждение удаления данных пользователя в БД
            return true;
        } else {
            return false;
        }
    }

    protected function user_validation($id) { //protected
        $query = "SELECT COUNT(*) AS 'check' FROM users WHERE (`id` = :id)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    private function verify_param($param) {
        foreach ($param as $item) {
            if ($item === "") {
                continue;
            } else {
                return true;
            }
        }
        return false;
    }

    private function create_user() { //1. Сохранение полей экземпляра класса в БД;
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
        $userdata = $this->user_validation($this->id);
        if ($userdata->check != 0) { // подтверждение сохранения данных пользователя в БД
            return true;
        } else {
            return false;
        }
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
