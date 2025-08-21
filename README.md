┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   API-1 (HTTP)  │    │   API-2 (Job)   │    │     Redis       │
│   Porta: 9000   │◄──►│   Consumer      │◄──►│     Fila        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nginx         │    │   PostgreSQL    │    │   Banco FIPE    │
│   Porta: 8080   │    │  3 Tabelas      │    │   Dados         │
└─────────────────┘    └─────────────────┘    └─────────────────┘

DOCKER PRECISA ESTÁ LIGADO

Link Postman Environment: https://www.mediafire.com/file/rz3i867xurkef11/Environment_Project.postman_environment.json/file
Link Postman Rotas: https://www.mediafire.com/file/6abouwot5mfuinc/Project_Test.postman_collection.json/file

⚡ Scripts de Gerenciamento
1. Inicialização Completa do Sistema
bash
./start.sh
Funções:

✅ Para containers existentes

✅ Constrói imagens Docker

✅ Inicia todos os serviços

✅ Verifica status e saúde

✅ Mostra URLs de acesso

2. Gerenciamento do Job Consumer
bash
./ListenerJob.sh [comando]  Monitoramento do job


🔄 Fluxo de Funcionamento
1. Consulta à API FIPE
📡 Consulta API Parallelum FIPE

🚗 Três categorias: carros, motos, caminhões

⚡ Busca marcas, modelos e anos

🛡️ Rate limiting e tratamento de erros

2. Processamento Redis
🎯 Armazena dados em fila Redis

📊 Mantém timestamp última atualização

🔄 Sistema de pub/sub para escalabilidade

💾 Cache de dados para performance

3. Armazenamento Banco
🗃️ Salva marcas com verificação de duplicidade

🔍 Índices para busca otimizada

🚀 Endpoints da API
API-1 (Porta 9000)