resource "azurerm_container_app" "this" {
  name                         = "${local.name_prefix}-ca"
  container_app_environment_id = azurerm_container_app_environment.this.id
  resource_group_name          = azurerm_resource_group.this.name
  revision_mode                = "Single"
  tags                         = local.tags

  depends_on = [
    azurerm_role_assignment.acr_pull,
    azurerm_role_assignment.identity_storage_blob_data_contributor,
    azurerm_role_assignment.identity_storage_blob_delegator,
  ]

  identity {
    type         = "UserAssigned"
    identity_ids = [azurerm_user_assigned_identity.this.id]
  }

  registry {
    server   = data.azurerm_container_registry.this.login_server
    identity = azurerm_user_assigned_identity.this.id
  }

  dynamic "secret" {
    for_each = azurerm_key_vault_secret.database
    content {
      name                = secret.key
      key_vault_secret_id = secret.value.versionless_id
      identity            = azurerm_user_assigned_identity.this.id
    }
  }

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

      env {
        name  = "AZURE_STORAGE_ACCOUNT_NAME"
        value = azurerm_storage_account.this.name
      }

      env {
        name  = "AZURE_STORAGE_CONTAINER_NAME"
        value = azurerm_storage_container.listing_thumbnails.name
      }

      volume_mounts {
        name = "secrets"
        path = "/mnt/secrets"
      }
    }

    volume {
      name         = "secrets"
      storage_type = "Secret"
    }
  }
}
