@echo off
echo -----------------------------------------
echo  Git Initial Push Helper Script
echo -----------------------------------------

set /p repoUrl=Enter your GitHub repository URL: 

echo Initializing Git...
git init

echo Adding files...
git add .

echo Committing...
git commit -m "Initial commit"

echo Renaming branch to main...
git branch -M main

echo Adding remote origin...
git remote add origin %repoUrl%

echo Pushing to GitHub...
git push -u origin main

echo -----------------------------------------
echo  Done! Your project is now on GitHub.
echo -----------------------------------------
pause