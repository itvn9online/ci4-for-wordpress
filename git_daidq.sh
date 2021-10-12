#!/bin/sh

pull_code(){
git checkout $1
git pull
git pull origin main
git merge main
git push
}
pull_code "daidq"
