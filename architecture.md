# Arquitetura da aplicação

Este diagrama mostra as conexões e agrupa os componentes por responsabilidade, focando no fluxo principal de dados.

```shell
+-------------------------------------------------+
|               Aplicação Frontend                |
|       (React SPA @ localhost:3000)              |
+-------------------------------------------------+
                        |
                        | (1. Requisições API via HTTPS)
                        v
+-------------------------------------------------+
|              Backend (API Laravel)              |
|          (app @ localhost:8000)                 |
|-------------------------------------------------|
| - Endpoints (/api/orders, /api/reports, etc.)   |
| - Webhook (/api/notifications/meli)             |
| - Despacha Jobs para o RabbitMQ                 |
+-------------------------------------------------+
      |         |                      |
      |         | (3. Guarda/Lê Dados) |
      |         +--------------------->+-----------------+
      |                                |  MySQL Database |
      | (2. Envia Tarefas Assíncronas)   |                 |
      v                                +-----------------+
+-------------------------------------------------+
|          Sistema de Filas & Workers             |
|-------------------------------------------------|
|   [RabbitMQ Broker]                             |
|      |                                          |
|      +---> [Worker: Orders] (processa pedidos)  |
|      |        |                                 |
|      +---> [Scheduler] (atualiza tokens)        |
|               |                                 |
+---------------|---------------------------------+
                |
                v (4. Chamadas Autenticadas)
+-------------------------------------------------+
|            API Externa do Mercado Livre         |
+-------------------------------------------------+

```

## Melhorias Futuras e Observações

Atualmente, o cálculo de SLA é feito em tempo real. Para sistemas com um volume de dados muito grande, este cálculo pode ser movido para um processo assíncrono. Neste fluxo, uma tarefa agendada enviaria o trabalho para a fila, e o worker_sla executaria o cálculo pesado em segundo plano, guardando o resultado. Desta forma, o dashboard passaria a carregar os dados de forma instantânea.