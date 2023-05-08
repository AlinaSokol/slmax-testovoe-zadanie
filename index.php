<?php
require_once 'classUser.php';
require_once 'classUserLog.php';

$address = "localhost";
$database = "slmax";
$username = "root";
$password = "";
$charset = 'utf8';
$pdo = new PDO("mysql:host=$address;dbname=$database;charset=$charset", $username, $password);

try {
    if (empty($_POST)) {
        $user = "Hello, slmax!";
    } else {
        $user = new User($pdo, $_POST);
        $copy = $user->formatting_userdata();
    }
    function getUserdata($pdo)
    {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $users = getUserdata($pdo);

    $user_list = new UserLog($pdo);
    $list = $user_list->create_user_log();

    if($_GET['index'] === 'deleteLog') {
        if ($user_list->clear_user_log()) {
            header("Location: " . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'],true, 302);
        } else {
            echo 'Error';
        }
    } elseif (isset($_GET['index'])) {
        $user_deleted = new User($pdo);
        $del = $user_deleted->delete_user($_GET['index']);
        if($del) {
            header("Location: " . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'],true, 302);
        } else {
            $message = "Ошибка удаления";
        }
    }
} catch (Exception $exception) {
    echo $exception->getMessage();
} catch (InvalidArgumentException $exParam) {
    echo $exParam->getMessage();
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
<pre><?php var_dump($user); ?></pre>
<pre>Copy: <?php var_dump($copy); ?></pre>
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
    <caption>In DB</caption>
    <thead>
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
<hr/>
<table class="table caption-top table-striped table-hover table-responsive" border="2">
    <caption>User log</caption>
    <thead>
    <th scope="col">Firstname</th>
    <th scope="col">Lastname</th>
    <th scope="col">Birthday</th>
    <th scope="col">gender</th>
    <th scope="col">city</th>
    </thead>
    <tbody>
    <?php
    foreach ($list as $row) {?>
        <tr>
            <td><?= $row->getFirstname() ?></td>
            <td><?= $row->getLastname() ?></td>
            <td><?= $row->getBirthday() ?></td>
            <td><?= $row->getGender() ?></td>
            <td><?= $row->getCity() ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<form action="" method="get">
    <button  type="submit" ><a href="<?php echo '?index=deleteLog'; ?>">Delete log</a></button>
</form>
</body>
</html>
