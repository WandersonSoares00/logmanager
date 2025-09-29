**LogManager**
==============

**Descrição**
-------------

O LogManager é uma aplicação web para gerir e monitorar pedidos de contas de vendedores do Mercado Livre. O sistema permite a sincronização de múltiplas contas, o processamento automático de pedidos via webhooks, a gestão de etiquetas de envio e a análise de performance através de relatórios de SLA.

O backend é uma API construída em Laravel, enquanto o frontend foi construído com React e TypeScript.

**Funcionalidades**
-------------------

*   **Sincronização de Contas:** Permite que os utilizadores conectem as suas contas do Mercado Livre de forma segura via OAuth2, com um mecanismo automático de atualização de tokens.
    
*   **Gestão de Pedidos:** Processa notificações de novos pedidos e atualizações em tempo real, guardando-os numa base de dados local.
    
*   **Download de Etiquetas:** Faz o download automático das etiquetas de envio em PDF quando um pedido está pronto para ser despachado, disponibilizando-as na interface.
    
*   **Histórico de Status:** Guarda um log completo de todas as alterações de status de um pedido, criando uma trilha de auditoria.
    
*   **Relatórios de SLA:** Oferece um dashboard com o cálculo do SLA (Service Level Agreement) de tempo de envio semanal e uma lista de pedidos enviados no dia.
    

**Arquitetura e Tecnologias**
-----------------------------

*   **Backend:** PHP 8.2 / Laravel 11
    
*   **Frontend:** React 18 (com Vite e TypeScript)
    
*   **Base de Dados:** MySQL 8
    
*   **Filas de Mensagens:** RabbitMQ para processamento assíncrono de tarefas.
    
*   **Ambiente de Desenvolvimento:** Docker e Docker Compose.
    

**Guia de Instalação e Execução**
---------------------------------

Siga estes passos para configurar e executar o projeto no seu ambiente de desenvolvimento.

### **Pré-requisitos**

*   **Git** instalado.
    
*   **Docker** e **Docker Compose** instalados e em execução.
    
*   Uma **aplicação de teste** criada no [Devcenter do Mercado Livre](https://developers.mercadolivre.com.br/devcenter/create-app) para obter as suas credenciais (APP\_ID e SECRET\_KEY).
    

### **Instalação**

1.  
    ```shell
    git clone https://github.com/WandersonSoares00/logmanager
    
    cd logmanager/backend
    
2.
    ```shell
    Copie o arquivo de exemplo para criar o seu ficheiro de configuração local
    
    cp .env.example .env
    
Agora, abra o ficheiro .env e preencha as suas credenciais do Mercado Livre:

# Credenciais do App Mercado
MELI_APP_ID=SEU_APP_ID_AQUI

MELI_SECRET_KEY=SUA_SECRET_KEY_AQUI

MELI_REDIRECT_URI="http://localhost:8000"

(A MELI_REDIRECT_URI será atualizada depois com o ngrok)

3.  ```shell
    docker compose up -d --build
        
4.  ```shell
    docker compose exec app composer install

5.  ```shell
    docker compose exec app php artisan migrate


### **Utilização**

1. 
    Num novo terminal, inicie o ngrok
    ` ngrok http 8000 `
    Copie a URL https://... que o ngrok lhe fornecer.
    
2.  **Atualizar a Configuração Final**
    
    *   APP\_URL=https://.ngrok-free.app

        SESSION\_DOMAIN=.ngrok-free.app

        MELI\_REDIRECT\_URI="https://.ngrok-free.app/auth/meli/callback"
        
    *   **No painel do Mercado Livre:** Atualize a **URL de Callback** e a **URL de Notificações** da sua aplicação para a sua URL do ngrok (.../auth/meli/callback e .../api/notifications/meli, respetivamente).
            
3.  **Acessar a Aplicação**
    
    *   O seu **Frontend** está agora disponível em: **http://localhost:3000**
        
    *   O seu **Painel do RabbitMQ** está em: **http://localhost:15672** (user: guest, pass: guest)
        

para usar a aplicação no seu navegador, clicar em "Conectar com Mercado Livre" e começar a usar o sistema!

**(Opcional) Popular com Dados de Teste**Se quiser preencher a sua base de dados com pedidos falsos para testar a interface:

```shell
docker compose exec app php artisan db:seed
