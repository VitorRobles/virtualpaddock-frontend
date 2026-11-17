# VirtualPaddock Front-end

Protótipo web mobile-first para organização de campeonatos amadores de sim-racing. O projeto representa a interface e os fluxos principais da proposta VirtualPaddock, conectando a descoberta de campeonatos à experiência de carreira do piloto e às ferramentas básicas de organização.

> **Estado do projeto:** protótipo funcional para demonstração acadêmica. Não é uma plataforma pronta para produção e não possui banco de dados nem backend multiusuário.

## O que está implementado

- Login de demonstração com perfis de piloto e organizador.
- Painel inicial e listagem de campeonatos.
- Criação e visualização de campeonatos.
- Fluxo de inscrição e dados de corridas.
- Histórico de carreira, resultados, vitórias e rating demonstrativo.
- Ranking de pilotos.
- Registro e consulta de incidentes.
- Preferências e restauração dos dados de demonstração.
- Interface responsiva com foco em uso mobile.

## Relação com a proposta

O protótipo cobre visualmente vários objetivos da proposta original: descoberta e inscrição em campeonatos, gestão básica para organizadores, histórico, rating e ranking. A implementação usa sessões PHP para simular os dados; por isso, não oferece persistência compartilhada entre usuários ou dispositivos. Autenticação segura, banco de dados, pagamentos/assinaturas, publicação em Android/iOS, integrações com simuladores e cálculo de rating de produção ainda não fazem parte deste front-end.

## Tecnologias

- PHP (sem dependências externas no servidor)
- HTML e CSS
- JavaScript
- Sessões PHP para dados temporários de demonstração

## Executar localmente

### XAMPP no Windows

1. Instale e inicie o Apache pelo XAMPP.
2. Copie esta pasta para `C:\\xampp\\htdocs\\virtualpaddock`.
3. Abra [http://localhost/virtualpaddock/](http://localhost/virtualpaddock/) no navegador.
4. Entre com uma das contas de demonstração abaixo.

O MySQL não é necessário. Os dados ficam na sessão PHP e podem ser restaurados pela tela **Configurações**.

### Servidor PHP embutido

Com PHP instalado, execute na raiz do projeto:

```bash
php -S localhost:8000
```

Depois acesse [http://localhost:8000](http://localhost:8000).

## Contas de demonstração

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Piloto | `marcus@email.com` | `123456` |
| Organizador | `org@email.com` | `123456` |

Essas credenciais são fixas e públicas por serem apenas para demonstração. Não reutilize essa autenticação nem essa senha em qualquer serviço real.

## Estrutura

```text
.
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── uploads/perfis/        # imagens locais; arquivos enviados ficam fora do Git
├── config.php             # sessão, dados iniciais e helpers
├── index.php              # redirecionamento de entrada
├── login.php
├── home.php
├── campeonatos.php
├── criar_campeonato.php
├── campeonato.php
├── historico.php
├── perfil_piloto.php
├── ranking.php
├── incidentes.php
└── configuracoes.php
```

## Próximos passos sugeridos

- Persistir usuários, campeonatos, inscrições e resultados em banco de dados.
- Implementar autenticação, autorização e validação adequadas para produção.
- Formalizar o cálculo e a transparência do rating.
- Adicionar limites de plano gratuito e assinatura para organizadores.
- Avaliar importação de resultados e integrações com iRacing e ACC.
- Evoluir para aplicações Android e iOS após validar a experiência web.

## Créditos

Proposta e protótipo desenvolvidos por **Vitor Hugo Rodrigues Robles** para a disciplina de Desenvolvimento de Software.

## Licença

Nenhuma licença foi definida. Consulte o autor antes de reutilizar ou redistribuir o código.

