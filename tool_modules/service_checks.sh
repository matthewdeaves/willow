#!/bin/bash

# Function to check if required Docker services are running
check_docker_services() {
    local willowcms_defined=false mysql_defined=false willowcms_running=false mysql_running=false
    
    # Check if services are defined in docker-compose.yml (even if not running)
    if docker compose config --services | grep -qxF "willowcms"; then willowcms_defined=true; fi
    if docker compose config --services | grep -qxF "mysql"; then mysql_defined=true; fi

    # Check if running, only if defined
    if $willowcms_defined && docker compose ps --services --filter "status=running" | grep -qxF "willowcms"; then willowcms_running=true; fi
    if $mysql_defined && docker compose ps --services --filter "status=running" | grep -qxF "mysql"; then mysql_running=true; fi

    if ! $willowcms_defined && ! $mysql_defined; then echo "Error: Critical Docker services ('willowcms', 'mysql') not defined in compose configuration."; return 1; fi

    # Contextual checks based on the chosen command
    case "$CURRENT_CHOICE_FOR_SERVICE_CHECK" in
        1|2|5|6|7|8|9|10|11|12) # Operations requiring willowcms
            if ! $willowcms_defined; then echo "Error: 'willowcms' service not defined in compose configuration."; return 1; fi
            if ! $willowcms_running; then echo "Error: 'willowcms' service is not running. Please start it."; return 1; fi ;;
        3|4) # Operations requiring mysql
            if ! $mysql_defined; then echo "Error: 'mysql' service not defined in compose configuration."; return 1; fi
            if ! $mysql_running; then echo "Error: 'mysql' service is not running. Please start it."; return 1; fi
            # For option 4, willowcms is also needed for cache clear.
            if [ "$CURRENT_CHOICE_FOR_SERVICE_CHECK" = "4" ]; then
                 if ! $willowcms_defined; then echo "Error: 'willowcms' service not defined in compose configuration (needed for cache clear)."; return 1; fi
                 if ! $willowcms_running; then echo "Error: 'willowcms' service is not running (needed for cache clear). Please start it."; return 1; fi
            fi
            ;;
    esac
    return 0
}