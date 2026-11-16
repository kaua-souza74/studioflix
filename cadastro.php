<?php
require __DIR__ . '/includes/bootstrap.php';

$authTitle = 'Crie sua conta';
$authDescription = 'Entre para acessar trailers e acompanhar as suas histórias favoritas.';
$authEyebrow = 'UM LUGAR PARA BOAS HISTÓRIAS';
$authImage = 'poster-interstellar.webp';
$authCaption = 'O próximo destino é seu.';
$authError = '';
$name = '';
$email = '';
$next = safe_next($_GET['next'] ?? null);
$nextQuery = $next !== null ? '?' . http_build_query(['next' => $next]) : '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');
    $next = safe_next($_POST['next'] ?? null);
    $nextQuery = $next !== null ? '?' . http_build_query(['next' => $next]) : '';

    if (!csrf_valid()) {
        $authError = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $authError = 'Preencha nome e e-mail válidos.';
    } elseif (strlen($password) < 8) {
        $authError = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif ($password !== $confirmation) {
        $authError = 'As senhas não são iguais.';
    } else {
        try {
            $statement = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
            $statement->execute([
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
            ]);
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) db()->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'role' => 'user',
            ];
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
            redirect_to($next ?? 'index.php');
        } catch (PDOException $error) {
            if ($error->getCode() === '23000') {
                $authError = 'Este e-mail já está cadastrado. Tente entrar na sua conta.';
            } else {
                $authError = 'Não consegui acessar o MySQL. Importe database/schema.sql pelo phpMyAdmin e tente novamente.';
            }
        } catch (Throwable) {
            $authError = 'Não consegui concluir o cadastro. Confira a configuração local e tente novamente.';
        }
    }
}

require __DIR__ . '/includes/auth_header.php';
?>
<form class="auth-form" method="post" action="cadastro.php">
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= h($next ?? '') ?>">
    <label class="field-label" for="name">Nome completo</label>
    <input class="field-input" id="name" name="name" type="text" autocomplete="name" value="<?= h($name) ?>" placeholder="Como podemos chamar você?" maxlength="120" required>

    <label class="field-label" for="email">E-mail</label>
    <input class="field-input" id="email" name="email" type="email" autocomplete="email" value="<?= h($email) ?>" placeholder="voce@email.com" required>

    <label class="field-label" for="password">Senha</label>
    <div class="password-field">
        <input class="field-input" id="password" name="password" type="password" autocomplete="new-password" placeholder="Mínimo de 8 caracteres" minlength="8" required>
        <button class="password-toggle" type="button" data-password-toggle="password">Mostrar</button>
    </div>

    <label class="field-label" for="password_confirmation">Confirmar senha</label>
    <input class="field-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Digite a senha outra vez" minlength="8" required>

    <button class="button button-primary button-wide" type="submit">Criar minha conta</button>
    <p class="auth-switch">Já tem uma conta? <a href="login.php<?= h($nextQuery) ?>">Entrar</a></p>
</form>
<?php require __DIR__ . '/includes/auth_footer.php'; ?>
