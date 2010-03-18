<?php
function debug()
{
  $args = func_get_args();
  foreach ($args as $index => $arg)
  {
    Debug::dump($arg);
  }
}

function fd()
{
  $args = func_get_args();
  foreach ($args as $index => $arg)
  {
    Debug::fireLog($arg);
  }
}
?>