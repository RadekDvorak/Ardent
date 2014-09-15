<?php
use Collections\SortedSet;

require_once __DIR__ . "/autoload.php";

$set = new SortedSet();
$size = 94;

for($i = 0; $i < $size; $i++)
{
	$set->add($i);
}

foreach ($set as $item)
{

}

/*
(recursionLimit) $ php recursionLimit.php
PHP Fatal error:  Maximum function nesting level of '100' reached, aborting! in somedirectory/Ardent/src/BinaryTree.php on line 21
PHP Stack trace:
PHP   1. {main}() somedirectory/Ardent/recursionLimit.php:0
PHP   2. Collections\SortedSet->getIterator() somedirectory/Ardent/recursionLimit.php:14
PHP   3. Collections\SplayTree->getIterator() somedirectory/Ardent/src/SortedSet.php:119
PHP   4. Collections\SplayTree->copyNode() somedirectory/Ardent/src/SplayTree.php:149
PHP   5. Collections\SplayTree->copyNode() somedirectory/Ardent/src/SplayTree.php:217
PHP   6. Collections\SplayTree->copyNode() somedirectory/Ardent/src/SplayTree.php:217
PHP   7. Collections\SplayTree->copyNode() somedirectory/Ardent/src/SplayTree.php:217
PHP   8. Collections\SplayTree->copyNode() somedirectory/Ardent/src/SplayTree.php:217
PHP   9. Collections\SplayTree->copyNode() somedirectory/Ardent/src/SplayTree.php:217
...
 */
