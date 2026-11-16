<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$user = current_user();
$error = '';
$saved = false;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!csrf_valid()) {
        $error = 'Sua sessão expirou. Atualize a página e tente novamente.';
    } elseif ($name === '' || mb_strlen($name) > 120) {
        $error = 'Confira o nome informado.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $error = 'A nova senha precisa ter pelo menos 8 caracteres.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmation) {
        $error = 'As novas senhas não são iguais.';
    } else {
        try {
            $passwordHash = null;
            if ($newPassword !== '') {
                $check = db()->prepare('SELECT password_hash FROM users WHERE id = :id');
                $check->execute(['id' => $user['id']]);
                $savedHash = $check->fetchColumn();
                if (!is_string($savedHash) || !password_verify($currentPassword, $savedHash)) {
                    throw new RuntimeException('Para trocar a senha, confirme a senha atual.');
                }
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            if ($passwordHash !== null) {
                $statement = db()->prepare('UPDATE users SET name = :name, password_hash = :password_hash WHERE id = :id');
                $statement->execute(['name' => $name, 'password_hash' => $passwordHash, 'id' => $user['id']]);
            } else {
                $statement = db()->prepare('UPDATE users SET name = :name WHERE id = :id');
                $statement->execute(['name' => $name, 'id' => $user['id']]);
            }

            $_SESSION['user']['name'] = $name;
            set_flash('success', 'Seu perfil foi atualizado.');
            redirect_to('perfil.php');
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        } catch (PDOException $exception) {
            $error = 'Não consegui salvar as alterações no MySQL.';
        } catch (Throwable) {
            $error = 'Não consegui salvar as alterações. Confira a configuração local e tente novamente.';
        }
    }
}

$pageTitle = 'Meu perfil';
$activeNav = 'profile';
require __DIR__ . '/includes/header.php';
?>
<section class="profile-page">
    <div class="page-intro-row">
        <div>
            <p class="eyebrow">SUA CONTA STUDIOFLIX</p>
            <h1>Meu perfil</h1>
            <p class="page-lede">Mantenha seus dados em dia para aproveitar cada sessão.</p>
        </div>
        <div class="profile-avatar-large"><?= h(initials($user['name'])) ?></div>
    </div>
    <?php if ($error !== ''): ?><div class="flash flash-error" role="alert"><?= h($error) ?></div><?php endif; ?>
    <div class="profile-layout">
        <section class="form-card">
            <div class="form-card-heading"><div><p class="eyebrow">INFORMAÇÕES PESSOAIS</p><h2>Seus dados</h2></div><span class="role-pill"><?= is_admin() ? 'Administrador' : 'Membro' ?></span></div>
            <form class="stack-form" method="post" action="perfil.php">
                <?= csrf_field() ?>
                <label class="field-label" for="name">Nome completo</label>
                <input class="field-input" id="name" name="name" type="text" value="<?= h($_POST['name'] ?? $user['name']) ?>" maxlength="120" required>
                <label class="field-label" for="email">E-mail da conta</label>
                <input class="field-input" id="email" type="email" value="<?= h($user['email']) ?>" readonly aria-readonly="true" autocomplete="email">
                <small class="field-help">Este e-mail é fixo e não pode ser alterado.</small>
                <div class="form-divider"></div>
                <div><p class="eyebrow">SEGURANÇA</p><h3>Trocar senha</h3><p class="muted-copy">Deixe em branco se não quiser alterar a senha.</p></div>
                <label class="field-label" for="current_password">Senha atual</label>
                <input class="field-input" id="current_password" name="current_password" type="password" autocomplete="current-password">
                <label class="field-label" for="new_password">Nova senha</label>
                <input class="field-input" id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8">
                <label class="field-label" for="password_confirmation">Confirmar nova senha</label>
                <input class="field-input" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8">
                <button class="button button-primary" type="submit">Salvar alterações</button>
            </form>
        </section>
        <aside class="profile-side-card">
            <img src="assets/images/poster-interstellar.webp" alt="" loading="lazy">
            <div class="profile-side-copy"><p class="eyebrow">CONTINUE EXPLORANDO</p><h2>Uma nova história está a um clique.</h2><a class="text-link" href="index.php">Voltar ao catálogo</a></div>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
