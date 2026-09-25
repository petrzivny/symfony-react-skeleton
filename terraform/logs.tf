resource "azurerm_monitor_data_collection_rule" "dcr1" {
  data_collection_endpoint_id = null
  description                 = null
  kind                        = "WorkspaceTransforms"
  location                    = "westeurope"
  name                        = "${local.name_prefix}-dcr"
  resource_group_name         = local.resource_group_name
  tags                        = local.tags

  data_flow {
    built_in_transform = null
    destinations       = [
      "{id}",
    ]
    output_stream      = null
    streams            = [
      "Microsoft-Table-AppServiceConsoleLogs",
    ]
    transform_kql      = <<-EOT
            source
            | extend Context = parse_json(ResultDescription)
            | extend Level_CF = case(isnotempty(Context['level']), tolong(Context['level']), isnotempty(Context['httpRequest']['status']), tolong(Context['httpRequest']['status']), 0)
            | extend Severity_CF = Context['level_name']
            | extend Message_CF = Context['message']
            | extend Channel_CF = Context['channel']
            | extend Source_CF = case(isnotempty(Context['httpRequest']), "php_access_log", ResultDescription has "- -", "nginx", ResultDescription has "open()", "nginx", isnotempty(Channel_CF), "php", "")
            | project-away Context, Level
        EOT
  }

  data_sources {
  }

  destinations {
    log_analytics {
      name                  = "{id}"
      workspace_resource_id = data.azurerm_log_analytics_workspace.this.id
    }
  }
}

# Parses Monolog JSON from Container App console logs (Log_s) into readable columns.
# Note: a Log Analytics workspace can have only one active WorkspaceTransforms DCR.
# my-company-law-01 currently points at dcr1 — this rule is created but not linked.
# To activate it without changing dcr1, add this table's transform to dcr1 instead,
# or switch the workspace defaultDataCollectionRuleResourceId to this rule (then
# dcr1's AppServiceConsoleLogs transform stops applying until moved here).
resource "azurerm_monitor_data_collection_rule" "container_app_console" {
  name                = "${local.name_prefix}-console-logs"
  resource_group_name = azurerm_resource_group.this.name
  location            = azurerm_resource_group.this.location
  kind                = "WorkspaceTransforms"
  description         = "Parse Monolog JSON from Container App console logs into Channel/Level/Message columns."
  tags                = local.tags

  destinations {
    log_analytics {
      name                  = "{id}"
      workspace_resource_id = data.azurerm_log_analytics_workspace.this.id
    }
  }

  data_flow {
    streams       = ["Microsoft-Table-ContainerAppConsoleLogs_CL"]
    destinations  = ["{id}"]
    transform_kql = <<-EOT
      source
      | extend Parsed = parse_json(Log_s)
      | extend Message_CF = Parsed['message']
      | project-away Parsed
    EOT
  }
}


# | extend Channel_CF = tostring(Parsed.channel)
# | extend Level_CF = toint(Parsed.level)
# | extend Severity_CF = tostring(Parsed.level_name)
# | extend Message_CF = coalesce(tostring(Parsed.message), Log_s)
# | extend Context_CF = tostring(Parsed.context)
# | project-away Parsed
