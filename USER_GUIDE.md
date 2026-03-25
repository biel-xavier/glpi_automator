# Automator User Guide

## Objetivo

O `automator` serve para aplicar automacoes simples no momento em que um item do GLPI e criado.

## Acesso

- abrir o menu do plugin Automator no GLPI
- entrar na tela de regras

## Como criar uma regra

1. clicar em `New Rule`
2. preencher o nome da regra
3. selecionar o `itemtype`
4. definir se a regra fica ativa
5. adicionar uma ou mais actions
6. salvar

## Campos da regra

- `name`: nome da regra
- `itemtype`: tipo GLPI no qual a automacao sera executada
- `is_active`: `1` para ativo, `0` para inativo
- `actions`: lista de actions executadas em ordem

## Action `AUTO_INCREMENT`

Use essa action quando precisar gerar um valor sequencial em um campo.

Configuracao:

- `field`: nome do campo que vai receber o proximo numero
- `table`: tabela em que o plugin vai buscar o maior valor e gravar o novo

## Exemplo

```json
{
  "name": "Numeracao de impressoras",
  "itemtype": "Printer",
  "is_active": 1,
  "actions": [
    {
      "action_type": "AUTO_INCREMENT",
      "configuration": {
        "field": "locations_id",
        "table": "glpi_printers"
      }
    }
  ]
}
```

## Boas praticas

- usar nomes de regra descritivos
- trabalhar com campos numericos
- validar se a tabela alvo pertence ao item correto
- manter regras pequenas e com objetivo unico

## Problemas comuns

- a regra nao executa: validar se ela esta ativa e se o `itemtype` e suportado
- o valor nao incrementa: revisar `field`, `table` e tipo do campo
- erro ao salvar: revisar permissao do plugin e token CSRF

