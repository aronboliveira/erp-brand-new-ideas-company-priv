provider "azurerm" {
  features {}
}

resource "azurerm_resource_group" "erp_rg" {
  name     = "erp-resources"
  location = "East US"
}

resource "azurerm_container_registry" "acr" {
  name                = "erpacrUnique123" # Must be globally unique
  resource_group_name = azurerm_resource_group.erp_rg.name
  location            = azurerm_resource_group.erp_rg.location
  sku                 = "Standard"
  admin_enabled       = true
}

resource "azurerm_kubernetes_cluster" "aks" {
  name                = "erp-aks"
  location            = azurerm_resource_group.erp_rg.location
  resource_group_name = azurerm_resource_group.erp_rg.name
  dns_prefix          = "erpaks"

  default_node_pool {
    name       = "default"
    node_count = 2
    vm_size    = "Standard_D2_v2"
  }

  identity {
    type = "SystemAssigned"
  }

  tags = {
    Environment = "Production"
  }
}

# Output ACR login server
output "acr_login_server" {
  value = azurerm_container_registry.acr.login_server
}

# Output AKS kube config
output "kube_config" {
  value     = azurerm_kubernetes_cluster.aks.kube_config_raw
  sensitive = true
}
