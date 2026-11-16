<?php
require __DIR__ . '/includes/bootstrap.php';

$authTitle = 'Recupere o acesso';
$authDescription = 'Vamos ajudar você a voltar para a sua conta.';
$authEyebrow = 'ACESSO SEGURO';
$authImage = 'poster-the-abyss.webp';
$authCaption = 'Suas histórias continuam esperando.';
$authError = '';
$notice = '';
$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$validReset = false;
$resetRecord = null;

if ($token !== '' && preg_match('/\A[a-f0-9]{64}\z/', $token)) {
    try {
        $statement = db()->prepare('SELECT id, user_id FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        $statement->execute(['token_hash' => hash('sha256', $token)]);
        $resetRecord = $statement->fetch();
        $validReset = $resetRecord !== false;
    } catch (Throwable) {
        $authError = 'Não consegui validar o link. Confira se o MySQL está configurado.';
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_valid()) {
        $authError = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif (($_POST['mode'] ?? 'request') === 'reset') {
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        if (!$validReset || !$resetRecord) {
            $authError = 'Este link expirou ou já foi usado. Peça uma nova redefinição.';
        } elseif (strlen($password) < 8) {
            $authError = 'A nova senha precisa ter pelo menos 8 caracteres.';
        } elseif ($password !== $confirmation) {
            $authError = 'As senhas não são iguais.';
        } else {
            try {
                $pdo = db();
                $pdo->beginTransaction();
                $update = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :user_id');
                $update->execute(['password_hash' => password_hash($password, PASSWORD_DEFAULT), 'user_id' => $resetRecord['user_id']]);
                $mark = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
                $mark->execute(['id' => $resetRecord['id']]);
                $pdo->commit();
                set_flash('success', 'Senha atualizada. Entre com a nova senha.');
                redirect_to('login.php');
            } catch (Throwable) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $authError = 'Não consegui salvar a nova senha. Tente novamente.';
            }
        }
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $authError = 'Informe um endereço de e-mail válido.';
        } else {
            try {
                $pdo = db();
                $statement = $pdo->prepare('SELECT id, email FROM users WHERE email = :email LIMIT 1');
                $statement->execute(['email' => $email]);
                $account = $statement->fetch();

                if ($account) {
                    $rawToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $rawToken);
                    $pdo->prepare('DELETE FROM password_resets WHERE expires_at < NOW() OR used_at IS NOT NULL')->execute();
                    $insert = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 30 MINUTE))');
                    $insert->execute(['user_id' => $account['id'], 'token_hash' => $tokenHash]);
                    $url = app_base_url() . '/recuperar-senha.php?token=' . $rawToken;
                    send_reset_email($account['email'], $url);
                }

                $notice = 'Se esse e-mail estiver cadastrado, enviaremos um link de redefinição válido por 30 minutos.';
            } catch (Throwable) {
                $authError = 'Não consegui iniciar a redefinição. Confira a configuração do MySQL.';
            }
        }
    }
}

if ($token !== '' && !$validReset && $authError === '') {
    $authError = 'Este link expirou ou já foi usado. Peça uma nova redefinição.';
}

require __DIR__ . '/includes/auth_header.php';
?>
<?php if ($notice !== ''): ?><div class="flash flash-success" role="status"><?= h($notice) ?></div><?php endif; ?>
<?php if ($validReset): ?>
    <form class="auth-form" method="post" action="recuperar-senha.php">
        <?= csrf_field() ?>
        <input type="hidden" name="mode" value="reset">
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <label class="field-label" for="password">Nova senha</label>
        <div class="password-field"><input class="field-input" id="password" name="password" type="password" autocomplete="new-password" minlength="8" placeholder="Pelo menos 8 caracteres" required><button class="password-toggle" type="button" data-password-toggle="password">Mostrar</button></div>
        <label class="field-label" for="password_confirmation">Confirmar nova senha</label>
        <input class="field-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" placeholder="Digite a senha outra vez" required>
        <button class="button button-primary button-wide" type="submit">Salvar nova senha</button>
        <p class="auth-switch"><a href="login.php">Voltar para o login</a></p>
    </form>
<?php else: ?>
    <form class="auth-form" method="post" action="recuperar-senha.php">
        <?= csrf_field() ?>
        <input type="hidden" name="mode" value="request">
        <label class="field-label" for="email">E-mail da conta</label>
        <input class="field-input" id="email" name="email" type="email" autocomplete="email" placeholder="voce@email.com" required>
        <button class="button button-primary button-wide" type="submit">Enviar link de recuperação</button>
        <p class="auth-switch"><a href="login.php">Voltar para o login</a></p>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/includes/auth_footer.php'; ?>
