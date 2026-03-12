<?php
session_start();


// اول كام سطر نفس الي موجودين في الريجيستر بص عليهم الاول

$message = '';
$userFile = 'Data/users.txt';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $message = '<p class="error">Enter The Email</p>';
    } elseif (empty($password)) {
        $message = '<p class="error">Enter The Password</p>';
    } else {
        $found = false;

        if (file_exists($userFile)) {
            $users = file($userFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            // $index هنا انا بستعمالها عشان اجيب رقم السطر ودا هيفيدني وانا بعمل اوفرايت
            foreach ($users as $index => $user) {

                list($userEmail, $userHash, $userToken) = array_pad(explode('|', $user), 3, '');

                if ($email === $userEmail && password_verify($password, $userHash)) {         // هناك كان بيتشك علي الاميل عشان لو مش موجود يقوله غلط

                    // هنا بيعمل اتشك عشان لو موجود يجبله الداتا

                    // توليد توكن عشوائي كبير
                    $newToken = bin2hex(random_bytes(32)); // 64 حرف HEX → قوي جدًا

                    // تحديث التوكن في الملف (override)
                    $users[$index] = $userEmail . '|' . $userHash . '|' . $newToken;
                    file_put_contents($userFile, implode("\n", $users) . "\n");

                    // حفظ التوكن في السيشن فقط
                    $_SESSION['user'] = $newToken;

                    $found = true;
                    header("Location: Note.php");
                    exit;
                }
            }
        }

        if (!$found) {
            $message = '<p class="error">Invalid email or password</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }

        form h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="email"],
        input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 95%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            background-color: #007BFF;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        p.error {
            color: red;
            font-size: 14px;
            margin: 5px 0;
        }

        p.success {
            color: green;
            font-size: 14px;
            margin: 5px 0;
        }
    </style>
</head>

<body>

    <form action="" method="POST">
        <h2>Login</h2>

        <?php echo $message; ?>

        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <input type="submit" value="Login">
    </form>

</body>

</html>