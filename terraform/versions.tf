terraform {
  required_version = ">= 1.5"

  # Uncomment and fill in to store state remotely (no storage account is created by this module).
  # backend "azurerm" {
  #   resource_group_name  = "terraform-state-rg"
  #   storage_account_name = "tfstateaccount"
  #   container_name       = "tfstate"
  #   key                  = "pricemonitor-dev.terraform.tfstate"
  # }

  required_providers {
    azurerm = {
      source  = "hashicorp/azurerm"
      version = "~> 4.0"
    }
  }
}

provider "azurerm" {
  features {}

  subscription_id = var.subscription_id

  resource_provider_registrations = "core"
  resource_providers_to_register = [
    "Microsoft.App",
  ]
}
