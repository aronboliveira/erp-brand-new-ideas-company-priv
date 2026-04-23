# CLI Commands — 2026-04-16

# Sessão: Configuração K8s local (minikube/kubectl), CI/CD GitHub Actions

## rsync (backup)

rsync -a --exclude='{.git,node_modules,vendor,.backup}' \_inc/laravel/k8s/ .backup/k8s_20260416/
rsync -a \_inc/laravel/Dockerfile .backup/Dockerfile.20260416.bak
rsync -a \_inc/laravel/docker-compose.yml .backup/docker-compose.yml.20260416.bak
rsync -a \_inc/laravel/azure-pipelines.yml .backup/azure-pipelines.yml.20260416.bak

## git

git add .gitignore \_inc/laravel/{.gitignore,Dockerfile,azure-pipelines.yml,composer.json,k8s/,scripts/k8s-deploy.sh} .github/workflows/ci.yml \_inc/laravel/utils/containers/
git commit -m "infra: K8s local deploy (minikube/kubectl), GitHub Actions CI/CD, Azure triggers desativados"

## test suites (resultados em \_inc/laravel/utils/logs/20260416/test-results.md)

php vendor/bin/phpstan analyse --memory-limit=2G --no-progress # 2 erros (OOM com 1G, reexecutado com 2G)
php -d memory_limit=2G vendor/bin/phpunit --no-coverage --stop-on-failure # 12832 tests, 12134 errors (MySQL offline)
npx eslint "resources/**/\*.{js,cjs,mjs}" "public/**/\*.js" --no-error-on-unmatched-pattern # 10035 problemas
npx tsc --noEmit # ~15 erros TS7006/TS7005
npx jest --config jest.config.cjs --verbose # 724 tests ALL PASSED
cd tests/frontend/js && npx jest --verbose # 949 tests, 5 failed, 944 passed
npx playwright test --reporter=list # 9 passed, 3 skipped
python3 -m flake8 tests/python/ resources/python/ --max-line-length=120 # 29 issues
python3 -m mypy resources/python/ --ignore-missing-imports # dir não encontrado
python3 -m pytest tests/python/ -v --tb=short # 268 passed, 171 skipped

## chmod

chmod +x scripts/k8s-deploy.sh

## mkdir

mkdir -p .github/workflows
mkdir -p \_inc/laravel/utils/containers/{minikube,kubectl,docker}
mkdir -p .backup
