<?php
session_start();

$userFile = "Data/users.txt";
$notesDir = "Data/notes";
$secret = "S0m3R@nd0mS@lt"; // نفس السر المستخدم في الريجيستر

//  التحقق من وجود السيشن 
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: Home.html");
    exit;
}

$token = $_SESSION['user'];      // بيسجل التوكن في ماغير عشان هيروح الملف بتاع اليوزر يشوف هي موجوده ولا لا عشان يتاكد من تسجيل للدوخول بشكل صحيح
$validUser = false;
$email = "";

//  التحقق من التوكن مقابل بيانات الملف
if (file_exists($userFile)) {
    $users = file($userFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($users as $user) {
        list($storedEmail, $hash, $storedToken) = array_pad(explode('|', $user), 3, '');
        if ($storedToken === $token) {
            $validUser = true;
            $email = $storedEmail;                 // لو لقاه مسجل بشكل صحيح بيحفظ الاميل عشان هو دا هيكون المتاح الي بيه بيعرف بيه الملف الي هيفتحهوله
            break;
        }
    }
}

//  لو طلع مفيش سيشن زي الي هو داخل بيها دي في الداتا بيز يبقا الغيها من السيشنز ورجعه للهوم ودا شبه اللوج اوت 
if (!$validUser) {
      session_unset();           // مسح البيانات
    session_destroy();          // تدمير الجلسة
    header("Location: Home.html");   // دا الريدايركت للهوم
    exit;
}

//  بنحدد الملف بتاع النوت الخاص بالمستخدم عن طريق الاميل والسيكريت كي الي بنستعمله في تكوين اسم الملف
$noteFile = $notesDir . "/" . hash('sha256', $email . $secret) . ".txt";

//  إضافة نوت جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note'])) {
    $note = trim($_POST['note']);
    if (!empty($note)) {
        $safeNote = htmlspecialchars($note, ENT_QUOTES, 'UTF-8'); // حماية من XSS
        file_put_contents($noteFile, $safeNote . "\n", FILE_APPEND); // بيعمل اضافه للمحتوي بتاع الملف بدون مسح القديم زي الريت
    }
}

//  تسجيل الخروج
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: Home.html");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Take Note</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
        }

        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        form input[type="text"] {
            width: 95%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        form input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            background-color: #17a2b8;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #138496;
        }

        .logout {
            background-color: #dc3545;
            margin-top: 10px;
        }

        .logout:hover {
            background-color: #c82333;
        }

        h3 {
            margin-top: 30px;
            text-align: center;
            color: #555;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            background: #f8f9fa;
            margin: 5px 0;
            padding: 10px 15px;
            border-radius: 5px;
            border-left: 5px solid #17a2b8;
            word-wrap: break-word;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Take Note</h2>

        <form method="post">
            <input type='text' name='note' placeholder="Write your note here..." required>
            <input type="submit" value="Add Note">
        </form>

        <form method="post">
            <input type="submit" name="logout" value="Logout" class="logout">
        </form>

        <h3>Your Notes:</h3>
        <?php
        if (file_exists($noteFile)) {
            $notes = file($noteFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            echo "<ul>";
            foreach ($notes as $note) {
                echo "<li>" . $note . "</li>";
            }
            echo "</ul>";
        }
        ?>

    </div>

</body>

</html>