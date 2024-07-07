#!/bin/sh

git checkout daidq
git pull
git pull origin main
git merge main
git push
