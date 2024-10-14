#!/bin/bash
set -e

echo "Waiting for Postgres to be available..."
until psql -h "$DB_HOST" -U "$DB_USERNAME" -c '\q'; do
  >&2 echo "Postgres is unavailable - sleeping"
  sleep 1
done

DB_EXISTS=$(psql -h "$DB_HOST" -U "$DB_USERNAME" -tc "SELECT 1 FROM pg_database WHERE datname = '$DB_DATABASE_TESTING'" | grep -q 1 || echo 0)

if [ "$DB_EXISTS" -eq 0 ]; then
  echo "Creating database $DB_DATABASE_TESTING..."
  createdb -h "$DB_HOST" -U "$DB_USERNAME" "$DB_DATABASE_TESTING"
else
  echo "Database $DB_DATABASE_TESTING already exists"
fi

echo "Running migrations..."
php artisan migrate --force --env=testing

exec "$@"
