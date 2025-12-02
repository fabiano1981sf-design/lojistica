<?php
// File: public/esqueci-senha.php
require '../config/database.php';
require '../config/session.php';

use PHPMailer\PHPMailer\PHPMailer;
require '../vendor/autoload.php';

$mensagem = '';
if ($_POST) {
    global $pdo;
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expira = ? WHERE id = ?");
        $stmt->execute([$token, $expira, $user['id']]);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'seuemail@gmail.com';
            $mail->Password = 'suasenhaaqui';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('no-reply@empresa.com');
            $mail->addAddress($email);
            $mail->Subject = 'Recuperação de Senha';
            $mail->Body = "Clique no link para redefinir: http://seusite.com/redefinir-senha.php?token=$token";

            $mail->send();
            $mensagem = '<div class="alert alert-success">Link enviado para seu e-mail.</div>';
        } catch (Exception $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao enviar e-mail.</div>';
        }
    } else {
        $mensagem = '<div class="alert alert-info">Se o e-mail existir, enviaremos um link.</div>';
    }
}
?>
<!DOCTYPE html>
<html><head><title>Esqueci Senha</title></head><body>
<div class="container mt-5"><div class="card"><div class="card-body">
    <h3>Recuperar Senha</h3>
    <?= $mensagem ?>
    <form method="post">
        <input type="email" name="email" class="form-control" placeholder="Seu e-mail" required>
        <button type="submit" class="btn btn-primary mt-3">Enviar</button>
    </form>
</div></div></div>
</body></html>