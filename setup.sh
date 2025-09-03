#!/bin/bash

# AdapterCMS Setup Script
# This script ensures all dependencies are correctly installed with stable versions

echo "🚀 Starting AdapterCMS Setup..."

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: Please run this script from the AdapterCMS root directory"
    exit 1
fi

# Check if Docker is running
if ! docker info >/dev/null 2>&1; then
    echo "❌ Error: Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Error: docker-compose is not installed or not in PATH"
    exit 1
fi

echo "📦 Installing Composer dependencies with stable versions..."

# Remove existing composer.lock to ensure fresh dependency resolution
if [ -f "composer.lock" ]; then
    echo "🧹 Removing existing composer.lock for fresh dependency resolution..."
    rm composer.lock
fi

# Install dependencies (production mode)
composer install --no-dev --optimize-autoloader

if [ $? -ne 0 ]; then
    echo "❌ Error: Composer dependency installation failed"
    exit 1
fi

echo "🐳 Setting up Docker containers..."

# Stop any existing containers
docker-compose down

# Start containers in detached mode
docker-compose up -d

if [ $? -ne 0 ]; then
    echo "❌ Error: Failed to start Docker containers"
    exit 1
fi

# Wait for containers to be ready
echo "⏳ Waiting for containers to be ready..."
sleep 10

# Check if the web server is responding
echo "🔍 Testing web server availability..."
for i in {1..30}; do
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "302\|200"; then
        echo "✅ Web server is ready!"
        break
    fi
    
    if [ $i -eq 30 ]; then
        echo "❌ Error: Web server failed to start after 30 attempts"
        echo "📋 Container status:"
        docker-compose ps
        echo "📋 Container logs:"
        docker-compose logs --tail=20 willowcms
        exit 1
    fi
    
    echo "⏳ Attempt $i/30: Waiting for web server..."
    sleep 2
done

# Test key endpoints
echo "🧪 Testing application endpoints..."

# Test main page
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/en | grep -q "200"; then
    echo "✅ Main page: OK"
else
    echo "⚠️  Main page: Warning (might need database setup)"
fi

# Test products page
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/en/products | grep -q "200"; then
    echo "✅ Products page: OK"
else
    echo "❌ Products page: Failed"
fi

# Test product submission page
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/en/products/add | grep -q "200"; then
    echo "✅ Product submission page: OK"
else
    echo "❌ Product submission page: Failed"
fi

echo ""
echo "🎉 AdapterCMS Setup Complete!"
echo ""
echo "📋 Next Steps:"
echo "   1. Open your browser and go to: http://localhost:8080"
echo "   2. Navigate to: http://localhost:8080/en/products to see the product catalog"
echo "   3. Visit: http://localhost:8080/en/products/add to test product submission"
echo ""
echo "🔧 Available Services:"
echo "   • Web Application: http://localhost:8080"
echo "   • PhpMyAdmin: http://localhost:8082 (for database management)"
echo "   • Mailpit: http://localhost:8025 (for email testing)"
echo ""
echo "📚 Documentation:"
echo "   • Product submission form includes reliability scoring integration"
echo "   • Admin panel available at /admin (requires authentication setup)"
echo "   • Public submissions go to 'pending' status and require admin approval"
echo ""
echo "🛠️  Development Commands:"
echo "   • Stop containers: docker-compose down"
echo "   • View logs: docker-compose logs -f willowcms"
echo "   • Restart containers: docker-compose restart"

# Optional: Show container status
echo ""
echo "📊 Current Container Status:"
docker-compose ps
