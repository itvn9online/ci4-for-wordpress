#!/bin/sh

pull_code(){
git checkout main
git pull
git pull origin $1
git merge $1
git push
}
pull_code "daidq"
