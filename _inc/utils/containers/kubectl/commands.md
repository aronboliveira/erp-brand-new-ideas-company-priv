# kubectl — Comandos utilizados

> Registro de comandos kubectl executados no cluster local ERP Prestech.
> Namespace padrão: `erp-prestech`

## 20260416

```bash
# Aplicar namespace
kubectl apply -f k8s/namespace.yaml

# Aplicar todos os manifests do backend
kubectl apply -f k8s/backend/config_secrets.yaml
kubectl apply -f k8s/backend/nginx-config.yaml
kubectl apply -f k8s/backend/mysql.yaml
kubectl apply -f k8s/backend/redis.yaml
kubectl apply -f k8s/backend/deployment.yaml

# Aplicar frontend + ingress
kubectl apply -f k8s/frontend/deployment.yaml
kubectl apply -f k8s/ingress.yaml

# Verificar todos os recursos no namespace
kubectl get all -n erp-prestech

# Verificar ingress
kubectl get ingress -n erp-prestech

# Verificar pods
kubectl get pods -n erp-prestech

# Descrever pod backend (debug)
kubectl describe pod -l app=erp-backend -n erp-prestech

# Logs do container php-fpm
kubectl logs -l app=erp-backend -c php-fpm -n erp-prestech --tail=50

# Logs do container nginx sidecar
kubectl logs -l app=erp-backend -c nginx -n erp-prestech --tail=50

# Logs do MySQL
kubectl logs -l app=mysql -n erp-prestech --tail=50

# Executar artisan no pod backend
kubectl exec -n erp-prestech deploy/erp-backend -c php-fpm -- php artisan migrate --force
kubectl exec -n erp-prestech deploy/erp-backend -c php-fpm -- php artisan migrate:fresh --seed --force
kubectl exec -n erp-prestech deploy/erp-backend -c php-fpm -- php artisan key:generate --force

# Rollout restart (redeploy sem rebuild)
kubectl rollout restart deployment/erp-backend -n erp-prestech
kubectl rollout status deployment/erp-backend -n erp-prestech

# Aguardar pod ficar pronto
kubectl wait --for=condition=ready pod -l app=erp-backend -n erp-prestech --timeout=180s
kubectl wait --for=condition=ready pod -l app=mysql -n erp-prestech --timeout=120s
kubectl wait --for=condition=ready pod -l app=redis -n erp-prestech --timeout=60s

# Deletar namespace inteiro (DESTRUTIVO)
kubectl delete namespace erp-prestech

# Port-forward para acesso direto ao backend
kubectl port-forward -n erp-prestech svc/erp-backend 8080:80

# Port-forward para acesso direto ao MySQL
kubectl port-forward -n erp-prestech svc/mysql-service 3306:3306
```
