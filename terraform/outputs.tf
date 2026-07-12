# output "container_app_fqdn" {
#   description = "Public FQDN for the Container App ingress."
#   value       = azurerm_container_app.this.latest_revision_fqdn
# }

output "kv_azure-postgresql-clientid" {
  description = "Client ID of the user-assigned managed identity used for ACR pull and for azure-postgresql-clientid"
  value       = azurerm_user_assigned_identity.this.client_id
}

output "kv_database-user" {
  description = "This is used for pgaadauth_create_principal and for database-user."
  value       = azurerm_user_assigned_identity.this.name
}
