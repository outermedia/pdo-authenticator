# pdo-authenticator
General php authenticator based on PDO to check logins.

##Installation

At first install or download composer.phar to your computer. Follow the instructions provided by [getcomposer.org](https://getcomposer.org/download/).

### Step 1: Download the project

Create an intermediate directory, cd into it and download the latest distribution without tests:

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

Hint: If you want to run the phpunit tests, additionally run (creates new directoy pdo-authenticator/):
```
php ~/php/composer.phar --prefer-source create-project outermedia/pdo-authenticator
pushd pdo-authenticator && vendor/bin/phpunit src/test/php/ && popd
```
Note: The dbunit tests require sqlite3.

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

a) Get a user's salt ("user1"): Encode your form parameters with the specified charset!
```
curl -X POST -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" \
    --data 'action=getsalt&login=user1' http://localhost/pdo-auth/index.php
```
should return something like:
```
{"charset":"latin1","result":true,"salt":"$1$rasmusl1"}
```
The charset used by the database table, the salt and a success flag ("result").

b) Check a user's login: pwd is the calculated hash.
```
curl -X POST -H "Content-Type: application/x-www-form-urlencoded; charset=UTF-8" \
    --data 'action=login&login=user1&pwd=$1$rasmusl1$2ASuKCrDVFQspP8.yIzVl.' \
    http://localhost/pdo-auth/index.php
```
The expected answer is e.g.
```
{"charset":"latin1","result":true}
```
The flag "result" indicates the success.
