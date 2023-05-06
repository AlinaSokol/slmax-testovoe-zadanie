<?php

/*class Connection {
    private $connection;
    private $typeConnection;
    private $address = "localhost";
    private $database = "slmax";
    private $username = "root";
    private $password = "";
    private $charset = 'utf8';
    function __construct($type = 'PDO')
    {
        $this->typeConnection = $type;

        if ($type === 'PDO') {
            try {
                $this->connection = new PDO("mysql:host=$this->address;dbname=$this->database;charset=$this->charset", $this->username, $this->password);
                $this->connection->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {
                echo "Ошибка: " . $e->getMessage();
            }
        }
    }
    public function getConnection()
    {
        return $this->connection;
    }
}*/

require_once 'DataBase.php';
require_once 'List.php';

$address = "localhost";
$database = "slmax";
$username = "root";
$password = "";
$charset = 'utf8';
$pdo = new PDO("mysql:host=$address;dbname=$database;charset=$charset", $username, $password);
$copy = null;
if (empty($_POST)) {
    $message = "Hello, slmax!";
} else {
    $user = new DataBase($pdo, $_POST);
    $message = $user;
    $copy = $user->formatting_userdata($user);
    //$message = $user->first_userdata;
}
function getUserdata($pdo)
{
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/*$connection = new Connection();
$pdo = $connection->getConnection();*/
$users = getUserdata($pdo);

if(isset($_GET['index'])) {
    $user = new DataBase($pdo);
    $del = $user->delete_user($_GET['index']);
    if($del) {
        header("Location: " . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'],true, 302);
    } else {
        $message = "Ошибка удаления";
    }
}


?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>slmax</title>
</head>
<body>
<pre>Clone: <?php echo var_dump($copy); //$message; ?></pre>
<pre><?php echo var_dump($message); //$message; ?></pre>
<p>Write your data:</p>
<form action="" method="post">
    <div>
        <span>firstname </span>
        <input type="text" name="firstname" />
    </div>
    <div>
        <span>lastname </span>
        <input type="text" name="lastname" />
    </div>
    <div>
        <span>birthday </span>
        <input type="date" name="birthday" />
    </div>
    <div>
        <span>gender </span>
        <input type="text" name="gender" />
    </div>
    <div>
        <span>city </span>
        <input type="text" name="city" />
    </div>
    <button type="submit">Submit data</button>
</form>
<p>Or write your id:</p>
<form action="" method="post">
    <div>
        <span>id </span>
        <input type="text" name="id" />
    </div>
    <button type="submit">Submit id</button>
</form>
<table class="table caption-top table-striped table-hover table-responsive" border="2">
    <caption>Content</caption>
    <thead>
    <!--<th scope="col">Id</th>-->
    <th scope="col">Firstname</th>
    <th scope="col">Lastname</th>
    <th scope="col">Birthday</th>
    <th scope="col">gender</th>
    <th scope="col">city</th>
    <th scope="col">DELETE</th>
    </thead>
    <tbody>
    <?php
    foreach ($users as $user) { ?>
        <tr>
            <!--<td><?/*= $user['id']; */?></td>-->
            <td><?= $user['firstname']; ?></td>
            <td><?= $user['lastname']; ?></td>
            <td><?= $user['birthday']; ?></td>
            <td><?= $user['gender']; ?></td>
            <td><?= $user['city']; ?></td>
            <td><a href="<?php echo '?index=' . $user['id']?>">Удалить</a></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</body>
</html>
