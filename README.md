GooglePosta
===========

A Laposta / Google Contacts (GC) API bridge


Dependencies
------------

* Composer
* PHP >= 5.3.3
* PHP JSON
* PHP CURL
* PHP Mcrypt


Install
-------

#### Step 1 - Get the source and install source dependencies
```sh
git clone https://github.com/laposta/googleposta.git googleposta \
  && cd googleposta \
  && composer install --no-dev --prefer-dist --optimize-autoloader
```

#### Step 2 - Ensure that data directory is writable
```sh
chown apache:apache data
# or
chmod ugo+w data
```
#### Step 3 - Create your virtual host on apache or nginx
Use the **public** directory as you virtual hosts web root. SSL is highly recommended though not a requirement for this application.


Configure
---------

The [config.php](/config.php) file contains default values for the application to be run without a customized configuration. Only production values should be added to this file. A **config.local.php** file can be created alongside this file where values can be overridden.

Use the following command to safely create a copy for your application instance:
```sh
cp -n config.php config.local.php
```
or for a documentation free copy use:
```sh
php -r "file_exists('config.local.php') || \
file_put_contents('config.local.php', \"<?php\n\nreturn \".var_export(require 'config.php', true).\";\n\");"
```

Use your preferred editor to modify the config.local.php file for your specific requirements.


Use
---

### Connecting a laposta account to GC

To enable a new connection between Laposta and GC the following options must be sent to https://{googleposta_host}/authority/ using the POST method.

* **email** - Laposta account holder email
* **lapostaApiToken** - Laposta API key
* **returnUrl** (optional) - Return URL

An example using an HTML form:

```html
<form method="post" action="https://{googleposta_host}/authority/">
    <input type="hidden" name="email" value="{laposta_login}" />
    <input type="hidden" name="lapostaApiToken" value="{laposta_api_key}" />
    <input type="hidden" name="returnUrl" value="{return_url}" />

    <input type="submit" value="Yes, Do it!" />
</form>
```

The user will be presented with Google's API consent screen (Or google sign in page if not already signed in followed by the latter). After confirming consent for Googleposta to access the users contacts within GC he/she will be redirected back to the return url (if provided).

The return url will receive a query string **status** value indicating the success or failure of the authorization request.


### Removing an existing connection

To remove connection between Laposta and GC the following options must be sent to https://{googleposta_host}/authority/delete using the POST method. The same can be accomplished by sending the same to https://{googleposta_host}/authority/ using the DELETE method.

* **email** - Laposta account holder email
* **lapostaApiToken** - Laposta API key
* **returnUrl** (optional) - Return URL

An example using an HTML form:

```html
<form method="post" action="https://{googleposta_host}/authority/delete">
    <input type="hidden" name="email" value="{laposta_login}" />
    <input type="hidden" name="lapostaApiToken" value="{laposta_api_key}" />
    <input type="hidden" name="returnUrl" value="{return_url}" />

    <input type="submit" value="Yes, Delete it!" />
</form>
```

All data caches in googleposta belonging to the user are removed and the authorisation keys allowing googleposta to connect to GC are deleted. The user will then be redirected back to the return url (if provided).

The return url will receive a query string **status** value indicating the success or failure of the authorization request.


### Trigger synchronisation of accounts

To import contacts from GC into laposta the following options must be sent to https://{googleposta_host}/sync/import using the POST method.

* **email** - Laposta account holder email
* **lapostaApiToken** - Laposta API key
* **returnUrl** (optional) - Return URL

An example using an HTML form:

```html
<form method="post" action="https://{googleposta_host}/sync/import">
    <input type="hidden" name="email" value="{laposta_login}" />
    <input type="hidden" name="lapostaApiToken" value="{laposta_api_key}" />
    <input type="hidden" name="returnUrl" value="{return_url}" />

    <input type="submit" value="Yes, Import them all!" />
</form>
```

Once complete, the user will then be redirected back to the return url (if provided).

The return url will receive a query string **status** value indicating the success or failure of the import.
