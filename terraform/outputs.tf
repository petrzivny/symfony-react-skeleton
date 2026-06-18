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
