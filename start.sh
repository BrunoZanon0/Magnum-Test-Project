#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir com cor
print_color() {
    echo -e "${1}${2}${NC}"
}

# Função para verificar se comando foi bem sucedido
check_success() {
    if [ $? -eq 0 ]; then
        print_color $GREEN "✅ $1"
        return 0
    else
        print_color $RED "❌ $1"
        return 1
    fi
}

# Banner inicial
echo "=========================================="
print_color $BLUE "🚀 INICIANDO MAGNUM BANK DOCKER"
echo "=========================================="

# 1. Parar containers existentes
print_color $YELLOW "🛑 Parando containers existentes..."
docker-compose down
check_success "Containers parados"

# 2. Construir images
print_color $YELLOW "🔨 Construindo images (no cache)..."
docker-compose build --no-cache
check_success "Images construídas"

# 3. Iniciar containers
print_color $YELLOW "🚀 Iniciando containers..."
docker-compose up -d
check_success "Containers iniciados"

# 4. Aguardar inicialização
print_color $YELLOW "⏳ Aguardando inicialização ..."
sleep 1

# 5. Verificar status
print_color $YELLOW "📊 Verificando status dos serviços..."
docker-compose ps
check_success "Status verificado"

# 6. Verificar logs da API-1
print_color $YELLOW "📋 Logs da API-1 (últimas 10 linhas)..."
docker-compose logs api-1 --tail=10
check_success "Logs API-1 verificados"

# 7. Verificar logs da API-2
print_color $YELLOW "📋 Logs da API-2 (últimas 5 linhas)..."
docker-compose logs api-2 --tail=5
check_success "Logs API-2 verificados"

# 8. Verificação final
echo "=========================================="
print_color $YELLOW "🔍 VERIFICAÇÃO FINAL"

# Testar se API está respondendo
if curl -s http://localhost:8080/api/health > /dev/null; then
    print_color $GREEN "✅ API respondendo na porta 8080"
    API_STATUS=true
else
    print_color $RED "❌ API não respondendo na porta 8080"
    API_STATUS=false
fi

# Verificar se containers estão rodando
RUNNING_CONTAINERS=$(docker-compose ps --services --filter "status=running")
TOTAL_CONTAINERS=$(docker-compose ps --services | wc -l)

if [ $(echo "$RUNNING_CONTAINERS" | wc -l) -eq $TOTAL_CONTAINERS ]; then
    print_color $GREEN "✅ Todos os $TOTAL_CONTAINERS containers estão rodando"
    CONTAINER_STATUS=true
else
    print_color $RED "❌ Apenas $(echo "$RUNNING_CONTAINERS" | wc -l) de $TOTAL_CONTAINERS containers rodando"
    CONTAINER_STATUS=false
fi

# Resultado final
echo "=========================================="
if [ "$API_STATUS" = true ] && [ "$CONTAINER_STATUS" = true ]; then
    print_color $GREEN "🎉 Docker e Projeto rodando com sucesso ✅"
    echo ""
    print_color $BLUE "🌐 URLs disponíveis:"
    print_color $BLUE "   API: http://localhost:8080/api/health"
    print_color $BLUE "   RabbitMQ: http://localhost:15672 (guest/guest)"
    print_color $BLUE "   PostgreSQL: localhost:5432 (user: user, db: fipe)"
else
    print_color $RED "⚠️  Alguns serviços podem não estar funcionando corretamente"
    print_color $YELLOW "💡 Use 'docker-compose logs' para investigar problemas"
fi

echo "=========================================="