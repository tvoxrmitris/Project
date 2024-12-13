<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form>
        <input type="submit" name="send" value="Gửi">
    </form>

    <?php
// include "PHPMailer/src/PHPMailer.php";
// include "PHPMailer/src/Exception.php";
// include "PHPMailer/src/OAuth.php";
// include "PHPMailer/src/POP3.php";
// include "PHPMailer/src/SMTP.php";
 
require ("PHPMailer\src\PHPMailer.php");
require ("PHPMailer\src\smtp.php");
require ("PHPMailer\src\Exception.php");

$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->IsSMTP();

$mail->SMTPDebug = 1;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'ssl';
$mail->Host = "smtp.gmail.com";
$mail->Port = 465;
$mail->IsHTML(true);
$mail->Username="triminhvo0404@gmail.com";
$mail->Password = "xtdfqrmtbvriqvyr";
$mail->SetFrom("triminhvo0404@gmail.com");
$mail->Subject ="Test";
$mail->Body="Hello";
$mail->AddAddress("triminhvo2202@gmail.com");
if(!$mail->send()){
    echo "Mailer error" .$mail->ErrorInfo;
} else {
    echo "Message have been sent.";
}


 
 
?>
</body>
</html>