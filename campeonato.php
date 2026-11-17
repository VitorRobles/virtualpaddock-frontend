<?php
require 'config.php';
$u = need_login();
$id = (int)($_GET['id'] ?? 0);
$c = db()['campeonatos'][$id] ?? null;
if (!$c) { flash('Campeonato não encontrado.'); header('Location: campeonatos.php'); exit; }
$eOrganizador = $u['tipo'] === 'Organizador' && (int)$c['org'] === (int)$u['id'];
if ($c['status'] === 'Rascunho' && !$eOrganizador) { flash('Este rascunho ainda não foi publicado.'); header('Location: campeonatos.php'); exit; }
$org = db()['usuarios'][$c['org']]['nome'] ?? 'Organização';
$links = $c['links'] ?? [];
$temLinks = !empty(array_filter($links));
$redes = [
    'site' => ['Site oficial', 'WEB'], 'instagram' => ['Instagram', 'IG'],
    'youtube' => ['YouTube', 'YT'], 'twitch' => ['Twitch', 'TW'], 'discord' => ['Discord', 'DC']
];
$insc = null;
foreach (db()['inscricoes'] as $i) if ($i['uid'] == $u['id'] && $i['camp'] == $id) $insc = $i;
$ocupadas = inscritos($id);
$pode = !$insc && $u['tipo'] === 'Piloto' && $c['status'] === 'Inscrições Abertas' && $ocupadas < $c['vagas'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pode) {
    $_SESSION['db']['inscricoes'][] = ['uid' => $u['id'], 'camp' => $id, 'status' => 'Pendente'];
    $_SESSION['db']['notif'][] = ['uid' => $c['org'], 'texto' => $u['nome'] . ' pediu inscrição em ' . $c['nome'] . '.', 'lida' => 0];
    flash('Inscrição enviada. Fique atento a possíveis avisos dos organizadores.');
    header("Location: campeonato.php?id=$id"); exit;
}
$corridas = array_values(array_filter(db()['corridas'], fn($r) => $r['camp'] == $id));
$titulo = $c['nome'];
include 'partials_header.php';
?>
<style>
.social-links{display:grid;gap:9px;margin:14px 0 22px}.social-link{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f4f6f9;border:1px solid var(--line);border-radius:8px;font-weight:700;font-size:13px}.social-link:hover{border-color:var(--orange);color:var(--blue)}.social-mark{width:34px;height:34px;display:grid;place-items:center;flex:none;border-radius:9px;background:#fff;color:var(--ink);font-size:10px}.org-view{border-left:4px solid var(--orange);padding:14px 16px;background:#fff6ec;border-radius:8px;margin-bottom:18px}.org-view strong{display:block}.org-view p{margin:5px 0 0;font-size:13px}
</style>
<div class="container page-heading">
  <span class="breadcrumb"><a href="home.php"><?= $eOrganizador ? 'Painel do organizador' : 'Carreira' ?></a> / <a href="campeonatos.php">Campeonatos</a> / <?= e($c['nome']) ?></span>
  <h1><?= e($c['nome']) ?></h1>
  <div><span class="tag <?= $c['status'] === 'Inscrições Abertas' ? 'ok' : ($c['status'] === 'Rascunho' ? 'warn' : '') ?>"><?= e($c['status']) ?></span><span class="tag"><?= e($c['categoria']) ?></span><span class="tag"><?= e($c['plataforma']) ?></span></div>
  <p>Organizado por <?= e($org) ?> · <?= date('d/m/Y', strtotime($c['inicio'])) ?> a <?= date('d/m/Y', strtotime($c['fim'])) ?></p>
</div>
<div class="container page-content"><div class="about-grid">
  <div>
    <?php if ($eOrganizador): ?><div class="org-view"><strong>Visão do organizador</strong><p>Esta é a página que os pilotos consultam. Status: <?= e($c['status']) ?> · <?= $ocupadas ?> de <?= (int)$c['vagas'] ?> vagas ocupadas.</p></div><?php endif; ?>
    <h2 style="font-size:34px">Calendário</h2>
    <?php foreach ($corridas as $r): ?>
    <div class="item-cal"><span class="rn">R<?= (int)$r['rodada'] ?></span><div><strong><?= e(db()['pistas'][$r['pista']] ?? 'Pista a definir') ?></strong><small><?= date('d/m/Y H:i', strtotime($r['data'])) ?> · <?= (int)$r['voltas'] ?> voltas</small></div></div>
    <?php endforeach; ?>
    <?php if (!$corridas): ?><div class="empty"><h3>Calendário em preparação</h3><p>O organizador ainda não publicou as etapas.</p></div><?php endif; ?>
    <h2 style="font-size:34px;margin-top:36px">Regulamento</h2>
    <?php if (!empty($c['regras'])): ?><ul class="rules"><?php foreach ($c['regras'] as $reg): ?><li><?= e($reg) ?></li><?php endforeach; ?></ul>
    <?php else: ?><p>O organizador ainda não publicou o regulamento.</p><?php endif; ?>
  </div>
  <aside class="side">
    <?php if ($temLinks): ?><h3>Canais oficiais</h3><div class="social-links">
      <?php foreach ($redes as $chave => [$nomeRede, $marca]): if (empty($links[$chave])) continue; ?>
      <a class="social-link" href="<?= e($links[$chave]) ?>" target="_blank" rel="noopener noreferrer"><span class="social-mark"><?= e($marca) ?></span><?= e($nomeRede) ?> <?= ico('arrow') ?></a>
      <?php endforeach; ?>
    </div><?php endif; ?>
    <h3>Vagas</h3>
    <div class="bar"><i style="width:<?= min(100, round($ocupadas / max(1, $c['vagas']) * 100)) ?>%;background:var(--orange)"></i></div>
    <p style="font-size:13px"><?= $ocupadas ?> de <?= (int)$c['vagas'] ?> pilotos · <?= count($corridas) ?> corridas</p>
    <?php if ($eOrganizador): ?>
      <a class="button dark block" href="campeonatos.php">Voltar à central de campeonatos</a>
    <?php elseif ($pode): ?>
      <button class="button green block" data-open="confirma">Inscrever-se</button>
    <?php elseif ($insc): ?>
      <div class="notice" style="margin:0">Você está inscrito (<?= e(mb_strtolower($insc['status'])) ?>). Fique atento a possíveis avisos dos organizadores.</div>
    <?php elseif ($u['tipo'] !== 'Piloto'): ?>
      <div class="notice" style="margin:0">Apenas contas de piloto podem se inscrever.</div>
    <?php else: ?>
      <div class="notice" style="margin:0">Inscrições indisponíveis: campeonato fechado ou sem vagas.</div>
    <?php endif; ?>
  </aside>
</div></div>
<?php if ($pode): ?>
<dialog id="confirma" aria-labelledby="t-confirma">
  <h2 id="t-confirma">Confirmar inscrição?</h2>
  <p>Você vai se inscrever no <?= e($c['nome']) ?>. O organizador será avisado e poderá aprovar sua vaga.</p>
  <form method="post" class="dialog-actions"><button class="button green">Sim, inscrever</button><button type="button" class="button light" data-close>Não</button></form>
</dialog>
<?php endif; ?>
<?php include 'partials_footer.php'; ?>