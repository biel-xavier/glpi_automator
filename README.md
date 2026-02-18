# Automator Plugin for GLPI

O plugin **Automator** permite criar regras automáticas que são executadas quando itens do GLPI são criados ou atualizados.

## Funcionalidades

### Regras de Automação

- Defina regras baseadas em **ItemType** (Computer, User, Group, etc.)
- Configure múltiplas ações por regra
- Ative/desative regras conforme necessário

### Ações Disponíveis

#### Auto Increment

Incrementa automaticamente um campo customizado baseado no valor máximo existente.

**Configuração:**

- **Target Field**: Campo que receberá o próximo valor sequencial
- **Algoritmo**: Busca `MAX(field) + 1` na tabela do ItemType

**Exemplo de Uso:**

- Gerar número de série sequencial para computadores
- Criar IDs customizados para contratos
- Numerar automaticamente equipamentos

## Instalação

1. Copie o plugin para `/marketplace/automator`
2. Acesse **Setup > Plugins** no GLPI
3. Clique em **Install** no plugin Automator
4. O plugin criará as tabelas necessárias e implantará os assets do frontend

## Uso

1. Acesse **Plugins > Automator**
2. Clique em **New Rule**
3. Selecione o **Item Type** (ex: Computer)
4. Adicione uma ou mais **Actions**
5. Configure cada ação (ex: selecione o campo target)
6. Ative a regra

## Desenvolvimento Frontend

O frontend usa React + Vite + Material-UI.

Para modificar o frontend:

```bash
cd web/
npm install
npm run dev    # desenvolvimento
npm run build  # produção
```

Após o build, execute `npm run deploy` ou reinstale o plugin para atualizar os assets.

## Arquitetura

- **Backend**: PHP usando padrão Strategy para Actions
- **Frontend**: React SPA com Material-UI
- **API**: REST-like endpoints em `front/api.php`

## Estrutura de Arquivos

```
automator/
├── setup.php              # Registro do plugin
├── hook.php               # Instalação/desinstalação
├── inc/
│   ├── rule.class.php     # Modelo de Regras
│   ├── action.class.php   # Modelo de Ações
│   └── Action/
│       ├── ActionInterface.php
│       └── AutoIncrement.php
├── front/
│   ├── api.php            # API REST
│   └── rule.php           # Página principal
└── web/                   # Frontend React
    ├── src/
    │   ├── App.jsx
    │   └── components/
    │       └── RuleEditor.jsx
    └── package.json
```

## Próximas Funcionalidades

- Mais tipos de ações (copiar campo, formatar string, etc.)
- Condições para execução de regras
- Suporte a hooks `item_update`
- Logs de execução de regras
