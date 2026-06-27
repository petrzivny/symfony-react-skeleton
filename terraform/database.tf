locals {
  database_name = coalesce(var.database_name, replace("${var.project_name}_${var.environment}", "-", "_"))
  database_user = coalesce(var.database_user, "${local.database_name}_user")
}

provider "postgresql" {
  alias = "admin"

  host            = data.azurerm_postgresql_flexible_server.this.fqdn
  port            = 5432
  database        = "postgres"
  username        = var.postgresql_admin_username
  password        = var.postgresql_admin_password
  sslmode         = "require"
  connect_timeout = 15
  superuser       = false
}

provider "postgresql" {
  alias = "app_db"

  host            = data.azurerm_postgresql_flexible_server.this.fqdn
  port            = 5432
  database        = azurerm_postgresql_flexible_server_database.this.name
  username        = var.postgresql_admin_username
  password        = var.postgresql_admin_password
  sslmode         = "require"
  connect_timeout = 15
  superuser       = false
}

resource "azurerm_postgresql_flexible_server_database" "this" {
  name      = local.database_name
  server_id = data.azurerm_postgresql_flexible_server.this.id
  charset   = "UTF8"
  collation = "en_US.utf8"
}

resource "postgresql_role" "app" {
  provider = postgresql.admin

  name            = local.database_user
  login           = true
  password        = var.database_user_password
  create_database = false
  create_role     = false

  depends_on = [azurerm_postgresql_flexible_server_database.this]
}

resource "postgresql_grant" "app_database" {
  provider = postgresql.admin

  database    = azurerm_postgresql_flexible_server_database.this.name
  role        = postgresql_role.app.name
  object_type = "database"
  privileges  = ["CONNECT", "CREATE", "TEMPORARY"]

  depends_on = [postgresql_role.app]
}

resource "postgresql_grant" "app_schema" {
  provider = postgresql.app_db

  database    = azurerm_postgresql_flexible_server_database.this.name
  role        = postgresql_role.app.name
  schema      = "public"
  object_type = "schema"
  privileges  = ["CREATE", "USAGE"]

  depends_on = [postgresql_grant.app_database]
}
