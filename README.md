# BoraLá

## Introdução

> O BoraLá é um MPV desenvolvido em um hackaton, durante 24 horas. Através de indicadores gerados em compras efetuadas, conseguimos mapear locais onde você e seus amigos mais frequentam, dessa forma podemos sugerir lugares em comum para um encontro, Happy Hour, etc.

## Instalação

* Sistema dividido em dividido em 3 partes (Cliente, API, API Internet Banking). Para configuração do ambiente para a API, foi utilizado o Docker. (Leia README dentro da pasta API)

* A API Internet Banking, simula uma integração com Bancos, Adiquirentes ou Bandeira, onde, dado um usuário, é retornado um número X de transação. (README dentro da pasta API Internet banking)

* O cliente utiliza o Angular, e para manter as dependências utilizamos o Npm, Grunt, Bower, além do Less como pre-processador css. (README dentro da pasta front)
