<?php
require __DIR__ . '/includes/bootstrap.php';
require_admin();

$pdo = db();
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_valid()) {
        set_flash('error', 'Sua sessão expirou. Atualize a página e tente novamente.');
        redirect_to('admin.php');
    }

    $action = (string) ($_POST['action'] ?? '');
    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);

    try {
        if ($action === 'update' && $userId) {
            $name = trim((string) ($_POST['name'] ?? ''));
            $role = (string) ($_POST['role'] ?? 'user');
            if ($name === '' || mb_strlen($name) > 120) {
                throw new RuntimeException('Confira o nome informado.');
            }
            if (!in_array($role, ['user', 'admin'], true)) {
                throw new RuntimeException('O perfil selecionado não é válido.');
            }

            $accountCheck = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
            $accountCheck->execute(['id' => $userId]);
            if (!$accountCheck->fetchColumn()) {
                throw new RuntimeException('Essa conta não foi encontrada. Atualize a página e tente novamente.');
            }

            if ((int) $userId === (int) current_user()['id']) {
                $update = $pdo->prepare('UPDATE users SET name = :name WHERE id = :id');
                $update->execute(['name' => $name, 'id' => $userId]);
                $_SESSION['user']['name'] = $name;
            } else {
                $update = $pdo->prepare('UPDATE users SET name = :name, role = :role WHERE id = :id');
                $update->execute(['name' => $name, 'role' => $role, 'id' => $userId]);
            }

            set_flash('success', 'Alterações salvas. O e-mail da conta permanece fixo.');
            redirect_to('admin.php?edit=' . (int) $userId);
        }

        if ($action === 'delete' && $userId) {
            if ((int) $userId === (int) current_user()['id']) {
                throw new RuntimeException('Você não pode excluir a própria conta por esta tela.');
            }
            $delete = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $delete->execute(['id' => $userId]);
            set_flash('success', 'Usuário excluído.');
            redirect_to('admin.php');
        }

        throw new RuntimeException('A ação solicitada não é válida.');
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        $error = $exception->getCode() === '23000'
            ? 'Já existe uma conta com esse e-mail.'
            : 'Não consegui salvar a alteração no MySQL.';
    } catch (Throwable) {
        $error = 'Não consegui concluir essa ação. Tente novamente.';
    }
}

$search = trim((string) ($_GET['q'] ?? ''));
$params = [];
$sql = 'SELECT id, name, email, role, created_at FROM users';
if ($search !== '') {
    $sql .= ' WHERE name LIKE :search OR email LIKE :search';
    $params['search'] = '%' . $search . '%';
}
$sql .= ' ORDER BY created_at DESC, id DESC';
$list = $pdo->prepare($sql);
$list->execute($params);
$users = $list->fetchAll();

$stats = [
    'total' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'members' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'admins' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'movies' => (int) $pdo->query('SELECT COUNT(*) FROM movies WHERE is_published = 1')->fetchColumn(),
];

$editing = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
if ($editId) {
    $statement = $pdo->prepare('SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $editId]);
    $editing = $statement->fetch() ?: null;
}

$pageTitle = 'Administração';
$activeNav = 'admin';
require __DIR__ . '/includes/header.php';
?>
<section class="admin-page">
    <div class="admin-welcome">
        <div><p class="eyebrow">STUDIOFLIX / VISÃO GERAL</p><h1>Olá, <?= h(explode(' ', trim(current_user()['name']))[0] ?? 'admin') ?>.</h1><p class="page-lede">Sua comunidade e seus acessos em um só lugar.</p></div>
        <div class="admin-date"><span class="eyebrow">HOJE</span><strong><?= h(date('d.m.Y')) ?></strong></div>
    </div>

    <?php if ($error !== ''): ?><div class="flash flash-error" role="alert"><?= h($error) ?></div><?php endif; ?>

    <div class="admin-stat-grid">
        <article class="stat-card stat-card-featured"><div class="stat-card-top"><span class="eyebrow">01 / CONTAS</span><span class="stat-card-mark">SF</span></div><strong><?= $stats['total'] ?></strong><span>Perfis na comunidade</span></article>
        <article class="stat-card"><div class="stat-card-top"><span class="eyebrow">02 / MEMBROS</span><span class="stat-card-mark">+ </span></div><strong><?= $stats['members'] ?></strong><span>Contas de assinantes</span></article>
        <article class="stat-card"><div class="stat-card-top"><span class="eyebrow">03 / EQUIPE</span><span class="stat-card-mark">◆</span></div><strong><?= $stats['admins'] ?></strong><span>Administradores</span></article>
        <article class="stat-card"><div class="stat-card-top"><span class="eyebrow">04 / CATÁLOGO</span><span class="stat-card-mark">▶</span></div><strong><?= $stats['movies'] ?></strong><span>Títulos publicados</span></article>
    </div>

    <div class="admin-content-grid">
        <section class="admin-table-card">
            <div class="table-card-heading">
                <div><p class="eyebrow">COMUNIDADE STUDIOFLIX</p><h2>Contas e acessos</h2></div>
                <form class="admin-search" action="admin.php" method="get"><label class="sr-only" for="user-search">Buscar usuários</label><input id="user-search" name="q" type="search" placeholder="Buscar nome ou e-mail" value="<?= h($search) ?>"><button type="submit">Buscar</button></form>
            </div>
            <div class="table-scroll">
                <table class="user-table">
                    <thead><tr><th>Nome</th><th>Perfil</th><th>Cadastro</th><th class="table-actions-heading">Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $listedUser): ?>
                        <tr class="<?= (int) $listedUser['id'] === (int) $editId ? 'is-editing' : '' ?>">
                            <td><div class="table-user"><span class="avatar avatar-small"><?= h(initials($listedUser['name'])) ?></span><span><strong><?= h($listedUser['name']) ?></strong><small><?= h($listedUser['email']) ?></small></span></div></td>
                            <td><span class="role-pill <?= $listedUser['role'] === 'admin' ? 'role-admin' : '' ?>"><?= $listedUser['role'] === 'admin' ? 'Admin' : 'Comum' ?></span></td>
                            <td><?= h(date('d/m/Y', strtotime($listedUser['created_at']))) ?></td>
                            <td><div class="table-actions">
                                <a class="text-link" href="admin.php?edit=<?= (int) $listedUser['id'] ?>">Editar</a>
                                <?php if ((int) $listedUser['id'] !== (int) current_user()['id']): ?>
                                    <form method="post" action="admin.php" data-confirm="Excluir esta conta? Esta ação não pode ser desfeita.">
                                        <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= (int) $listedUser['id'] ?>">
                                        <button class="text-button text-danger" type="submit">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($users === []): ?><tr><td colspan="4" class="table-empty">Nenhum usuário encontrado.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-card-foot"><span>Exibindo <?= count($users) ?> conta(s)</span><span>Os dados são salvos no MySQL</span></div>
        </section>

        <aside class="admin-side-column <?= $editing ? 'is-editing' : '' ?>">
            <?php if ($editing): ?>
                <section class="admin-edit-card">
                    <p class="eyebrow"><?= (int) $editing['id'] === (int) current_user()['id'] ? 'SUA CONTA ADMINISTRADORA' : 'EDITANDO CONTA' ?></p><h2><?= h($editing['name']) ?></h2>
                    <p class="admin-edit-intro">Atualize os dados da conta. O e-mail de acesso é fixo.</p>
                    <form class="stack-form" method="post" action="admin.php?edit=<?= (int) $editing['id'] ?>">
                        <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="user_id" value="<?= (int) $editing['id'] ?>">
                        <label class="field-label" for="edit-name">Nome completo</label><input class="field-input" id="edit-name" name="name" type="text" value="<?= h($_POST['name'] ?? $editing['name']) ?>" maxlength="120" autocomplete="name" required>
                        <label class="field-label" for="edit-email">E-mail da conta</label><input class="field-input" id="edit-email" type="email" value="<?= h($editing['email']) ?>" readonly aria-readonly="true" autocomplete="email">
                        <small class="field-help">Este e-mail não pode ser alterado.</small>
                        <label class="field-label" for="edit-role">Tipo de acesso</label>
                        <?php if ((int) $editing['id'] === (int) current_user()['id']): ?>
                            <input class="field-input" id="edit-role" value="Administrador — sua própria função não pode ser alterada" disabled>
                            <input type="hidden" name="role" value="admin">
                        <?php else: ?>
                            <?php $selectedRole = $_POST['role'] ?? $editing['role']; ?>
                            <select class="field-input" id="edit-role" name="role"><option value="user" <?= $selectedRole === 'user' ? 'selected' : '' ?>>Usuário comum</option><option value="admin" <?= $selectedRole === 'admin' ? 'selected' : '' ?>>Administrador</option></select>
                        <?php endif; ?>
                        <button class="button button-primary button-wide" type="submit">Salvar dados da conta</button><a class="text-link text-center" href="admin.php">Cancelar edição</a>
                    </form>
                </section>
            <?php else: ?>
                <section class="admin-side-card admin-side-card-dark">
                    <p class="eyebrow">GESTÃO DA COMUNIDADE</p><h2>Quem assiste, faz parte.</h2><p>Edite o nome, e-mail de acesso e perfil de cada conta. As alterações ficam salvas no MySQL.</p><span class="admin-side-mark">STUDIOFLIX&nbsp; / &nbsp;ADMIN</span>
                </section>
                <section class="admin-side-card admin-side-card-outline">
                    <p class="eyebrow">ATALHO</p><h3>Seu perfil</h3><p>Revise seus próprios dados de acesso.</p><a class="text-link" href="perfil.php">Abrir meu perfil</a>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
