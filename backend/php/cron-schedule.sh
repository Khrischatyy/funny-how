#!/bin/sh

# Create a directory for cron jobs
mkdir -p /app/cron.d

# Add your cron job here
echo "* * * * * cd /app && php artisan schedule:run --no-ansi >> /app/storage/logs/cron.log 2>&1" > /app/cron.d/laravel-schedule

# Temporary cron job for testing
echo "* * * * * echo 'Cron is working at \$(date)' >> /app/storage/logs/cron_test.log 2>&1" >> /app/cron.d/laravel-schedule

# Give execute permission to the cron job file
chmod 0644 /app/cron.d/laravel-schedule

echo "Starting Supercronic..."

# Start supercronic in the foreground (for Docker)
supercronic /app/cron.d/laravel-schedule