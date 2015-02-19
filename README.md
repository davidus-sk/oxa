# Oxa

Oxa is a lightweight URL shortener built using PHP and MySQL.

>**Note:** You can use Oxa in two ways: *1)* you can host your own URL shortening service by buying a domain name (e.g. shrt.co) and deploying Oxa to it or
>*2)* you can use Oxa's client library ant utilize `http://oxa.us` for URL shortening without the need for your own server (jump to client library below). 

## Requirements

To run your own Oxa powered shortener service you will need the following:

* A server (physical or virtual) or a computer running Windows or Linux
* Apache or Nginx (or other web server)
* PHP5+
* MySQL
* little bit of Linux-Fu
* A domain name

## Installation

Configuration of your web server is beyond the scope of this document.

Simply checkout this project into your web server's document root or wherever you are hosting your domain's content from and edit `conf\config.php` to match your setup. At minimum you need to configure MySQL connection settings:

```php
define('DB_NAME', 'db_oxa');
define('DB_USER', 'database_username');
define('DB_PASSWORD', 'database_password');
define('DB_HOST', 'server_name_or_ip');
```

Load Oxa's database schema from `sql\oxa.sql' onto your MySQL server: `mysql -u database_username -p < sql\oxa.sql` (*database_username* must have CREATE permissions).

## How to use

Oxa is best used via its simple API. See examples in the Client library class.

### POST (add)

To shorten a new URL you simply POST a raw JSON string to `http://yourserver.com/api/` with the following parameters set:

* longURL STRING (required)
  * The long URL you want to shorten
* secret STRING (optional)
  * Secret phrase you want to associate with the long URL (for deletion)

Example using curl: `curl --data '{"longURL":"http://www.google.com","secret":"banana"}' http://oxa.us/api/`

### DELETE

To delete an existing URL you have shortened previously you DELETE a raw json string to `http://yourserver.com/api/` with the following parameters set:

* longURL STRING (required)
  * The long URL you want to shorten
* secret STRING (required)
  * Secret phrase you associated with the long URL

Example using curl: `curl -X DELETE --data '{"longURL":"http://www.google.com","secret":"banana"}' http://oxa.us/api/`

## Client library

Simply include the client library via `require 'OxaClient.php';` and start using it. Sample code:

```php
$shortener = new OxaClient();
$shortener->addUrl('http://haha.com', 'banana');
$shortener->addUrl('http://hihi.com');
$result = $Shortener->shorten();
```



## Support

There are bound to be issues. Feel free to contact me with problems you might come across or submit your own fixes and improvements.