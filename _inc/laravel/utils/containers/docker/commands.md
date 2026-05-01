# Docker — Comandos utilizados

> Registro de comandos Docker executados no contexto do ERP Brand New Ideas Company.
> Inclui builds locais, docker-compose e integração com minikube.

## 20260416

```bash
# ── Docker Compose (desenvolvimento local sem K8s) ─────────────────────

# Subir stack completa (app + nginx + mysql + redis)
docker compose up -d --build

# Parar stack
docker compose down

# Parar e remover volumes (DESTRUTIVO)
docker compose down -v

# Executar artisan via docker compose
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan key:generate

# Logs da aplicação
docker compose logs -f app

# ── Docker build para minikube ──────────────────────────────────────────

# Configurar Docker para usar daemon do minikube (OBRIGATÓRIO antes do build)
eval $(minikube docker-env)

# Build da imagem da aplicação para minikube (sem cache)
docker build -t erp-brand-new-ideas-company-app:latest . --no-cache

# Build da imagem da aplicação para minikube (com cache)
docker build -t erp-brand-new-ideas-company-app:latest .

# Listar imagens no daemon do minikube
docker images | grep erp-brand-new-ideas-company

# Reverter Docker para daemon local
eval $(minikube docker-env -u)

# ── Docker genérico ─────────────────────────────────────────────────────

# Limpar imagens não utilizadas
docker image prune -f

# Limpar tudo (containers parados, imagens sem tag, networks, build cache)
docker system prune -af --volumes
```
