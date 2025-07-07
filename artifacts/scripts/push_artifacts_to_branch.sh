#!/bin/bash
set -e

BRANCH_NAME="build-artifacts"
BUILD_DIR="build"

# Clone the repo and switch to the artifacts branch
git clone --depth=1 --branch=main https://github.com/your-org/your-repo.git $BUILD_DIR
cd $BUILD_DIR

# Create or switch to the artifacts branch
git checkout -B $BRANCH_NAME

# Copy build artifacts (e.g., vendor, dist)
cp -r ../vendor ./vendor
cp -r ../web ./web

# Commit and push
git config user.name "CI Bot"
git config user.email "ci-bot@example.com"
git add .
git commit -m "Update build artifacts from Travis CI"
git push origin $BRANCH_NAME --force
