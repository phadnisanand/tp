#!/bin/bash
set -e

# Authenticate with Acquia CLI
acli auth:login --key="$ACQUIA_API_KEY" --secret="$ACQUIA_API_SECRET"

# Push sanitized build to Acquia Cloud
acli push:artifact --dir=. \
  --destination-git-urls=git@github.com:your-org/your-repo.git \
  --destination-git-branch=build-artifacts \
  --no-sanitize
