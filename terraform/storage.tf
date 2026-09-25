resource "azurerm_storage_account" "this" {
  name                            = local.storage_account_name
  resource_group_name             = azurerm_resource_group.this.name
  location                        = azurerm_resource_group.this.location
  account_tier                    = "Standard"
  account_replication_type        = "LRS"
  account_kind                    = "StorageV2"
  min_tls_version                 = "TLS1_2"
  https_traffic_only_enabled      = true
  allow_nested_items_to_be_public = false
  # azurerm polls Blob with the account key after create. Disabling keys here
  # makes that wait 403 (KeyBasedAuthenticationNotPermitted). The app never
  # uses the key (managed identity + User Delegation SAS). Portal/SDK still
  # default to Azure AD via default_to_oauth_authentication.
  shared_access_key_enabled       = true
  default_to_oauth_authentication = true
  public_network_access_enabled   = true
  tags                            = local.tags
}

resource "azurerm_storage_container" "listing_thumbnails" {
  name                  = "listing-thumbnails"
  storage_account_id    = azurerm_storage_account.this.id
  container_access_type = "private"
}

resource "azurerm_role_assignment" "identity_storage_blob_data_contributor" {
  scope                = azurerm_storage_account.this.id
  role_definition_name = "Storage Blob Data Contributor"
  principal_id         = azurerm_user_assigned_identity.this.principal_id
}

resource "azurerm_role_assignment" "identity_storage_blob_delegator" {
  scope                = azurerm_storage_account.this.id
  role_definition_name = "Storage Blob Delegator"
  principal_id         = azurerm_user_assigned_identity.this.principal_id
}
