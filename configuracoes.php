<?php
require 'config.php';
$u = need_login(); $titulo = 'Configurações'; $id = $u['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'excluir') {
        unset($_SESSION['db']['usuarios'][$id], $_SESSION['db']['carreira'][$id], $_SESSION['db']['prefs'][$id], $_SESSION['uid']);
        $_SESSION['db']['inscricoes'] = array_values(array_filter($_SESSION['db']['inscricoes'], fn($i) => $i['uid'] != $id));
        header('Location: login.php'); exit;
    }
    if ($acao === 'resetar') { $_SESSION['db'] = seed(); unset($_SESSION['uid']); flash('Dados de demonstração restaurados.'); header('Location: login.php'); exit; }
    $idioma = in_array($_POST['idioma'] ?? '', ['pt-BR', 'en-US', 'es-ES']) ? $_POST['idioma'] : 'pt-BR';
    $_SESSION['db']['prefs'][$id] = ['push' => isset($_POST['push']), 'som' => isset($_POST['som']), 'vib' => isset($_POST['vib']), 'idioma' => $idioma];
    flash('Configurações salvas.'); header('Location: configuracoes.php'); exit;
}
$p = prefs($id);
include 'partials_header.php';
?>
<div class="container page-heading">
  <span class="breadcrumb"><a href="home.php">Carreira</a> / Configurações</span>
  <h1>Configurações</h1><p><?= e($u['nome']) ?> · <?= e($u['email']) ?> · <?= e($u['tipo']) ?></p>
</div>


<div class="container page-content"><div class="about-grid">
  <form method="post" class="card-form">
    <h3>Notificações</h3>
    <div class="switch-row"><label for="push">Notificações push</label><div class="form-check form-switch m-0 p-0"><input class="form-check-input" type="checkbox" role="switch" id="push" name="push" <?= $p['push'] ? 'checked' : '' ?>></div></div>
    <div class="switch-row"><label for="som">Sons do app</label><div class="form-check form-switch m-0 p-0"><input class="form-check-input" type="checkbox" role="switch" id="som" name="som" <?= $p['som'] ? 'checked' : '' ?>></div></div>
    <div class="switch-row"><label for="vib">Vibração</label><div class="form-check form-switch m-0 p-0"><input class="form-check-input" type="checkbox" role="switch" id="vib" name="vib" <?= $p['vib'] ? 'checked' : '' ?>></div></div>
    <label for="idioma" style="margin-top:22px">Idioma</label>
    <select id="idioma" name="idioma" class="form-select">
      <?php foreach (['pt-BR' => 'Português (Brasil)', 'en-US' => 'English', 'es-ES' => 'Español'] as $k => $v): ?><option value="<?= $k ?>" <?= $p['idioma'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
    </select>
    <button class="button block">Salvar alterações</button>
  </form>
  <aside class="side" style="position:static">
    <h3>Conta</h3>
    <p style="font-size:13px">Saia da conta, restaure os dados de demonstração ou exclua sua conta.</p>
    <a class="button light block" href="logout.php">Sair da conta</a>
    <form method="post"><input type="hidden" name="acao" value="resetar"><button class="button light block" style="margin-top:12px">Restaurar dados de demonstração</button></form>
    <button class="button red block" style="margin-top:12px" data-open="excluir">Excluir conta</button>
  </aside>
</div></div>
<dialog id="excluir" aria-labelledby="t-excluir">
  <h2 id="t-excluir">Excluir sua conta?</h2>
  <p>Você tem certeza que deseja <strong class="perigo">excluir sua conta</strong>? Essa ação é irreversível.</p>
  <form method="post" class="dialog-actions"><input type="hidden" name="acao" value="excluir"><button class="button red">Sim, excluir</button><button type="button" class="button light" data-close>Não</button></form>
</dialog>
<?php include 'partials_footer.php'; ?>
