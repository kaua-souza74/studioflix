<?php
$loginFormAction = $loginFormAction ?? 'login.php';
$loginFormModal = $loginFormModal ?? false;
?>
<form class="auth-form<?= $loginFormModal ? ' auth-modal-form' : '' ?>" method="post" action="<?= h($loginFormAction) ?>" <?= $loginFormModal ? 'data-auth-modal-form' : '' ?>>
    <?= csrf_field() ?>
    <input type="hidden" name="next" value="<?= h($next ?? '') ?>">
    <?php if ($loginFormModal): ?><div class="flash flash-error auth-modal-error" data-auth-modal-error role="alert" hidden></div><?php endif; ?>
    <label class="field-label" for="<?= $loginFormModal ? 'modal-email' : 'email' ?>">E-mail</label>
    <input class="field-input" id="<?= $loginFormModal ? 'modal-email' : 'email' ?>" name="email" type="email" autocomplete="email" value="<?= h($email ?? '') ?>" placeholder="voce@email.com" required>

    <div class="field-label-row">
        <label class="field-label" for="<?= $loginFormModal ? 'modal-password' : 'password' ?>">Senha</label>
        <a class="text-link text-link-small" href="recuperar-senha.php">Esqueci minha senha</a>
    </div>
    <div class="password-field">
        <input class="field-input" id="<?= $loginFormModal ? 'modal-password' : 'password' ?>" name="password" type="password" autocomplete="current-password" placeholder="Sua senha" required>
        <button class="password-toggle" type="button" data-password-toggle="<?= $loginFormModal ? 'modal-password' : 'password' ?>">Mostrar</button>
    </div>

    <button class="button button-primary button-wide" type="submit">Entrar</button>
    <p class="auth-switch">Ainda não tem uma conta? <a href="cadastro.php<?= h($nextQuery ?? '') ?>" <?= $loginFormModal ? 'data-auth-modal-register' : '' ?>>Criar conta</a></p>
</form>
