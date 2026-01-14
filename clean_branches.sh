#!/bin/bash

#================================================================
#- IMPLEMENTATION
#-    author    DanyKurosaki
#-    github    https://github.com/danykurosaki
#-    Purpose   This script cleans local branches that do not have
#-             a tracking branch on the remote git server.
#-             Usage: ./clean_branches.sh
#================================================================

# Array of branch names to exclude
excluded_branches=("develop" "master" "main")

echo "Please, enter the name of the remote repository (press Enter to use 'origin'):"
read origin

if [ -z "$origin" ]; then
  origin="origin"
fi

echo "Do you want to list or remove branches? (press Enter for listing or enter 'remove' for removing):"
read action

git fetch "$origin"

for branch in $(git branch --format "%(refname:short)"); do
  # Skip current branch for safety
  if [ "$branch" = "$(git rev-parse --abbrev-ref HEAD)" ]; then
    echo "Skipping current branch $branch."
    continue
  fi

  # Skip excluded branches
  if [[ " ${excluded_branches[*]} " =~ " $branch " ]]; then
    echo "Skipping $branch as it's in the excluded list."
    continue
  fi

  if git branch -r --list "$origin/$branch" > /dev/null; then
    echo "The branch $branch exists in the $origin repository, it will not be removed."
  else
    echo "The branch $branch does not exist in the $origin repository."
    if [ "$action" = "remove" ]; then
      echo "Removing $branch."
      git branch -d "$branch"
    fi
  fi
done
