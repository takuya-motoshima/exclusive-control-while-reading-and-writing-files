# exclusive-control-while-reading-and-writing-files

This is a test program for exclusive control of file reading and writing.  
Here, two processes write and read to one file at the same time,  
the read process waits for the write process to complete, and then reads the file after the write process ompletes.

## Usage

You can actually test the exclusive control of file reading and writing with the following command.

```sh
rm -f test.txt && php write.php & php read.php;

# Output: write - Open
#         write - Lock
#         write - Write "H"
#         read - Open
#         write - Write "e"
#         write - Write "l"
#         write - Write "l"
#         write - Write "o"
#         write - Write " "
#         write - Write "W"
#         write - Write "o"
#         write - Write "r"
#         write - Write "l"
#         write - Write "d"
#         write - Write "!"
#         read - Contents: "Hello World!"
#         read - Unlock
#         write - Unlock
```

## Reference

### read.php

This program reads the file after another process has finished writing the file.  
If it takes more than 10 seconds for another process to read the file, it will forcibly stop waiting and read the file.

```php
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
```

### write.php

This program locks the file exclusively and writes Hello World character by character to the file every second.  
When all the characters are written, the lock is released and the file is closed.

```php
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
```
