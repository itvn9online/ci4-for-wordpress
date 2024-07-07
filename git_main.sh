#!/bin/sh

git checkout main
git pull
git pull origin daidq
git merge daidq
git push
