<?php
require_once '../../includes/session_start.php';
require_once '../../includes/toast.php';
require_once '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_recover_password'])) {
    $email = $_POST['email_recover_password'] ?? '';
} elseif (isset($_SESSION['email_recover_password'])) {
    $email = $_SESSION['email_recover_password'];
    unset($_SESSION['email_recover_password']);
} else {
    redirect_with_toast('../../pages/recover_password/enter_email.php', 'Dados não fornecidos', 'danger');
    exit();
}


if (empty($email)) {
    redirect_with_toast('../../pages/recover_password/enter_email.php', 'O campo de E-mail deve ser preenchido', 'danger');
    exit();
}


require_once '../../private/config/db_connection.php';
$db = new db_connection();
$conn = $db->get_connection();


$sql = "SELECT * FROM usuario WHERE email = ?";
$result = $conn->execute_query($sql, [$email]);
if ($result && $result->num_rows > 0) {

    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $verification_code = '';
        for ($i = 0; $i < 8; $i++) {
            $verification_code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $sql = "SELECT codigo FROM codigo_de_verificacao WHERE codigo = ?";
        $check = $conn->execute_query($sql, [$verification_code]);
        $has_duplicate = $check && $check->num_rows > 0;
        if ($check) $check->free();
    } while ($has_duplicate);


    $sql = "DELETE FROM codigo_de_verificacao WHERE fk_usuario_email = ?";
    $conn->execute_query($sql, [$email]);


    $sql = "INSERT INTO codigo_de_verificacao (codigo, fk_usuario_email) VALUES (?, ?)";
    $conn->execute_query($sql, [$verification_code, $email]);

    $db->close_connection();


    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../private/config');
    $dotenv->load();

    $SMTP_HOST = $_ENV['SMTP_HOST'];
    $SMTP_PORT = (int) $_ENV['SMTP_PORT'];
    $SMTP_USER = $_ENV['SMTP_USER'];
    $SMTP_PASS = $_ENV['SMTP_PASS'];

    date_default_timezone_set('America/Sao_Paulo');
    $expiration_time = date("d/m/Y H:i:s", strtotime("+3 minutes"));
    $html_message = '
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Recuperação de Senha - ChronoCert</title>
        <style>
            body { font-family: "Times New Roman", Times, serif; font-size: 22px; color: #333; line-height: 1.8; background-color: #f2f2f2; margin: 0; padding: 0; }
            .container { width: 90%; max-width: 650px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
            h2 { text-align: center; color: #0056b3; font-size: 30px; margin-bottom: 20px; }
            p { margin: 20px 0; }
            .justified { text-align: justify; }
            .centered { text-align: center; }
            .code { font-family: "Times New Roman", Times, serif; font-size: 34px; font-weight: bold; background: #f8f8f8; padding: 20px 30px; display: block; letter-spacing: 3px; border: 3px dashed #bbb; border-radius: 6px; margin: 20px auto; text-align: center; max-width: 100%; }
            .instructions { font-size: 20px; background: #e9f5ff; padding: 15px; border: 1px solid #b3d7ff; border-radius: 6px; margin: 30px 0; }
            .footer { font-size: 18px; color: #777; margin-top: 40px; padding-top: 20px; border-top: 2px solid #ccc; text-align: center; background-color: #f9f9f9; }
            @media (max-width: 480px) { body { font-size: 20px; } h2 { font-size: 26px; } .code { font-size: 30px; padding: 15px 20px; } }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Recuperação de Senha - ChronoCert</h2>
            <p>Olá,</p>
            <p class="justified">Recebemos sua solicitação de recuperação de senha em <strong>' . date("d/m/Y H:i:s") . '</strong>. Para continuar, utilize o código abaixo:</p>
            <p class="code">' . $verification_code . '</p>
            <div class="instructions">
                <p class="justified">Se você não solicitou esta recuperação, por favor, desconsidere este e-mail.</p>
                <p class="justified">Esse código é válido por 3 minutos. Caso expire, solicite um novo código.</p>
                <p class="justified">Este código vai expirar em <strong>' . $expiration_time . '</strong>.</p>
            </div>
            <p class="centered">Para sua segurança, recomendamos que você não compartilhe esse código com ninguém.</p>
            <div class="footer">
                <p class="centered">Este é um e-mail automático, por favor não responda.</p>
                <p class="centered">&copy; ' . date("Y") . ' ChronoCert.</p>
            </div>
        </div>
    </body>
    </html>
    ';

    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = $SMTP_HOST;
    $mail->Port = $SMTP_PORT;
    $mail->SMTPSecure = $SMTP_PORT === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPAuth = true;
    $mail->Username = $SMTP_USER;
    $mail->Password = $SMTP_PASS;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($SMTP_USER, 'Suporte ChronoCert');
    $mail->addReplyTo($SMTP_USER, 'Suporte ChronoCert');
    $mail->addAddress($email);
    $mail->Subject = 'Recuperação de Senha - ChronoCert';
    $mail->msgHTML($html_message);
    $mail->AltBody = 'Utilize o código a seguir para redefinir sua senha: ' . $verification_code;

    if (!$mail->send()) {
        redirect_with_toast('../../pages/recover_password/enter_email.php', 'Erro ao enviar e-mail: ' . $mail->ErrorInfo, 'danger');
        exit();
    }

    $_SESSION['email_recover_password'] = $email;
    redirect_with_toast('../../pages/recover_password/recover_password.php', 'Foi enviado no seu e-mail um código para redefinir sua senha', 'success');
    exit();
} else {
    if ($result) $result->free();
    $db->close_connection();
    redirect_with_toast('../../pages/recover_password/enter_email.php', 'E-mail não encontrado', 'danger');
    exit();
}
