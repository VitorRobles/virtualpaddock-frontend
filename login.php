<?php
require 'config.php';
if (user()) { header('Location: home.php'); exit; }
$aba = ($_GET['aba'] ?? 'entrar') === 'cadastrar' ? 'cadastrar' : 'entrar';
$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['acao'] ?? '') === 'entrar') {
        $email = trim($_POST['email'] ?? '');
        foreach (db()['usuarios'] as $r) {
            if (strcasecmp($r['email'], $email) === 0 && password_verify($_POST['senha'] ?? '', $r['senha'])) {
                $_SESSION['uid'] = $r['id']; header('Location: home.php'); exit;
            }
        }
        $erro = 'E-mail ou senha incorretos.';
    } else {
        $aba = 'cadastrar';
        $nome = trim($_POST['nome'] ?? ''); $email = trim($_POST['email'] ?? ''); $senha = $_POST['senha'] ?? '';
        $idade = (int)($_POST['idade'] ?? 0);
        $tipo = ($_POST['tipo'] ?? '') === 'Organizador' ? 'Organizador' : 'Piloto';
        $existe = array_filter(db()['usuarios'], fn($x) => strcasecmp($x['email'], $email) === 0);
        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6 || $idade < 10) {
            $erro = 'Informe nome, e-mail válido, idade e uma senha com 6 ou mais caracteres.';
        } elseif ($existe) {
            $erro = 'Este e-mail já está cadastrado. Use a aba Entrar.';
        } else {
            $id = $_SESSION['db']['prox']['usuario']++;
            $_SESSION['db']['usuarios'][$id] = ['id' => $id, 'nome' => $nome, 'email' => $email, 'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'idade' => $idade, 'regiao' => $_POST['regiao'] ?? 'Sul', 'tipo' => $tipo];
            if ($tipo === 'Piloto') $_SESSION['db']['carreira'][$id] = ['rating' => 1000, 'vit' => 0, 'podios' => 0];
            $_SESSION['uid'] = $id;
            flash('Conta criada. Bem-vindo ao paddock!'); header('Location: home.php'); exit;
        }
    }
}
$titulo = 'Entrar';
include 'partials_header.php';
?>
<section class="hero">
  <?= pista_svg() ?>
  <div class="container">
    <div>
      <div class="hero-label">Plataforma de sim-racing</div>
      <h1>Sua carreira.<br>Seu grid.</h1>
      <p>Encontre campeonatos organizados, inscreva-se sem burocracia e acompanhe rating, vitórias e histórico em um só lugar.</p>
      <div class="actions"><a class="button" href="?aba=cadastrar">Criar minha conta <?= ico('arrow') ?></a></div>
    </div>
    <div class="panel">
      <h2><?= $aba === 'entrar' ? 'Entrar no paddock' : 'Criar conta' ?></h2>
      <p style="font-size:14px;margin-bottom:18px"><?= $aba === 'entrar' ? 'Teste com marcus@email.com e a senha 123456.' : 'Leva menos de um minuto.' ?></p>
      <div class="tabs"><a href="?aba=entrar" class="<?= $aba === 'entrar' ? 'on' : '' ?>">Entrar</a><a href="?aba=cadastrar" class="<?= $aba === 'cadastrar' ? 'on' : '' ?>">Cadastrar</a></div>
      <?php if ($erro): ?><div class="erro" role="alert"><?= e($erro) ?></div><?php endif; ?>
      <?php if ($aba === 'entrar'): ?>
      <form method="post">
        <input type="hidden" name="acao" value="entrar">
        <label for="email">E-mail</label><input id="email" name="email" class="form-control" placeholder="exemplo@email.com" required>
        <label for="senha">Senha</label><input id="senha" name="senha" type="password" class="form-control" required>
        <button class="button block">Entrar</button>
      </form>
      <?php else: ?>
      <form method="post">
        <input type="hidden" name="acao" value="cadastrar">
        <label for="nome">Nome de piloto</label><input id="nome" name="nome" class="form-control" maxlength="100" required>
        <label for="email">E-mail</label><input id="email" name="email" type="email" class="form-control" required>
        <label for="senha">Senha (mínimo 6 caracteres)</label><input id="senha" name="senha" type="password" class="form-control" minlength="6" required>
        <div class="row g-2">
          <div class="col-5"><label for="idade">Idade</label><input id="idade" name="idade" type="number" min="10" class="form-control" required></div>
          <div class="col-7"><label for="regiao">Região</label><select id="regiao" name="regiao" class="form-select"><?php foreach (['Norte', 'Nordeste', 'Centro-Oeste', 'Sudeste', 'Sul', 'América do Sul', 'Outra'] as $r): ?><option><?= e($r) ?></option><?php endforeach; ?></select></div>
        </div>
        <label for="tipo">Quero usar como</label><select id="tipo" name="tipo" class="form-select"><option>Piloto</option><option>Organizador</option></select>
        <button class="button block">Criar conta</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<section class="benefits"><div class="container">
  <div class="benefit"><?= ico('flag') ?><div><strong>Inscrição rápida</strong><small>Escolha o campeonato e confirme em dois toques.</small></div></div>
  <div class="benefit"><?= ico('clock') ?><div><strong>Histórico num só lugar</strong><small>Todas as corridas, posições e pontos.</small></div></div>
  <div class="benefit"><?= ico('chart') ?><div><strong>Rating claro</strong><small>Veja sua evolução e sua posição no ranking.</small></div></div>
</div></section>
<?php include 'partials_footer.php'; ?>
