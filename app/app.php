<?php

declare(strict_types=1);

function getTransactionFiles($dirPath): array
{
    $files = [];
    foreach (scandir($dirPath) as $file) {
        if (is_dir($file)) {
            continue;
        }

        $files[] = $dirPath   . $file;
    }

    return $files;
}

function getTransactions(string $filename, ?callable $transactionHandler =  null): array
{
    if (!file_exists($filename)) {
        trigger_error("file {$filename} does not exist ", E_USER_ERROR);
    }


    $file = fopen($filename, 'r');

    fgetcsv($file);


    $transactions = [];

    while (($transaction = fgetcsv($file)) !== false) {

        if ($transactionHandler !== null) {
            $transaction = $transactionHandler($transaction);
        }

        $transactions[] = $transaction;
    }

    return $transactions;
}


function extractTransaction(array $transactionRow): array
{
    [$date, $checkNumber, $description, $amount] = $transactionRow;

    $amount = (float) str_replace(['$', ','], '', $amount);

    return array(
        'date' => $date,
        'checkNumber' => $checkNumber,
        'description' => $description,
        'amount' => $amount,
    );
}


function calculateTotals(array $transactions): array
{
    $totals = ['netTotal' => 0, 'totalIncome' => 0, 'totalExpense' => 0];

    foreach ($transactions as $transaction) {
        $totals['totalIncome'] += $transaction['amount'];

        if ($transaction['amount'] >= 0) {
            $totals['netTotal'] += $transaction['amount'];
        } else {
            $totals['totalExpense'] += $transaction['amount'];
        }
    }

    return $totals;
}
