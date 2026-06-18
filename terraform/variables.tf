variable "subscription_id" {
  description = "Azure subscription ID for all resources."
  type        = string
}

variable "project_name" {
  description = "Project name used in resource naming."
  type        = string
  default     = "symfony-react-skeleton"
}

variable "environment" {
  description = "Environment name (e.g. dev, stg)."
  type        = string
  default     = "dev"
}

variable "location" {
  description = "Azure region for created resources."
  type        = string
  default     = "westeurope"
}

variable "resource_group_name" {
  description = "Name of the resource group for this deployment. Defaults to \"<project>-<env>-aca-rg\"."
  type        = string
  default     = null
}

variable "shared_resource_group_name" {
  description = "Resource group containing the existing Log Analytics workspace and ACR."
  type        = string
}

variable "log_analytics_workspace_name" {
  description = "Existing Log Analytics workspace name (referenced, not created)."
  type        = string
}

variable "acr_name" {
  description = "Existing Azure Container Registry name (referenced, not created)."
  type        = string
}

variable "create_acr_pull_role_assignment" {
  description = "Create AcrPull role assignment on the shared ACR for the managed identity. Set to false if an admin assigns the role out-of-band."
  type        = bool
  default     = true
}

variable "nginx_image_repo" {
  description = "ACR repository path for the nginx image (without registry host or tag)."
  type        = string
  default     = "symfony-react-skeleton/nginx"
}

variable "nginx_image_tag" {
  description = "Tag for the nginx image."
  type        = string
  default     = "latest"
}

variable "php_image_repo" {
  description = "ACR repository path for the php-fpm image (without registry host or tag)."
  type        = string
  default     = "symfony-react-skeleton/php"
}

variable "php_image_tag" {
  description = "Tag for the php-fpm image."
  type        = string
  default     = "latest"
}

variable "nginx_cpu" {
  description = "CPU cores for the nginx container."
  type        = number
  default     = 0.25
}

variable "nginx_memory" {
  description = "Memory for the nginx container."
  type        = string
  default     = "0.5Gi"
}

variable "php_cpu" {
  description = "CPU cores for the php-fpm container."
  type        = number
  default     = 0.25
}

variable "php_memory" {
  description = "Memory for the php-fpm container."
  type        = string
  default     = "0.5Gi"
}

variable "min_replicas" {
  description = "Minimum number of replicas (0 allows scale-to-zero)."
  type        = number
  default     = 0
}

variable "max_replicas" {
  description = "Maximum number of replicas."
  type        = number
  default     = 1
}

variable "tags" {
  description = "Tags applied to created resources."
  type        = map(string)
  default     = {}
}
