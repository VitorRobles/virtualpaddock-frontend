
<?php
$titulo = $titulo ?? 'Virtual Paddock';
$atual = basename($_SERVER['SCRIPT_NAME']);
$u = $u ?? null;
$avatarFoto = '';
if ($u && ($u['tipo'] ?? '') === 'Piloto') {
  $avatarFoto = db()['perfis'][$u['id']]['foto'] ?? '';
  if (!$avatarFoto) {
    $avatarFiles = glob(__DIR__ . '/uploads/perfis/piloto-' . (int)$u['id'] . '-*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];
    sort($avatarFiles);
    if ($avatarFiles) $avatarFoto = 'uploads/perfis/' . basename(end($avatarFiles));
  }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0f2740">
<title><?= e($titulo) ?> | Virtual Paddock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
<script src="assets/js/app.js" defer></script>
</head>
<body>
<a class="skip" href="#main">Pular para o conteúdo</a>
<header class="header"><div class="container header-inner">
  <a class="brand" href="<?= $u ? 'home.php' : 'login.php' ?>" aria-label="Virtual Paddock, início">
    <svg viewBox="0 0 36 36" aria-hidden="true"><path d="M7 33V4M7 5h22l-4 7 4 7H7"/><path d="M13 5v14M19 5v14" stroke-dasharray="3 3"/></svg>virtual<span class="brand-light">paddock</span><span class="brand-dot">.</span>
  </a>
  <?php if ($u): ?>
  <nav aria-label="Navegação principal">
    <?= nv('home.php', 'Carreira', $atual) ?>
    <?= nv('ranking.php', 'Ranking', $atual) ?>
    <?= nv('historico.php', 'Histórico', $atual) ?>
    <?= nv(['campeonatos.php', 'campeonato.php', 'criar_campeonato.php'], 'Campeonatos', $atual) ?>
    <?= nv('incidentes.php', 'Incidentes', $atual) ?>
  </nav>
  <a href="configuracoes.php" aria-label="Configurações" title="Configurações" style="display:grid;place-items:center;width:36px;height:36px;border-radius:8px;color:var(--muted)"><?= ico('cog') ?></a>
  <a class="user-chip" href="perfil_piloto.php" aria-label="Configurações da conta">
    <?php if ($avatarFoto): ?><img class="avatar" style="object-fit:cover" src="<?= e($avatarFoto) ?>" alt=""><?php else: ?><span class="avatar"><?= e(mb_strtoupper(mb_substr($u['nome'], 0, 1))) ?></span><?php endif; ?><span class="user-name"><?= e($u['nome']) ?></span>  </a><?php endif; ?>
</div></header>
<main id="main" tabindex="-1">
