output "container_app_fqdn" {
  description = "Public FQDN for the Container App ingress."
  value       = azurerm_container_app.this.latest_revision_fqdn
}

output "acr_login_server" {
  description = "Login server of the referenced Azure Container Registry."
  value       = data.azurerm_container_registry.this.login_server
}

output "resource_group_name" {
  description = "Name of the resource group created for this deployment."
  value       = azurerm_resource_group.this.name
}

output "managed_identity_client_id" {
  description = "Client ID of the user-assigned managed identity used for ACR pull."
  value       = azurerm_user_assigned_identity.this.client_id
}

output "key_vault_name" {
  description = "Name of the Azure Key Vault storing database secrets."
  value       = azurerm_key_vault.this.name
}

output "key_vault_uri" {
  description = "URI of the Azure Key Vault."
  value       = azurerm_key_vault.this.vault_uri
}

output "database_secret_names" {
  description = "Database secret names created in Key Vault (set values manually in Azure)."
  value       = sort(var.database_secret_names)
}

output "postgresql_server_fqdn" {
  description = "FQDN of the existing PostgreSQL flexible server."
  value       = data.azurerm_postgresql_flexible_server.this.fqdn
}

output "database_name" {
  description = "Name of the application database created on the shared PostgreSQL flexible server."
  value       = azurerm_postgresql_flexible_server_database.this.name
}

output "database_user" {
  description = "PostgreSQL login scoped to the application database."
  value       = postgresql_role.app.name
}
