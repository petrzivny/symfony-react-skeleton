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

output "managed_identity_name" {
  value       = azurerm_user_assigned_identity.this.name
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
