<?php declare(strict_types = 1);

return [
	'lastFullAnalysisTime' => 1761166980,
	'meta' => array (
  'cacheVersion' => 'v12-linesToIgnore',
  'phpstanVersion' => '2.1.31',
  'metaExtensions' => 
  array (
  ),
  'phpVersion' => 80412,
  'projectConfig' => '{parameters: {level: 5, paths: [C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app], excludePaths: {analyseAndScan: [app/vendor/phpmailer, app/vendor, vendor], analyse: []}, tmpDir: C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\var\\cache\\phpstan}}',
  'analysedPaths' => 
  array (
    0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app',
  ),
  'scannedFiles' => 
  array (
  ),
  'composerLocks' => 
  array (
    'C:/wamp64/www/FORMATION/BUT/BdeLive/composer.lock' => 'edaad29627296f13351187a5949eaa068ad83cf9',
  ),
  'composerInstalled' => 
  array (
    'C:/wamp64/www/FORMATION/BUT/BdeLive/vendor/composer/installed.php' => 
    array (
      'versions' => 
      array (
        'phpstan/phpstan' => 
        array (
          'pretty_version' => '2.1.31',
          'version' => '2.1.31.0',
          'reference' => 'ead89849d879fe203ce9292c6ef5e7e76f867b96',
          'type' => 'library',
          'install_path' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\vendor\\composer/../phpstan/phpstan',
          'aliases' => 
          array (
          ),
          'dev_requirement' => true,
        ),
      ),
    ),
  ),
  'executedFilesHashes' => 
  array (
    'phar://C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\Attribute85.php' => '123dcd45f03f2463904087a66bfe2bc139760df0',
    'phar://C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\ReflectionAttribute.php' => '0b4b78277eb6545955d2ce5e09bff28f1f8052c8',
    'phar://C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\ReflectionIntersectionType.php' => 'a3e6299b87ee5d407dae7651758edfa11a74cb11',
    'phar://C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\vendor\\phpstan\\phpstan\\phpstan.phar\\stubs\\runtime\\ReflectionUnionType.php' => '1b349aa997a834faeafe05fa21bc31cae22bf2e2',
  ),
  'phpExtensions' => 
  array (
    0 => 'Core',
    1 => 'PDO',
    2 => 'Phar',
    3 => 'Reflection',
    4 => 'SPL',
    5 => 'SimpleXML',
    6 => 'bcmath',
    7 => 'calendar',
    8 => 'ctype',
    9 => 'curl',
    10 => 'date',
    11 => 'dom',
    12 => 'filter',
    13 => 'hash',
    14 => 'iconv',
    15 => 'json',
    16 => 'libxml',
    17 => 'mbstring',
    18 => 'mysqlnd',
    19 => 'openssl',
    20 => 'pcre',
    21 => 'random',
    22 => 'readline',
    23 => 'session',
    24 => 'standard',
    25 => 'tokenizer',
    26 => 'xml',
    27 => 'xmlreader',
    28 => 'xmlwriter',
    29 => 'zlib',
  ),
  'stubFiles' => 
  array (
  ),
  'level' => '5',
),
	'projectExtensionFiles' => array (
),
	'errorsCallback' => static function (): array { return array (
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php' => 
  array (
    0 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Instantiated class PHPMailer\\PHPMailer\\PHPMailer not found.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 49,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 49,
       'nodeType' => 'PhpParser\\Node\\Expr\\New_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    1 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method isSMTP() on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 52,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 52,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    2 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Host on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 53,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 53,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    3 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Host on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 53,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 53,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    4 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $SMTPAuth on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 54,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 54,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    5 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $SMTPAuth on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 54,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 54,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    6 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Username on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 55,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    7 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Username on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 55,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 55,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    8 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Password on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 56,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 56,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    9 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Password on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 56,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 56,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    10 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to constant ENCRYPTION_STARTTLS on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 57,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 57,
       'nodeType' => 'PhpParser\\Node\\Expr\\ClassConstFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    11 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $SMTPSecure on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 57,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 57,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    12 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $SMTPSecure on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 57,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 57,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    13 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Port on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 58,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 58,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    14 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Port on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 58,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 58,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    15 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method setFrom() on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 61,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 61,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    16 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method addAddress() on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 62,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 62,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    17 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method isHTML() on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 65,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 65,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    18 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $CharSet on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 66,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    19 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $CharSet on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 66,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 66,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    20 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Subject on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 67,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 67,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    21 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Subject on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 67,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 67,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    22 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Body on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 68,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    23 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $Body on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 68,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 68,
       'nodeType' => 'PHPStan\\Node\\PropertyAssignNode',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    24 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Call to method send() on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 70,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 70,
       'nodeType' => 'PhpParser\\Node\\Expr\\MethodCall',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    25 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Caught class PHPMailer\\PHPMailer\\Exception not found.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 74,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 74,
       'nodeType' => 'PhpParser\\Node\\Stmt\\Catch_',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    26 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Access to property $ErrorInfo on an unknown class PHPMailer\\PHPMailer\\PHPMailer.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 75,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => 'Learn more at https://phpstan.org/user-guide/discovering-symbols',
       'nodeLine' => 75,
       'nodeType' => 'PhpParser\\Node\\Expr\\PropertyFetch',
       'identifier' => 'class.notFound',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
    27 => 
    \PHPStan\Analyser\Error::__set_state(array(
       'message' => 'Variable $mail might not be defined.',
       'file' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'line' => 75,
       'canBeIgnored' => true,
       'filePath' => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php',
       'traitFilePath' => NULL,
       'tip' => NULL,
       'nodeLine' => 75,
       'nodeType' => 'PhpParser\\Node\\Expr\\Variable',
       'identifier' => 'variable.undefined',
       'metadata' => 
      array (
      ),
       'fixedErrorDiff' => NULL,
    )),
  ),
); },
	'locallyIgnoredErrorsCallback' => static function (): array { return array (
); },
	'linesToIgnore' => array (
),
	'unmatchedLineIgnores' => array (
),
	'collectedDataCallback' => static function (): array { return array (
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'Mailer',
        1 => 'getEmailTextVersion',
        2 => 'Mailer',
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\config.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'define',
        1 => 4,
      ),
      1 => 
      array (
        0 => 'define',
        1 => 5,
      ),
      2 => 
      array (
        0 => 'define',
        1 => 6,
      ),
      3 => 
      array (
        0 => 'define',
        1 => 7,
      ),
      4 => 
      array (
        0 => 'define',
        1 => 8,
      ),
      5 => 
      array (
        0 => 'define',
        1 => 10,
      ),
      6 => 
      array (
        0 => 'define',
        1 => 11,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\core\\Database.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\MethodWithoutImpurePointsCollector' => 
    array (
      0 => 
      array (
        0 => 'Database',
        1 => 'getConnection',
        2 => 'Database',
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\include\\auth.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'session_start',
        1 => 5,
      ),
      1 => 
      array (
        0 => 'session_start',
        1 => 16,
      ),
      2 => 
      array (
        0 => 'http_response_code',
        1 => 25,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\include\\autoload.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'spl_autoload_register',
        1 => 3,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\index.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'error_reporting',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'ini_set',
        1 => 3,
      ),
      2 => 
      array (
        0 => 'session_start',
        1 => 5,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AdminController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'DefaultController',
        1 => '__construct',
        2 => 6,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AuthenticatedController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'DefaultController',
        1 => '__construct',
        2 => 5,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\DefaultController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\ConstructorWithoutImpurePointsCollector' => 
    array (
      0 => 'DefaultController',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\events\\CreateEventController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'AdminController',
        1 => '__construct',
        2 => 5,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ResetPasswordController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureMethodCallCollector' => 
    array (
      0 => 
      array (
        0 => 
        array (
          0 => 'PasswordReset',
        ),
        1 => 'markTokenAsUsed',
        2 => 81,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\AuthController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'session_start',
        1 => 63,
      ),
      1 => 
      array (
        0 => 'session_start',
        1 => 93,
      ),
      2 => 
      array (
        0 => 'session_unset',
        1 => 96,
      ),
      3 => 
      array (
        0 => 'session_destroy',
        1 => 97,
      ),
      4 => 
      array (
        0 => 'session_start',
        1 => 149,
      ),
      5 => 
      array (
        0 => 'session_start',
        1 => 165,
      ),
      6 => 
      array (
        0 => 'session_start',
        1 => 181,
      ),
      7 => 
      array (
        0 => 'session_start',
        1 => 201,
      ),
      8 => 
      array (
        0 => 'session_start',
        1 => 217,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\DeleteAccountController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'session_start',
        1 => 30,
      ),
      1 => 
      array (
        0 => 'setcookie',
        1 => 55,
      ),
      2 => 
      array (
        0 => 'session_unset',
        1 => 57,
      ),
      3 => 
      array (
        0 => 'session_destroy',
        1 => 58,
      ),
      4 => 
      array (
        0 => 'session_start',
        1 => 59,
      ),
    ),
    'PHPStan\\Rules\\DeadCode\\PossiblyPureStaticCallCollector' => 
    array (
      0 => 
      array (
        0 => 'AuthenticatedController',
        1 => '__construct',
        2 => 13,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\LoginController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'session_start',
        1 => 55,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\LogoutController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'setcookie',
        1 => 30,
      ),
      1 => 
      array (
        0 => 'session_destroy',
        1 => 33,
      ),
      2 => 
      array (
        0 => 'session_start',
        1 => 35,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\RegisterController.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'session_start',
        1 => 53,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\pwd\\PasswordReset.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'date_default_timezone_set',
        1 => 70,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\events\\createEventPageView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 1,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 84,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\events\\eventPageView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'useCarousel',
        1 => 17,
      ),
      2 => 
      array (
        0 => 'useCarousel',
        1 => 18,
      ),
      3 => 
      array (
        0 => 'end_page',
        1 => 22,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\homePageView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 8,
      ),
      1 => 
      array (
        0 => 'session_start',
        1 => 15,
      ),
      2 => 
      array (
        0 => 'useCarousel',
        1 => 39,
      ),
      3 => 
      array (
        0 => 'end_page',
        1 => 97,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\legalTermsPageView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 139,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\sitemapView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'session_start',
        1 => 25,
      ),
      2 => 
      array (
        0 => 'end_page',
        1 => 49,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\teamView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 79,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\forgotPasswordView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 31,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\resetPasswordView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 29,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\verifyTokenView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 27,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\deleteAccountView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'session_start',
        1 => 4,
      ),
      2 => 
      array (
        0 => 'end_page',
        1 => 43,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\loginPageView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 37,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\registerPageView.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'start_page',
        1 => 2,
      ),
      1 => 
      array (
        0 => 'end_page',
        1 => 44,
      ),
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\rooter.php' => 
  array (
    'PHPStan\\Rules\\DeadCode\\PossiblyPureFuncCallCollector' => 
    array (
      0 => 
      array (
        0 => 'http_response_code',
        1 => 54,
      ),
    ),
  ),
); },
	'dependencies' => array (
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php' => 
  array (
    'fileHash' => 'd899a398349a0a3a2abd7527ee2604356736abe9',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ForgotPasswordController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\config.php' => 
  array (
    'fileHash' => 'e121c5de67597637aebf5f338cbd29d03201643e',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\core\\Database.php' => 
  array (
    'fileHash' => '51621d19b5a9b351fa065df5f931d00043c40f1a',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\admin\\EventCreationModel.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\pwd\\PasswordReset.php',
      2 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\users\\UserManager.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\cron\\clean_expired_tokens.php' => 
  array (
    'fileHash' => 'b51632a9849b8fe0eecce6ba06d909559f704cba',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\include\\auth.php' => 
  array (
    'fileHash' => '176f307a42ce7aa7ef33495c0ea84cb82c922f30',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AdminController.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AuthenticatedController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\include\\autoload.php' => 
  array (
    'fileHash' => 'c561fce51f0335ef04805a99086eb6fd58a89e9c',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\index.php' => 
  array (
    'fileHash' => '6cf8aa04da44f4c80e0516a861391fa44aa4aa52',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AdminController.php' => 
  array (
    'fileHash' => 'd0bc22380f639f32f4083f703d76502b4cf86bba',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\events\\CreateEventController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AuthenticatedController.php' => 
  array (
    'fileHash' => '205fcb0f8dcb17246cdf6f663905860125d024ba',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\DeleteAccountController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\DefaultController.php' => 
  array (
    'fileHash' => '4d754e19a834728515df93653d33c1fdf80eec57',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AdminController.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AuthenticatedController.php',
      2 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\events\\CreateEventController.php',
      3 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\DeleteAccountController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\events\\CreateEventController.php' => 
  array (
    'fileHash' => 'ac7290a2be3d049c7eadc92ceb6ce43b93404137',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\events\\EventController.php' => 
  array (
    'fileHash' => 'ca1b5a4fda515b4908f93051be13c298f9a2860e',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\HomeController.php' => 
  array (
    'fileHash' => 'aaa361b610196cbc6dded1bf1bd941993f69247c',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\LegalTermsController.php' => 
  array (
    'fileHash' => '15ae90f2b47dde3e3df4df3f27503a36ceac350e',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\SitemapController.php' => 
  array (
    'fileHash' => '0c634b199d559c362f254009353756f5e7675497',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\TeamController.php' => 
  array (
    'fileHash' => 'f083815c79dd0a675103d03761ba0a801a814309',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ForgotPasswordController.php' => 
  array (
    'fileHash' => 'edecfae08c35a31064ed2c2fbae058a8ce013b85',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ResetPasswordController.php' => 
  array (
    'fileHash' => '22c7685626088e0354d96418893b254bc52cc4d7',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\VerifyTokenController.php' => 
  array (
    'fileHash' => '5d081f6e812e5ec2babdcfa532053411ddc520ca',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\AuthController.php' => 
  array (
    'fileHash' => 'b5a57d6ef5e77746f1497ee7b318ffae7c5af788',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\LoginController.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\RegisterController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\DeleteAccountController.php' => 
  array (
    'fileHash' => '73c6de7bd17a2d664aa97137399e4354137340f1',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\LoginController.php' => 
  array (
    'fileHash' => 'd42a7b71e20d6bbea88230a16664f644766094c7',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\LogoutController.php' => 
  array (
    'fileHash' => 'c7d821293eb4192a65099bee93a1aa139c733299',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\RegisterController.php' => 
  array (
    'fileHash' => '04166eaf007589dc29682e8794c89fb1813d4d1c',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\admin\\EventCreationModel.php' =>
  array (
    'fileHash' => '4e8317d49cd365b78e1e32c49ff24ac57a8abace',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\pwd\\PasswordReset.php' => 
  array (
    'fileHash' => 'f4f46c68b64df4073f975fe659f477605033d9c9',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\cron\\clean_expired_tokens.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ForgotPasswordController.php',
      2 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ResetPasswordController.php',
      3 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\VerifyTokenController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\users\\UserManager.php' => 
  array (
    'fileHash' => '68a57d96b786d8e2a058a8cee1a360315789b54c',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\AuthController.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\DeleteAccountController.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\events\\createEventPageView.php' => 
  array (
    'fileHash' => '71bc2b54b7d7ae6576b4298cd340ec3d3d22e11e',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\events\\eventPageView.php' => 
  array (
    'fileHash' => 'f8a8cc907261e9ca8efe844387ca5e61187a8298',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\homePageView.php' => 
  array (
    'fileHash' => 'f93f41ce1f5ad36eb338e2c4780abb294149e77e',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\legalTermsPageView.php' => 
  array (
    'fileHash' => 'ce3a7ce4e09f0efeca7fd873505fde0cd49358ff',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\sitemapView.php' => 
  array (
    'fileHash' => '030d2bc21147105270b957997d7a89498d707e7c',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\teamView.php' => 
  array (
    'fileHash' => '81f391a168db7dbd24aeccdf9de7270d5a21b248',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\forgotPasswordView.php' => 
  array (
    'fileHash' => '1d16425a4eaf940420f220f933ca2f4bc752a922',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\resetPasswordView.php' => 
  array (
    'fileHash' => '7fb2147e043852a01b5210046117d6ceabb66058',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\verifyTokenView.php' => 
  array (
    'fileHash' => '37c335206b21ad381d77ca1b868ad7d20d44c41d',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\shared\\carousel.inc.php' => 
  array (
    'fileHash' => '6c1097e1b72e3d2a0cc099b86ebda2b42a6dd2c4',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\events\\eventPageView.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\homePageView.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\shared\\include.inc.php' => 
  array (
    'fileHash' => '71c7a0e6e20ba7dd5a1100e4f0fb773b18123ffb',
    'dependentFiles' => 
    array (
      0 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\events\\createEventPageView.php',
      1 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\events\\eventPageView.php',
      2 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\homePageView.php',
      3 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\legalTermsPageView.php',
      4 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\sitemapView.php',
      5 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\public\\teamView.php',
      6 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\forgotPasswordView.php',
      7 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\resetPasswordView.php',
      8 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\pwd\\verifyTokenView.php',
      9 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\deleteAccountView.php',
      10 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\loginPageView.php',
      11 => 'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\registerPageView.php',
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\deleteAccountView.php' => 
  array (
    'fileHash' => '4b4e0a5662058179377a71f684cad6e80bb8d3cd',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\loginPageView.php' => 
  array (
    'fileHash' => '0e70c717506c986c3ad99b61dd23afd315b4b87e',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\users\\registerPageView.php' => 
  array (
    'fileHash' => 'ef2ab71ab8789c2759e0a6aa7c02ae7df87a196e',
    'dependentFiles' => 
    array (
    ),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\rooter.php' => 
  array (
    'fileHash' => 'abb8f19b514ab700739901a3db2741f3e4a16c9c',
    'dependentFiles' => 
    array (
    ),
  ),
),
	'exportedNodesCallback' => static function (): array { return array (
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\config\\Mailer.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'Mailer',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Email Service Provider
 *
 * Handles email sending functionality using PHPMailer library.
 * Configured to work with AlwaysData SMTP server for sending
 * password reset emails and other application notifications.
 *
 * @package BdeLive\\Services
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'sendPasswordResetEmail',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Send a password reset email
     *
     * Sends an email containing a password reset token to the specified recipient.
     * The email is sent via SMTP using the AlwaysData mail server.
     *
     * @param string $to_email Recipient\'s email address
     * @param string $to_name Recipient\'s full name
     * @param string $token The password reset token (64-character hex string)
     * @return bool True if email sent successfully, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'to_email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'to_name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'token',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\core\\Database.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'Database',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/** 
 * This class manages a single PDO database connection instance throughout
 * the application lifecycle. It ensures only one connection exists and
 * provides access to it via the getInstance() method.
 * 
 * @package BdeLive\\Config
 * @author Mohamed-Amine Boudhib, Thomas Palot 
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getInstance',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the single instance of the Database class
     * 
     * Creates a new instance if one doesn\'t exist, otherwise returns
     * the existing instance. This ensures only one database connection
     * exists throughout the application.
     * 
     * @return Database The singleton instance
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => true,
           'returnType' => 'Database',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getConnection',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the PDO connection object
     * 
     * Returns the active PDO connection that can be used to execute
     * database queries throughout the application.
     * 
     * @return PDO The PDO database connection
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'PDO',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__wakeup',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Prevent unserialization of the singleton instance
     * 
     * @throws Exception Always throws exception to prevent unserialization
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\include\\auth.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'requireLogin',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => 'void',
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'requireAdmin',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => 'void',
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AdminController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'AdminController',
       'phpDoc' => NULL,
       'abstract' => true,
       'final' => false,
       'extends' => 'DefaultController',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\AuthenticatedController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'AuthenticatedController',
       'phpDoc' => NULL,
       'abstract' => true,
       'final' => false,
       'extends' => 'DefaultController',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\DefaultController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'DefaultController',
       'phpDoc' => NULL,
       'abstract' => true,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'loadView',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => true,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'viewName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\events\\CreateEventController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'CreateEventController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => 'AdminController',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'loadView',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'viewName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\events\\EventController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EventController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'loadView',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'viewName',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\HomeController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'HomeController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Home Controller
 * 
 * Handles the display of the application\'s home page.
 * This is the main entry point for users visiting the application.
 * 
 * @package BdeLive\\Controllers
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the home page
     * 
     * Loads and renders the home page view for the application.
     * 
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\LegalTermsController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'LegalTermsController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Legal Terms Controller
 * 
 * Handles the display of legal terms and conditions page.
 * 
 * @package BdeLive\\Controllers
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Display the legal terms page
     * 
     * Loads and renders the legal terms view containing terms of service
     * and privacy policy information.
     * 
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\SitemapController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'SitemapController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\public\\TeamController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'TeamController',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ForgotPasswordController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'ForgotPasswordController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Forgot Password Controller
 * 
 * Handles the password reset request process. Validates user email,
 * generates a secure reset token, and sends it via email.
 * First step in the password recovery workflow.
 * 
 * @package BdeLive\\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle forgot password page requests
     * 
     * Displays the forgot password form on GET requests, or processes
     * the email submission on POST requests.
     * 
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\ResetPasswordController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'ResetPasswordController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Reset Password Controller
 * 
 * Handles the final step of password reset workflow. Allows users to
 * enter a new password after successful token verification.
 * Validates password requirements and updates the user\'s password.
 * 
 * @package BdeLive\\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle password reset page requests
     * 
     * Ensures user has valid reset token and user ID in session,
     * displays password reset form on GET, or processes password
     * change on POST.
     * 
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\pwd\\VerifyTokenController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'VerifyTokenController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Verify Token Controller
 * 
 * Handles the token verification step in the password reset workflow.
 * Validates the token sent to the user\'s email and ensures it\'s not
 * expired or already used. Second step in password recovery.
 * 
 * @package BdeLive\\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle token verification page requests
     * 
     * Ensures user came from forgot password flow, displays token input
     * form on GET, or processes token verification on POST.
     * 
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\AuthController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'AuthController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Authentication Controller
 * 
 * Handles user authentication, registration, session management and user data retrieval.
 * This controller acts as a bridge between the user interface (the views) and the UserManager model,
 * managing the authentication workflow and session state.
 * 
 * @package BdeLive\\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Constructor - Initialize the AuthController
     * 
     * Creates a new UserManager instance for handling user-related database operations.
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'login',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Authenticate a user with email and password
     * 
     * Validates user credentials against the database. If successful, creates
     * a new session and stores user information in session variables.
     * 
     * @param string $email The user\'s email address
     * @param string $pwd The user\'s password
     * @return bool True if authentication successful, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pwd',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'logout',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Handle user logout
     * 
     * Destroys the current session and redirects to home page.
     * Clears all session data and cookies associated with the user\'s session.
     * 
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'register',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Register a new user
     * 
     * Creates a new user account after validating the email format and checking
     * for duplicate email addresses. The password is hashed before storage.
     * 
     * @param string $last_name User\'s last name
     * @param string $first_name User\'s first name
     * @param string $user_status User\'s class year (1, 2, or 3)
     * @param string $email User\'s email address
     * @param string $pwd User\'s password (will be hashed)
     * @return int|false The new user ID if successful, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int|false',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'last_name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'first_name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_status',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'pwd',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isLoggedIn',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if a user is currently logged in
     * 
     * Verifies the presence of required session variables to determine
     * if a user has an active authenticated session.
     * 
     * @return bool True if user is logged in, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCurrentUserId',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the current logged-in user\'s ID
     * 
     * Retrieves the user ID from the current session if available.
     * 
     * @return int|null The user ID, or null if not logged in
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?int',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCurrentUserFullName',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the current logged-in user\'s full name
     * 
     * Returns the user\'s first name and last name concatenated.
     * 
     * @return string|null The user\'s full name, or null if not logged in
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCurrentUserEmail',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the current logged-in user\'s email address
     * 
     * Retrieves the email address from the current session.
     * 
     * @return string|null The user\'s email, or null if not logged in
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCurrentUserClasseAnnee',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the current logged-in user\'s class year
     * 
     * Retrieves the class year information from the current session.
     * 
     * @return string|null The user\'s class year (1, 2, or 3), or null if not logged in
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => '?string',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getCurrentUserData',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get the complete user data for the currently logged-in user
     * 
     * Retrieves all user information from the database for the current session user.
     * Returns user data including ID, name, email, and class year.
     * 
     * @return array|false Array of user data if found, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array|false',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\DeleteAccountController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'DeleteAccountController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Delete Account Controller
 *
 * Allows a logged-in user to delete their own account.
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => 'AuthenticatedController',
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'loadView',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => false,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'void',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'viewName',
               'type' => NULL,
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\LoginController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'LoginController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Login Controller
 * 
 * Handles user login operations including form display, input validation,
 * and authentication processing. Works with AuthController to verify
 * user credentials and establish authenticated sessions.
 * 
 * @package BdeLive\\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Constructor - Initialize the LoginController
     * 
     * Creates a new AuthController instance for handling authentication operations.
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\LogoutController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'LogoutController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Logout Controller
 * 
 * Handles user logout operations, including session destruction
 * and cleanup of session cookies. Redirects users to the home page
 * after successful logout.
 * 
 * @package BdeLive\\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Process user logout
     * 
     * Destroys the current user session, clears all session data and cookies,
     * then redirects to the home page with a success message.
     * 
     * @return void
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\controllers\\users\\RegisterController.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'RegisterController',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Register Controller
 * 
 * Handles user registration operations including form display, input validation,
 * account creation, and automatic login after successful registration.
 * Works with AuthController to create new user accounts.
 * 
 * @package BdeLive\\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Constructor - Initialize the RegisterController
     * 
     * Creates a new AuthController instance for handling registration operations.
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\admin\\EventCreationModel.php' =>
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'EventCreationModel',
       'phpDoc' => NULL,
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'isAdmin',
           'phpDoc' => NULL,
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'userId',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\pwd\\PasswordReset.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'PasswordReset',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * Password Reset Model
 * 
 * Handles password reset functionality including token generation, validation,
 * and password updates. Manages the PASSWORD_RESET_TOKEN table for secure
 * password reset operations with time-limited tokens.
 * 
 * @package BdeLive
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Constructor - Initialize the PasswordReset model
     * 
     * Retrieves the database connection from the Database singleton.
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getUserByEmail',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get user information by email address
     * 
     * Retrieves basic user information (without password) for password reset purposes.
     * 
     * @param string $email The user\'s email address
     * @return array|false Array containing user data if found, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array|false',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'createToken',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create a password reset token
     *
     * Generates a secure random token valid for 3 hours and stores it in the database.
     * The token is a 64-character hexadecimal string.
     *
     * @param int $user_id The ID of the user requesting password reset
     * @return string|false The generated token if successful, false otherwise
     *
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string|false',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_id',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'verifyToken',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verify a password reset token
     * 
     * Checks if a token is valid, not expired, and not already used.
     * Automatically deletes expired tokens.
     * 
     * @param string $token The token to verify
     * @return array Associative array with \'valid\' (bool), and if valid: \'user_id\' and \'token_id\',
     *               or if invalid: \'message\' (string) explaining why
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'token',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'markTokenAsUsed',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Mark a token as used
     * 
     * Prevents a token from being reused for multiple password resets.
     * Should be called after a successful password reset.
     * 
     * @param string $token The token to mark as used
     * @return bool True if successful, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'token',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updatePassword',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update a user\'s password
     * 
     * Changes the user\'s password to a new value. The password is hashed using SHA-1
     * before being stored in the database.
     * 
     * @param int $user_id The ID of the user whose password to update
     * @param string $new_password The new password (plain text, will be hashed)
     * @return bool True if successful, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_id',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'new_password',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'cleanExpiredTokens',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Clean expired and used tokens
     * 
     * Removes all expired tokens and used tokens from the database.
     * This method should be called periodically (e.g., via cron job) to maintain
     * database hygiene.
     * 
     * @return bool True if successful, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\models\\users\\UserManager.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedClassNode::__set_state(array(
       'name' => 'UserManager',
       'phpDoc' => 
      \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
         'phpDocString' => '/**
 * User Manager Model
 * 
 * Handles all user-related database operations including CRUD operations,
 * password hashing and verification, and user search functionality.
 * This class provides a data access layer for the USERS table.
 * 
 * @package BdeLive\\Models
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */',
         'namespace' => NULL,
         'uses' => 
        array (
        ),
         'constUses' => 
        array (
        ),
      )),
       'abstract' => false,
       'final' => false,
       'extends' => NULL,
       'implements' => 
      array (
      ),
       'usedTraits' => 
      array (
      ),
       'traitUseAdaptations' => 
      array (
      ),
       'statements' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => '__construct',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Constructor - Initialize the UserManager
     * 
     * Retrieves the database connection from the Database singleton.
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => NULL,
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'hashPassword',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Hash a password using SHA-1
     * 
     * Generates a hashed password of the one that is provided
     * 
     * @param string $password The plain text password to hash
     * @return string The hashed password
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'string',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'password',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'verifyPassword',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Verify a password against its hash
     * 
     * Compares a plain text password with its hashed version to verify if they match.
     * 
     * @param string $password The plain text password to verify
     * @param string $hashedPassword The hashed password to compare against
     * @return bool True if the password matches, false otherwise
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'password',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'hashedPassword',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        3 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'findUserByEmail',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Find a user by email address
     * 
     * Searches for a user in the database using their email address.
     * Returns all user information including the hashed password.
     * 
     * @param string $email The email address to search for
     * @return array|false Array containing user data if found, false otherwise
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array|false',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        4 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'findUserById',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Find a user by their ID 
     * 
     * Retrieves user information from the database using the user ID.
     * Does not return the password field for security reasons.
     * 
     * @param int $user_id The user ID to search for
     * @return array|false Array containing user data if found, false otherwise
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array|false',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_id',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        5 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'getAllUsers',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Get all users from the database
     * 
     * Retrieves all registered users, ordered by last name and first name.
     * Password fields are excluded from the results (for security reasons).
     * 
     * @return array Array of user records (empty array if no users found)
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
          ),
           'attributes' => 
          array (
          ),
        )),
        6 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'findUsersByUserStatus',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Find users by class year
     * 
     * Retrieves all users belonging to a specific class year,
     * ordered by last name and first name.
     * 
     * @param string $user_status The user status to filter by (BUT 1, BUT 2, BUT 3, Personnel Enseignant)
     * @return array Array of user records (empty array if no users found)
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'array',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_status',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        7 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'createUser',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Create a new user
     * 
     * Inserts a new user record into the database with hashed password.
     * The password is automatically hashed before storage.
     * 
     * @param string $last_name User\'s last name
     * @param string $first_name User\'s first name
     * @param string $user_status User\'s status (BUT 1, BUT 2, BUT 3, Personnel Enseignant)
     * @param string $email User\'s email address
     * @param string $password User\'s password (plain text, will be hashed)
     * @return int|false The new user ID if successful, false otherwise
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'int|false',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'last_name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'first_name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_status',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'password',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        8 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updateUser',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update user information
     * 
     * Updates an existing user\'s profile information (excluding password).
     * 
     * @param int $user_id The ID of the user to update
     * @param string $last_name New last name
     * @param string $first_name New first name
     * @param string $user_status New user status (BUT 1, BUT 2, BUT 3, Personnel Enseignant)
     * @param string $email New email address
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_id',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'last_name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            2 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'first_name',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            3 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_status',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            4 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        9 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'updatePassword',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Update a user\'s password
     * 
     * Changes a user\'s password. The new password is automatically hashed
     * before storage.
     * 
     * @param int $user_id The ID of the user
     * @param string $new_password The new password (plain text, will be hashed)
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_id',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
            1 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'new_password',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        10 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'deleteUser',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Delete a user from the database
     * 
     * Permanently removes a user record. This operation cannot be undone.
     * 
     * @param int $user_id The ID of the user to delete
     * @return bool True if deletion successful, false otherwise
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'user_id',
               'type' => 'int',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
        11 => 
        \PHPStan\Dependency\ExportedNode\ExportedMethodNode::__set_state(array(
           'name' => 'emailExists',
           'phpDoc' => 
          \PHPStan\Dependency\ExportedNode\ExportedPhpDocNode::__set_state(array(
             'phpDocString' => '/**
     * Check if an email address already exists
     * 
     * Useful for validation during user registration to prevent duplicate accounts.
     * 
     * @param string $email The email address to check
     * @return bool True if email exists, false otherwise
     * @throws PDOException If database query fails
     */',
             'namespace' => NULL,
             'uses' => 
            array (
            ),
             'constUses' => 
            array (
            ),
          )),
           'byRef' => false,
           'public' => true,
           'private' => false,
           'abstract' => false,
           'final' => false,
           'static' => false,
           'returnType' => 'bool',
           'parameters' => 
          array (
            0 => 
            \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
               'name' => 'email',
               'type' => 'string',
               'byRef' => false,
               'variadic' => false,
               'hasDefault' => false,
               'attributes' => 
              array (
              ),
            )),
          ),
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\shared\\carousel.inc.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'useCarousel',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'carouselLabel',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'imageMap',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        2 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'carouselId',
           'type' => NULL,
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
  'C:\\wamp64\\www\\FORMATION\\BUT\\BdeLive\\app\\modules\\views\\shared\\include.inc.php' => 
  array (
    0 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'start_page',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
        0 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'title',
           'type' => 'string',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => false,
           'attributes' => 
          array (
          ),
        )),
        1 => 
        \PHPStan\Dependency\ExportedNode\ExportedParameterNode::__set_state(array(
           'name' => 'wouldNav',
           'type' => 'bool',
           'byRef' => false,
           'variadic' => false,
           'hasDefault' => true,
           'attributes' => 
          array (
          ),
        )),
      ),
       'attributes' => 
      array (
      ),
    )),
    1 => 
    \PHPStan\Dependency\ExportedNode\ExportedFunctionNode::__set_state(array(
       'name' => 'end_page',
       'phpDoc' => NULL,
       'byRef' => false,
       'returnType' => NULL,
       'parameters' => 
      array (
      ),
       'attributes' => 
      array (
      ),
    )),
  ),
); },
];
