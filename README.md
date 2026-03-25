# Automator

Plugin de automacao para GLPI voltado a executar regras quando itens sao criados.

## Documentos

- `USER_GUIDE.md`: guia de uso para quem configura regras no plugin
- `TECHNICAL_GUIDE.md`: arquitetura, hooks, tabelas, APIs e pontos de extensao

## Resumo funcional

O plugin permite cadastrar regras por `itemtype`, ativar ou desativar cada regra e executar actions em sequencia. No estado atual, a action implementada e `AUTO_INCREMENT`.

## Capacidades atuais

- execucao automatica no hook `item_add`
- regras por tipo de item
- multiplas actions por regra
- interface web React para cadastro e manutencao

## ItemTypes suportados no hook atual

- `Computer`
- `Monitor`
- `Printer`
- `Software`
- `User`
- `Group`
- `Ticket`
- `Contract`

## Action disponivel

- `AUTO_INCREMENT`

## Estrutura principal

- `setup.php`
- `hook.php`
- `inc/rule.class.php`
- `inc/action.class.php`
- `inc/Action/AutoIncrement.php`
- `front/api.php`
- `front/rule.php`
- `web/src/*`

