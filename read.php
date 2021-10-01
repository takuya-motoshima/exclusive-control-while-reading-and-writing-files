<?php
$file = 'test.txt';

// Wait for the write process to start writing to the file.
while (!file_exists($file) || filesize($file) === 0) sleep(1);

// Open file.
$fp = fopen($file, 'r+');
echo "read - Open\n";

// Lock file.
// $lock = flock($fp, LOCK_EX);// Other processes will not be able to modify or browse files.
// if (!$lock) throw new \RuntimeException('Can\'t lock file');
// echo "read - Lock\n";

// Wait until you finish writing the file, then read it
$retries = 0;
$timeoutSecs = 10;
$gotLock = true;
while (!flock($fp, LOCK_EX|LOCK_NB, $wouldBlock)) {
  if ($wouldBlock && $retries++ < $timeoutSecs)
    sleep(1);
  else {
    $gotLock = false;
    sleep(1);// 
    break;
  }
}

// Read the file.
$contents = file_get_contents($file);
// $contents = fread($fp, filesize($file));
echo "read - Contents: \"{$contents}\"\n";

// Unlock.
flock($fp, LOCK_UN);
fclose($fp);
echo "read - Unlock\n";