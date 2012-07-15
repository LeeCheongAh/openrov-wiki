#!/bin/bash

# MySQL Variables
USER='root'
PASS=''
DB='openrov_wiki'

# Location of the MediaWiki installation
DATA='/srv/openrov-wiki/db-backups'

# Filename that mysqldump will output
file_name=$DB\_`date +'%Y-%m-%d-%H%m'`.sql.gz

# Begin the MySQL Dump Operation
mysqldump -u$USER $DB | gzip > $DATA/$file_name

