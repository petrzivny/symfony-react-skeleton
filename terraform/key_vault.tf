locals {
  # Key Vault names must be globally unique, 3–24 alphanumeric characters.
  key_vault_name = coalesce(
    var.key_vault_name,
    substr(replace(lower("${var.project_name}${var.environment}kv"), "-", ""), 0, 24)
  )
}

resource "azurerm_key_vault" "this" {
  name                       = local.key_vault_name
  location                   = azurerm_resource_group.this.location
  resource_group_name        = azurerm_resource_group.this.name
  tenant_id                  = data.azurerm_client_config.current.tenant_id
  sku_name                   = "standard"
  soft_delete_retention_days = 7
  purge_protection_enabled   = false
  rbac_authorization_enabled = true
  tags                       = local.tags
}

resource "azurerm_role_assignment" "terraform_kv_secrets_officer" {
  scope                = azurerm_key_vault.this.id
  role_definition_name = "Key Vault Secrets Officer"
  principal_id         = data.azurerm_client_config.current.object_id
}

resource "azurerm_role_assignment" "identity_kv_secrets_user" {
  scope                = azurerm_key_vault.this.id
  role_definition_name = "Key Vault Secrets User"
  principal_id         = azurerm_user_assigned_identity.this.principal_id
}

# Placeholder values are required on create; set real values in the Azure portal or CLI.
# lifecycle.ignore_changes keeps Terraform from overwriting manual updates.
resource "azurerm_key_vault_secret" "database" {
  for_each = toset(var.database_secret_names)

  name         = each.key
  value        = "initial-value_change-manually-in-azure-portal"
  key_vault_id = azurerm_key_vault.this.id

  lifecycle {
    ignore_changes = [value]
  }

  depends_on = [
    azurerm_role_assignment.terraform_kv_secrets_officer,
  ]
}
