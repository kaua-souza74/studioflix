<?php
require __DIR__ . '/includes/bootstrap.php';

$authTitle = 'Entre no Studioflix';
$authDescription = 'Acesse sua conta e continue de onde a sua próxima história começa.';
$authEyebrow = 'BEM-VINDO DE VOLTA';
$authImage = 'hero-arrival.webp';
$authCaption = 'Uma boa história muda a noite.';
$authBodyClass = 'auth-login-page';
$authError = '';
$email = '';
$isModalLoginRequest = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_GET['modal'] ?? '') === '1';
$loginFormAction = 'login.php';
$next = safe_next($_GET['next'] ?? null);
$nextQuery = $next !== null ? '?' . http_build_query(['next' => $next]) : '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $next = safe_next($_POST['next'] ?? null);
    $nextQuery = $next !== null ? '?' . http_build_query(['next' => $next]) : '';

    if (!csrf_valid()) {
        $authError = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $authError = 'Confira o e-mail e a senha informados.';
    } else {
        try {
            $statement = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
            $statement->execute(['email' => $email]);
            $account = $statement->fetch();

            if ($account && password_verify($password, $account['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['_csrf'] = bin2hex(random_bytes(32));
                $_SESSION['user'] = [
                    'id' => (int) $account['id'],
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'role' => $account['role'],
                ];

                $redirectAfterLogin = is_admin() ? ($next ?? 'admin.php') : ($next ?? 'index.php');
                if ($isModalLoginRequest) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => true, 'redirect' => $redirectAfterLogin]);
                    exit;
                }
                redirect_to($redirectAfterLogin);
            }

            $authError = 'E-mail ou senha não conferem.';
        } catch (Throwable) {
            $authError = 'Não consegui acessar o MySQL. Importe database/schema.sql pelo phpMyAdmin e tente novamente.';
        }
    }
}

if ($isModalLoginRequest) {
    http_response_code(422);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'message' => $authError !== '' ? $authError : 'Não consegui entrar. Tente novamente.']);
    exit;
}

require __DIR__ . '/includes/auth_header.php';
?>
<?php require __DIR__ . '/includes/login_form.php'; ?>
<?php require __DIR__ . '/includes/auth_footer.php'; ?>
