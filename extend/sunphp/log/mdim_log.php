<?php

if (!function_exists('mdim_log')) {
  function mdim_log($msg)
  {
    $ts = date('Y-m-d H:i:s');
    if (!is_string($msg)) {
      $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
    }
    $line = '[' . $ts . '] [mdim] ' . $msg;
    // 只使用 stderr 输出，避免重复日志
    @file_put_contents('php://stderr', $line . PHP_EOL, FILE_APPEND);

    // 本地文件按 年月/日.log 落盘，自动创建目录
    if (defined('IA_ROOT')) {
      $logBaseDir = IA_ROOT . '/runtime/mdim/log';
    } else {
      $logBaseDir = __DIR__ . '/../../../../runtime/mdim/log';
    }
    $yearMonth = date('Ym');
    $dayFile = date('d') . '.log';
    $targetDir = $logBaseDir . '/' . $yearMonth;
    if (!is_dir($targetDir)) {
      @mkdir($targetDir, 0777, true);
    }
    @file_put_contents($targetDir . '/' . $dayFile, $line . PHP_EOL, FILE_APPEND);
  }
}

// Telegram 专用日志：标签 [telegram]，落盘到 runtime/telegram/log
if (!function_exists('telegram_log')) {
  function telegram_log($msg)
  {
    $ts = date('Y-m-d H:i:s');
    if (!is_string($msg)) {
      $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
    }
    $line = '[' . $ts . '] [telegram] ' . $msg;
    @file_put_contents('php://stderr', $line . PHP_EOL, FILE_APPEND);

    if (defined('IA_ROOT')) {
      $logBaseDir = IA_ROOT . '/runtime/telegram/log';
    } else {
      $logBaseDir = __DIR__ . '/../../../../runtime/telegram/log';
    }
    $yearMonth = date('Ym');
    $dayFile = date('d') . '.log';
    $targetDir = $logBaseDir . '/' . $yearMonth;
    if (!is_dir($targetDir)) {
      @mkdir($targetDir, 0777, true);
    }
    @file_put_contents($targetDir . '/' . $dayFile, $line . PHP_EOL, FILE_APPEND);
  }
}
