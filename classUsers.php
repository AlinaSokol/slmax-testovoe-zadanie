<?php
try {//В файле со 2ым классом должна проходить проверка на наличие первого класса. Если класс отсутствует вывести ошибку и не объявлять класс 2.
    if (!class_exists('User')) {
        throw new Exception ("Class User not found");
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
}

class Users extends User{
    private $id_array = [];
    private $user_log = [];
    public $divided_index;
    public $array_from_DB;
    public function __construct($pdo, $query) { //1. Конструктор ведет поиск id людей по всем полям БД (поддержка выражений больше, меньше, не равно);
        parent::__construct($pdo);
        foreach ($query as $key => $param) {
            preg_match_all('/^[a-zА-Я]{1,50}$/iu', $param, $word);
            if ($key == 'value' && (!filter_var($param, FILTER_VALIDATE_INT) xor empty($word[0]))) {
                return false;
            }
        }
        foreach ($query as $index => $item) {
            if (strpos($index, 'field') !== false) {
                $this->divided_index = explode('_', $index);
                $param_group[$this->divided_index[1]][] = $item;
            } elseif (strpos($index, 'inequality') !== false && strpos($index, $this->divided_index[1]) !== false) {
                $param_group[$this->divided_index[1]][] = $item;
            } elseif (strpos($index, 'value') !== false && strpos($index, $this->divided_index[1]) !== false) {
                $param_group[$this->divided_index[1]][] = $item;
                $parameters[] = implode(' ', $param_group[$this->divided_index[1]]);
                //$this->divided_index = [];
            }
        }
        $search_parameters = implode(' AND ', $parameters);
        $query = $this->conn->query("SELECT `id` FROM users WHERE (?)");
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$search_parameters]);
        $this->array_from_DB = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($this->array_from_DB as $array) {
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
