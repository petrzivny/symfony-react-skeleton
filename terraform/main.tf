locals {
  name_prefix         = "${var.project_name}-${var.environment}"
  resource_group_name = coalesce(var.resource_group_name, "${local.name_prefix}-rg")

  nginx_image = "${data.azurerm_container_registry.this.login_server}/${var.nginx_image_repo}:${var.nginx_image_tag}"
  php_image   = "${data.azurerm_container_registry.this.login_server}/${var.php_image_repo}:${var.php_image_tag}"

  # Storage account names: 3–24 lowercase alphanumeric, globally unique.
  storage_account_name = coalesce(
    var.storage_account_name,
    substr(replace(lower("${var.project_name}${var.environment}st"), "-", ""), 0, 24)
  )

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
