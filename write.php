<?php
$file = 'test.txt';

// Open file.
$fp = fopen($file, 'a+');
echo "write - Open\n";

// Lock file.
$lock = flock($fp, LOCK_EX);// Other processes will not be able to modify or browse files.
// $lock = flock($fp, LOCK_EX|LOCK_NB);// Other processes will not be able to modify or browse files.
// $lock = flock($fp, LOCK_SH);// Other processes can see the file, but cannot change it.
if (!$lock) throw new \RuntimeException('Can\'t lock file');
echo "write - Lock\n";

// Write "Hello World" character by character in the file
$str = 'Hello World!';
for ($i=0, $len=strlen($str); $i<$len; $i++) {
  $ch = $str[$i];
  fwrite($fp, $ch);
  echo "write - Write \"{$ch}\"\n";
  sleep(1);
}

// // Read the file.
// rewind($fp);
// $contents = fread($fp, filesize($file));
// echo "write - Contents: \"{$contents}\"\n";

// Unlock.
flock($fp, LOCK_UN);
fclose($fp);
echo "write - Unlock\n";