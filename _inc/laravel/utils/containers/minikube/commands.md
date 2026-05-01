# Minikube — Comandos utilizados

> Registro de comandos minikube executados na gestão do cluster local ERP Brand New Ideas Company.
> Atualizado conforme novos comandos são utilizados.

## 20260416

```bash
# Iniciar minikube com perfil padrão (driver docker, 4 CPUs, 4GB RAM)
minikube start --cpus=4 --memory=4096 --driver=docker --addons=ingress,metrics-server,dashboard

# Verificar status do cluster
minikube status

# Habilitar addon ingress (necessário para roteamento HTTP)
minikube addons enable ingress

# Habilitar addon metrics-server (necessário para HPA / monitoramento)
minikube addons enable metrics-server

# Obter IP do minikube (para configurar /etc/hosts)
minikube ip

# Configurar Docker CLI para usar o daemon do minikube
eval $(minikube docker-env)

# Reverter Docker CLI para daemon local
eval $(minikube docker-env -u)

# Abrir dashboard do Kubernetes
minikube dashboard

# Criar tunnel para acessar services LoadBalancer/Ingress
minikube tunnel

# Parar cluster
minikube stop

# Deletar cluster completamente
minikube delete
```
