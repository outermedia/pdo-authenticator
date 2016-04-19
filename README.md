# pdo-authenticator
General php authenticator based on PDO to check logins.

##Installation

At first install or download composer.phar to your computer. Follow the instructions provided by [getcomposer.org](https://getcomposer.org/download/).

### Step 1: Download the project

To download the latest distribution without tests, run

``` bash
php ~/php/composer.phar --prefer-dist require outermedia/pdo-authenticator
```
This creates files in your current working directory:

```
├── composer.json
├── composer.lock
└── vendor
    │   ...
    └── outermedia
        └── pdo-authenticator
            ├── LICENSE
            ├── README.md
            ├── composer.json
            ├── composer.lock
            └── src
                └── main
                    └── webapp
                        ├── Om
                        │   └── Pdo
                        │       └── Authenticator
                        │           ├── DatabaseConfiguration.php
                        │           ├── DatabaseQueryBuilder.php
                        │           ├── PdoAuthenticator.php
                        │           └── RequestHandler.php
                        ├── dbconf.php.template
                        └── index.php

```

###Step 2: Deploy the files

Now copy the files to your destination directory ($DEST)
```
cp -r vendor/outermedia/pdo-authenticator/src/main/webapp/* $DEST
```

### Step 3: Rename the database settings template

Rename the file dbconf.php.template:
```
mv $DEST/dbconf.php.template dbconf.php
```

### Step 4: Set your database options

Edit dbconf.php.

Options are:

- __pdoUrl__ - a PDO connection URL e.g. for a local mysql and a database dbname1 "mysql:host=localhost;dbname=dbname1"
- __dbUser__ - the username used for the database connection
- __dbPassword__ - the password used for the database connection
- __table__ - the database table name which holds the user information
- __usernameColumn__ - the column name which stores the username (of __table__)
- __passwordColumn__ - the column name which stores a user's password (of __table__)

### Step 5: Test you installation

Two POST actions are supported:

```
http://<host>/<path to your installation>/index.html
```
