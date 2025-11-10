#!/bin/bash

##############################################################################
# Web Visibility Migration Script - Production Runner
# 
# This script fetches AWS Secrets Manager credentials and runs the 
# web visibility migration CLI script.
#
# Usage:
#   ./scripts/run_web_visibility_migration.sh report
#   ./scripts/run_web_visibility_migration.sh run --dry-run
#   ./scripts/run_web_visibility_migration.sh run
#   ./scripts/run_web_visibility_migration.sh run --batch-size=200
##############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
SECRET_NAME='rds!cluster-2cc47963-bf79-4426-8b61-6aac4f194a15'  # Use single quotes for ! character
AWS_REGION="us-west-1"
DB_HOST_PROD="opuzen-aurora-mysql8-cluster.cluster-c7886s6kkcmk.us-west-1.rds.amazonaws.com"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Web Visibility Migration - Production${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# Check if AWS CLI is installed
if ! command -v aws &> /dev/null; then
    echo -e "${RED}ERROR: AWS CLI is not installed${NC}"
    echo "Install it with: sudo apt-get install -y awscli"
    exit 1
fi

# Check if jq is installed
if ! command -v jq &> /dev/null; then
    echo -e "${RED}ERROR: jq is not installed${NC}"
    echo "Install it with: sudo apt-get install -y jq"
    exit 1
fi

# Fetch credentials from AWS Secrets Manager
echo -e "${YELLOW}Fetching database credentials from AWS Secrets Manager...${NC}"
SECRET_JSON=$(aws secretsmanager get-secret-value \
  --secret-id "$SECRET_NAME" \
  --region "$AWS_REGION" \
  --query SecretString \
  --output text 2>&1)

if [ $? -ne 0 ]; then
    echo -e "${RED}ERROR: Failed to fetch secret from AWS Secrets Manager${NC}"
    echo "$SECRET_JSON"
    exit 1
fi

# Parse credentials (RDS secret only contains username and password)
export DB_HOST="$DB_HOST_PROD"
export DB_USERNAME=$(echo $SECRET_JSON | jq -r '.username')
export DB_PASSWORD=$(echo $SECRET_JSON | jq -r '.password')
export SERVER_NAME=localhost

# Verify credentials were parsed
if [ -z "$DB_HOST" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo -e "${RED}ERROR: Failed to parse credentials from secret${NC}"
    echo "DB_HOST: $DB_HOST"
    echo "DB_USERNAME: $DB_USERNAME"
    echo "DB_PASSWORD: [length=${#DB_PASSWORD}]"
    exit 1
fi

echo -e "${GREEN}✓ Credentials loaded successfully${NC}"
echo "  DB Host: $DB_HOST"
echo "  DB User: $DB_USERNAME"
echo "  Password: ${DB_PASSWORD:0:3}***${DB_PASSWORD: -3}"
echo ""

# Get command and arguments
COMMAND=${1:-report}
shift || true
ARGS="$@"

# Run the migration script
echo -e "${YELLOW}Running: php index.php cli/migrate_web_visibility $COMMAND $ARGS${NC}"
echo ""

php index.php cli/migrate_web_visibility $COMMAND $ARGS

EXIT_CODE=$?

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✓ Script completed successfully${NC}"
else
    echo -e "${RED}✗ Script failed with exit code: $EXIT_CODE${NC}"
fi

exit $EXIT_CODE

