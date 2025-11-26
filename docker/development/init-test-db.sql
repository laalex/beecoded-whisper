-- Create test database for PHPUnit tests
-- This runs automatically when the postgres container is first initialized

CREATE DATABASE whisper_test;
GRANT ALL PRIVILEGES ON DATABASE whisper_test TO whisper;
