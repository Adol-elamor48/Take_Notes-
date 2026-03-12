<?php
session_start();

$message = '';       //بعمل متغير عشان اخزن فيه الايرور واطبعها جوه الفروم
$userFile = 'Data/users.txt';             //دا الفولدر الماستر وجواه الملف بتاع البيانات لليوزر
$notesFolder = 'Data/notes/';            // دا فولدر جوه الفولدر بتاع الداتا عشان اخزن فيه ملفات النوتس بتاع كل يوزر

// إنشاء فولدرات لو مش موجودة
if (!file_exists('Data')) mkdir('Data', 0755, true);
if (!file_exists($notesFolder)) mkdir($notesFolder, 0755, true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {          // بيتحقق ان الريكويست جاله علي هيئه بوست 

    $email = trim($_POST['email']);                 // بياخد الاميل وبيزيله من كل المسافات 
    $password = $_POST['password'];                 // بيستقبل الباسورد

    $email = filter_var($email, FILTER_SANITIZE_EMAIL);        // بيعمل فلتريشن للاميل عشان يبقا في صورة الاميل

    if (empty($email)) { // بيشوف الاميل تم ادخاله ولا لا
        $message = '<p class="error">Enter The Email</p>';     //بيخزن الايرور في الرساله الي بخزن فيها الايرور
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {    
        
    // الي بعده دا كله نفس الكلام بس علي كذا حاله مختلفين
        $message = '<p class="error">Invalid Email</p>';
    } elseif (empty($password)) {
        $message = '<p class="error">Enter The Password</p>';
    } elseif (strlen($password) < 8) {
        $message = '<p class="error">Password must be at least 8 characters</p>';

        // لو مفيش اي ايرور نبدا بقا نخش علي الابلكيشن الاساسي
    } else {

        // تحقق إذا الإيميل مسجل قبل كده
        $alreadyExists = false;
        if (file_exists($userFile)) {        // بيشوف الملف بتاع اليوزر موجود ولا لا عشان ممكن يبقا هو اول واحد يسجل
            $users = file($userFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);   //بيعمل لوب علي عناصر الملف عشان  يجزنهم في ايري ويعرف يتشك عليهم
            foreach ($users as $user) {
                list($storedEmail) = explode('|', $user);          // بيفصل بين عناصر الملف عشان كل واحد | بتدل علي حاجه معينه
                if ($storedEmail === $email) {
                    $alreadyExists = true;
                    $message = '<p class="error">Email already registered</p>';    // لو لقي فيه اميل يبقا مسجل قبل كده
                    break;
                }
            }
        }
        // لو طلع مش موجود قبل كده يبدا بقا يسجل بياناته

        if (!$alreadyExists) {
            // هاش للباسورد
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // توليد توكن عشوائي قوي
            $token = bin2hex(random_bytes(32));

            //  كتابة بيانات المستخدم في users.txt 
            $data = $email . "|" . $hash . "|" . $token . "\n";
            file_put_contents($userFile, $data, FILE_APPEND);

            // إنشاء ملف نوت خاص بالمستخدم باسم هاش من الايميل + salt
            $noteFile = $notesFolder . hash('sha256', $email . 'S0m3R@nd0mS@lt') . '.txt';
            if (!file_exists($noteFile)) file_put_contents($noteFile, "");         // لو الملف مش موجود بيكريته

            // حفظ التوكن في السيشن فقط
            $_SESSION['user'] = $token;

            $message = '<p class="success">Register success</p>';
            header("Location: Note.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
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
            background-color: #28a745;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218838;
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
        <h2>Register</h2>
        <?php echo $message; ?>
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <input type="submit" value="Register">
    </form>

</body>

</html>