<?php
// File: public/login.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

$mensagem = '';
if ($_POST) {
    if (login($_POST['email'], $_POST['senha'])) {
        header('Location: index.php');
        exit;
    } else {
        $mensagem = '<div class="alert alert-danger">Credenciais inválidas.</div>';
    }
}


// Após login bem-sucedido
$_SESSION['user_id'] = $user['id'];
$_SESSION['last_login'] = date('Y-m-d H:i:s'); // ADICIONE ISSO
session_regenerate_id(true);


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-center">Login</h3>
                        <?= $mensagem ?>
                        <form method="post">
                            <div class="mb-3">
                                <label>E-mail</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Senha</label>
                                <input type="password" name="senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="esqueci-senha.php">Esqueci minha senha</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>