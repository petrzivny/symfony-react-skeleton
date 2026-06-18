locals {
  name_prefix         = "${var.project_name}-${var.environment}"
  resource_group_name = coalesce(var.resource_group_name, "${local.name_prefix}-aca-rg")

  nginx_image = "${data.azurerm_container_registry.this.login_server}/${var.nginx_image_repo}:${var.nginx_image_tag}"
  php_image   = "${data.azurerm_container_registry.this.login_server}/${var.php_image_repo}:${var.php_image_tag}"

  default_tags = {
    project     = var.project_name
    environment = var.environment
    region      = var.location
    managed_by  = "terraform"
  }

  tags = merge(local.default_tags, var.tags)
}

resource "azurerm_resource_group" "this" {
  name     = local.resource_group_name
  location = var.location
  tags     = local.tags
}

resource "azurerm_container_app_environment" "this" {
  name                       = "${local.name_prefix}-cae"
  location                   = azurerm_resource_group.this.location
  resource_group_name        = azurerm_resource_group.this.name
  log_analytics_workspace_id = data.azurerm_log_analytics_workspace.this.id
  tags                       = local.tags
}

resource "azurerm_user_assigned_identity" "this" {
  name                = "${local.name_prefix}-identity"
  location            = azurerm_resource_group.this.location
  resource_group_name = azurerm_resource_group.this.name
  tags                = local.tags
}

resource "azurerm_role_assignment" "acr_pull" {
  count = var.create_acr_pull_role_assignment ? 1 : 0

  scope                = data.azurerm_container_registry.this.id
  role_definition_name = "AcrPull"
  principal_id         = azurerm_user_assigned_identity.this.principal_id
}

resource "azurerm_container_app" "this" {
  name                         = "${local.name_prefix}-app"
  container_app_environment_id = azurerm_container_app_environment.this.id
  resource_group_name          = azurerm_resource_group.this.name
  revision_mode                = "Single"
  tags                         = local.tags

  depends_on = [azurerm_role_assignment.acr_pull]

  identity {
    type         = "UserAssigned"
    identity_ids = [azurerm_user_assigned_identity.this.id]
  }

  registry {
    server   = data.azurerm_container_registry.this.login_server
    identity = azurerm_user_assigned_identity.this.id
  }

  # Future: store DATABASE_URL in Key Vault and reference it here.
  # secret {
  #   name                = "database-url"
  #   key_vault_secret_id = azurerm_key_vault_secret.database_url.versionless_id
  #   identity            = azurerm_user_assigned_identity.this.id
  # }

  ingress {
    external_enabled = true
    target_port      = 8080
    transport        = "auto"

    traffic_weight {
      latest_revision = true
      percentage      = 100
    }
  }

  template {
    min_replicas = var.min_replicas
    max_replicas = var.max_replicas

    container {
      name   = "nginx"
      image  = local.nginx_image
      cpu    = var.nginx_cpu
      memory = var.nginx_memory

      env {
        name  = "PHP_FPM_HOST"
        value = "127.0.0.1"
      }

      liveness_probe {
        transport               = "HTTP"
        port                    = 8080
        path                    = "/nginx-health"
        initial_delay           = 15
        interval_seconds        = 60
        timeout                 = 1
        failure_count_threshold = 3
      }

      readiness_probe {
        transport               = "HTTP"
        port                    = 8080
        path                    = "/nginx-health"
        initial_delay           = 2
        interval_seconds        = 10
        timeout                 = 1
        failure_count_threshold = 3
        success_count_threshold = 1
      }
    }

    container {
      name   = "php"
      image  = local.php_image
      cpu    = var.php_cpu
      memory = var.php_memory

      # Future: wire DATABASE_URL from a secret (Key Vault recommended).
      # env {
      #   name        = "DATABASE_URL"
      #   secret_name = "database-url"
      # }
    }
  }
}
