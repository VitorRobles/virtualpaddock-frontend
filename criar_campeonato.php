<?php
require 'config.php';
$u = need_login();
if ($u['tipo'] !== 'Organizador') { header('Location: home.php'); exit; }

$titulo = 'Criar campeonato';
$erro = null;
$campeonatosDoOrg = array_filter(db()['campeonatos'], fn($c) => $c['org'] == $u['id']);
$ativos = count(array_filter($campeonatosDoOrg, fn($c) => !in_array($c['status'], ['Finalizado', 'Rascunho'], true)));
$limite = $ativos >= 2;
$form = [
    'nome' => '', 'categoria' => '', 'plataforma' => '', 'inicio' => '', 'fim' => '',
    'vagas' => '20', 'status' => 'Inscrições Abertas', 'regras' => '',
    'site' => '', 'instagram' => '', 'youtube' => '', 'twitch' => '', 'discord' => ''
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form as $campo => $valor) $form[$campo] = trim((string)($_POST[$campo] ?? $valor));
    $inicioData = DateTime::createFromFormat('!Y-m-d', $form['inicio']);
    $fimData = DateTime::createFromFormat('!Y-m-d', $form['fim']);
    $inicioValido = $inicioData && $inicioData->format('Y-m-d') === $form['inicio'];
    $fimValido = $fimData && $fimData->format('Y-m-d') === $form['fim'];

    if ($limite && $form['status'] !== 'Rascunho') {
        $erro = 'Você já tem 2 campeonatos publicados ou em andamento. Finalize um para publicar outro.';
    } elseif ($form['nome'] === '' || $form['categoria'] === '' || $form['plataforma'] === '') {
        $erro = 'Preencha o nome, a categoria e o simulador.';
    } elseif (!$inicioValido || !$fimValido || $form['fim'] < $form['inicio']) {
        $erro = 'Informe datas válidas e confira se o fim acontece depois do início.';
    } elseif ((int)$form['vagas'] < 2) {
        $erro = 'O campeonato precisa oferecer pelo menos 2 vagas.';
    } elseif (!in_array($form['status'], ['Rascunho', 'Inscrições Abertas'], true)) {
        $erro = 'Escolha uma opção de publicação válida.';
    } else {
        $links = [];
        foreach (['site' => 'Site', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'twitch' => 'Twitch', 'discord' => 'Discord'] as $campo => $nome) {
            $url = $form[$campo];
            if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url))) {
                $erro = 'Confira o endereço de ' . $nome . '. Use um link completo começando com https://.';
                break;
            }
            if ($url !== '') $links[$campo] = $url;
        }
        if (!$erro) {
            $id = $_SESSION['db']['prox']['camp']++;
            $_SESSION['db']['campeonatos'][$id] = [
                'id' => $id, 'org' => $u['id'], 'nome' => $form['nome'],
                'categoria' => $form['categoria'], 'plataforma' => $form['plataforma'],
                'status' => $form['status'], 'inicio' => $form['inicio'], 'fim' => $form['fim'],
                'vagas' => (int)$form['vagas'],
                'regras' => array_values(array_filter(array_map('trim', explode(';', $form['regras'])))),
                'links' => $links
            ];
            flash($form['status'] === 'Rascunho' ? 'Rascunho salvo. Ele só aparece para você.' : 'Campeonato publicado com sucesso.');
            header('Location: campeonatos.php'); exit;
        }
    }
}
include 'partials_header.php';
?>
<style>
.organizer-create{max-width:900px;margin:0 auto}.organizer-create .creator-intro{background:var(--ink);color:#fff;border-radius:12px;padding:28px 32px;margin-bottom:18px}.creator-intro p{color:#c3cfdc;margin:0}.creator-kicker{display:block;color:#ffb15e;font-size:12px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;margin-bottom:8px}.creator-intro h2{font-size:34px;margin:0 0 8px}.creator-steps{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 18px}.creator-steps a{background:#fff;border:1px solid var(--line);border-radius:8px;padding:11px 16px;font-size:13px;font-weight:700}.creator-steps a:hover,.creator-steps a:focus{border-color:var(--orange);color:var(--ink)}.creator-section{scroll-margin-top:110px;margin-bottom:18px}.creator-section h3{margin-bottom:5px}.creator-section>p{font-size:13px}.social-field{display:flex;align-items:center;gap:12px}.social-mark{width:38px;height:38px;flex:none;display:grid;place-items:center;border-radius:10px;background:#eef2f6;color:var(--ink);font-size:11px;font-weight:800}.creator-actions{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-top:22px}.creator-hint{font-size:12px;color:var(--muted)}
</style>
<div class="container page-heading">
  <span class="breadcrumb"><a href="home.php">Painel do organizador</a> / <a href="campeonatos.php">Campeonatos</a> / Criar</span>
  <h1>Novo campeonato</h1><p>Monte o campeonato, adicione seus canais e escolha se quer publicar agora ou salvar como rascunho.</p>
</div>
<div class="container page-content">
<?php if (false): ?>
  <div class="notice"><strong>Limite de publicações atingido.</strong><p>Seu plano permite 2 campeonatos publicados ou em andamento. Rascunhos não ocupam vaga.</p><a class="button dark" href="campeonatos.php">Voltar aos meus campeonatos</a></div>
<?php else: ?>
  <div class="organizer-create">
    <?php if ($erro): ?><div class="erro" role="alert"><?= e($erro) ?></div><?php endif; ?>
    <div class="creator-intro"><span class="creator-kicker">Área do organizador · etapa de publicação</span><h2>Prepare seu próximo grid.</h2><p>As informações públicas ajudam os pilotos a entender o campeonato e encontrar seus canais oficiais.</p></div>
    <nav class="creator-steps" aria-label="Seções do formulário"><a href="#dados">01 · Dados do campeonato</a><a href="#links">02 · Links e redes sociais</a></nav>
    <form method="post" class="card-form">
      <section class="creator-section" id="dados" aria-labelledby="titulo-dados">
        <span class="creator-kicker">01 · Informações principais</span><h3 id="titulo-dados">Dados do campeonato</h3><p>Os campos com * são necessários para criar ou publicar.</p>
        <label for="nome" style="margin-top:0">Nome do campeonato *</label><input id="nome" name="nome" class="form-control" value="<?= e($form['nome']) ?>" maxlength="90" required>
        <div class="row g-3">
          <div class="col-md-6"><label for="categoria">Categoria *</label><input id="categoria" name="categoria" class="form-control" placeholder="GT3" value="<?= e($form['categoria']) ?>" required></div>
          <div class="col-md-6"><label for="plataforma">Simulador *</label><input id="plataforma" name="plataforma" class="form-control" placeholder="iRacing, ACC" value="<?= e($form['plataforma']) ?>" required></div>
          <div class="col-md-6"><label for="inicio">Início *</label><input id="inicio" name="inicio" type="date" class="form-control" value="<?= e($form['inicio']) ?>" required></div>
          <div class="col-md-6"><label for="fim">Fim *</label><input id="fim" name="fim" type="date" class="form-control" value="<?= e($form['fim']) ?>" required></div>
          <div class="col-md-6"><label for="vagas">Vagas para pilotos *</label><input id="vagas" name="vagas" type="number" min="2" max="200" class="form-control" value="<?= e($form['vagas']) ?>" required></div>
          <div class="col-md-6"><label for="status">Publicação</label><select id="status" name="status" class="form-select"><option value="Inscrições Abertas" <?= $form['status'] === 'Inscrições Abertas' ? 'selected' : '' ?>>Publicar inscrições</option><option value="Rascunho" <?= $form['status'] === 'Rascunho' ? 'selected' : '' ?>>Salvar como rascunho</option></select></div>
        </div>
        <label for="regras">Regulamento</label><textarea id="regras" name="regras" class="form-control" rows="4" placeholder="Separe cada regra com ponto e vírgula. Ex.: Duração: 3 horas; Pit stops obrigatórios"><?= e($form['regras']) ?></textarea>
      </section>
      <section class="creator-section" id="links" aria-labelledby="titulo-links">
        <span class="creator-kicker">02 · Canais oficiais</span><h3 id="titulo-links">Links do organizador</h3><p>Os links preenchidos ficam visíveis na página pública do campeonato. Você pode deixar qualquer campo em branco.</p>
        <label for="site">Site oficial</label><div class="social-field"><span class="social-mark">WEB</span><input id="site" name="site" type="url" class="form-control" placeholder="https://seudominio.com" value="<?= e($form['site']) ?>"></div>
        <label for="instagram">Instagram</label><div class="social-field"><span class="social-mark">IG</span><input id="instagram" name="instagram" type="url" class="form-control" placeholder="https://instagram.com/seu perfil" value="<?= e($form['instagram']) ?>"></div>
        <label for="youtube">YouTube</label><div class="social-field"><span class="social-mark">YT</span><input id="youtube" name="youtube" type="url" class="form-control" placeholder="https://youtube.com/@seucanal" value="<?= e($form['youtube']) ?>"></div>
        <label for="twitch">Twitch</label><div class="social-field"><span class="social-mark">TW</span><input id="twitch" name="twitch" type="url" class="form-control" placeholder="https://twitch.tv/seucanal" value="<?= e($form['twitch']) ?>"></div>
        <label for="discord">Discord</label><div class="social-field"><span class="social-mark">DC</span><input id="discord" name="discord" type="url" class="form-control" placeholder="https://discord.gg/seuconvite" value="<?= e($form['discord']) ?>"></div>
      </section>
      <div class="creator-actions"><button class="button"><?= $form['status'] === 'Rascunho' ? 'Salvar rascunho' : 'Criar campeonato' ?> <?= ico('arrow') ?></button><a class="button light" href="campeonatos.php">Cancelar</a><span class="creator-hint">Links são opcionais; o rascunho fica privado.</span></div>
    </form>
  </div>
<?php endif; ?>
</div>
<?php include 'partials_footer.php'; ?>
