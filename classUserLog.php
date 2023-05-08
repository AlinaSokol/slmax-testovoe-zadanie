<?php
try {//В файле со 2ым классом должна проходить проверка на наличие первого класса. Если класс отсутствует вывести ошибку и не объявлять класс 2.
    if (!class_exists('User')) {
        throw new Exception ("Class User not found");
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
}

class UserLog extends User{
    private $id_array = [];
    private $user_log = [];

    public function __construct($pdo) { //1. Конструктор ведет поиск id людей по всем полям БД (поддержка выражений больше, меньше, не равно);
        parent::__construct($pdo, $post = null);

        $stmt = $this->conn->query("SELECT `id` FROM users WHERE (`id` >= 24)");
        $array_from_DB = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($array_from_DB as $array) {
            $this->id_array[] = $array['id'];
        }
    }

    public function create_user_log() { //2. Получение массива экземпляров класса 1 из массива с id людей полученного в конструкторе;
        foreach ($this->id_array as $id) {
            $objectDB = new User($this->conn, ['id' => $id]);
            $this->user_log[$id] = $objectDB;
        }
        return $this->user_log;
    }

    public function clear_user_log() { //3. Удаление людей из БД с помощью экземпляров класса 1 в соответствии с массивом, полученным в конструкторе.
        foreach ($this->id_array as $id) {
            if ($this->delete_user($id)) {
                continue;
            } else {
                return false;
            }
        }
        return true;
    }
}
