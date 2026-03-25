# Automator Technical Guide

## Arquitetura

- `setup.php`: metadados, compatibilidade e menu
- `hook.php`: instalacao, remocao e registro do hook `item_add`
- `inc/rule.class.php`: carga e execucao das regras
- `inc/action.class.php`: despacho das actions
- `inc/Action/AutoIncrement.php`: implementacao da action atual
- `front/api.php`: endpoints do frontend
- `front/rule.php`: bootstrap da UI
- `web/src/*`: frontend React com MUI

## Tabelas

- `glpi_plugin_automator_rules`
- `glpi_plugin_automator_actions`
- permissao de perfil em `glpi_profiles.plugin_automator`

## Fluxo de execucao

1. um item e criado no GLPI
2. o hook `item_add` chama `PluginAutomatorRule::item_add`
3. o plugin busca regras ativas por `itemtype`
4. as actions sao carregadas ordenadas
5. cada action e executada sobre o item criado

## Contrato da regra

- `name`: string
- `itemtype`: string GLPI valida
- `is_active`: inteiro `0` ou `1`
- `actions[]`: lista ordenada

## Contrato da action

- `action_type`: string
- `configuration`: JSON
- `order`: inteiro de ordenacao

## `AUTO_INCREMENT`

Comportamento:

- busca `MAX(CAST(field AS UNSIGNED))`
- calcula `max + 1`
- respeita `entities_id` quando aplicavel
- para `glpi_plugin_fields_*`, filtra tambem por `itemtype`
- faz `UPDATE` no item principal quando a tabela alvo coincide
- faz `UPDATE` ou `INSERT` em tabela complementar por `items_id` + `itemtype`

## API

- `action=get_itemtypes`
- `action=get_fields&itemtype=...`
- `action=get_rules`
- `action=save_rule`
- `action=delete_rule`

Chamadas de escrita devem trafegar como requisicao AJAX do GLPI com CSRF valido.

## Limitacoes

- apenas hook `item_add`
- sem motor de condicoes
- apenas `AUTO_INCREMENT` implementado

## Extensao

- adicionar novas classes em `inc/Action`
- estender `hook.php` para novos eventos
- formalizar validacao de schema por tipo de action

