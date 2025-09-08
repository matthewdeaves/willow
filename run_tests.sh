#!/bin/bash

# Run PHPUnit tests with filtering options as per project rules

echo "==============================================="
echo "WillowCMS PHPUnit Test Runner"
echo "==============================================="
echo ""

# Parse command line arguments
TEST_SCOPE="${1:-all}"
FILTER="${2:-}"

case "$TEST_SCOPE" in
    controller|controllers)
        echo "Running Controller tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit --filter Controller tests/TestCase/Controller/
        ;;
    model|models|table|tables)
        echo "Running Model/Table tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit --filter Table tests/TestCase/Model/Table/
        ;;
    entity|entities)
        echo "Running Entity tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit --filter Entity tests/TestCase/Model/Entity/
        ;;
    job|jobs)
        echo "Running Job tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit --filter Job tests/TestCase/Job/
        ;;
    behavior|behaviors)
        echo "Running Behavior tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Model/Behavior/
        ;;
    middleware)
        echo "Running Middleware tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Middleware/
        ;;
    smoke)
        echo "Running Smoke tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/Smoke/
        ;;
    product|products)
        echo "Running Product-related tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit --filter "Product" tests/TestCase/
        ;;
    all)
        echo "Running ALL tests..."
        docker compose exec -T willowcms php vendor/bin/phpunit tests/TestCase/
        ;;
    *)
        echo "Invalid scope: $TEST_SCOPE"
        echo ""
        echo "Usage: $0 [scope] [filter]"
        echo ""
        echo "Available scopes:"
        echo "  controller  - Run controller tests"
        echo "  model       - Run model/table tests"
        echo "  entity      - Run entity tests"
        echo "  job         - Run job tests"
        echo "  behavior    - Run behavior tests"
        echo "  middleware  - Run middleware tests"
        echo "  smoke       - Run smoke tests"
        echo "  product     - Run product-related tests"
        echo "  all         - Run all tests (default)"
        echo ""
        echo "Example: $0 controller testView"
        exit 1
        ;;
esac
