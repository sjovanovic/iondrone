IonDrone
========

## Overview

IonDrone is a simple web based code editor for GitHub. It uses the CORS GitHub API and [CodeMirror](http://codemirror.net/) editor. It is mostly web app with the exception of the authentication which is done server side (php).

## Install

- clone the repo
- [register new GitHub application](https://github.com/settings/applications) and make sure that url and callback url point to your domain (in order for CORS to work)
- edit application/configuration/main.php and add your GitHub app's client ID and secret ID
- open your IonDrone enabled site and login with a GitHub account

## Features

- authenticate with your GitHub account
- create GitHub repos
- browse repos
- add files to repos
- edit files in CodeMirror web based editor
- save files (commit and push) back to GitHub repos