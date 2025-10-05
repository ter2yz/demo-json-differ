<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DiffService
{
    /**
     * Compare two JSON objects and return line-by-line differences
     */
    public function compareJsonAsLines($obj1, $obj2): array
    {
        try {
            $str1 = json_encode($obj1, JSON_PRETTY_PRINT);
            $str2 = json_encode($obj2, JSON_PRETTY_PRINT);

            $lines1 = explode("\n", $str1);
            $lines2 = explode("\n", $str2);

            return $this->diffLines($lines1, $lines2);
        } catch (\Exception $e) {
            Log::error('Error in compareJsonAsLines fn: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Perform line-level diff using dynamic programming
     */
    private function diffLines(array $lines1, array $lines2): array
    {
        $result = [];
        $m = count($lines1);
        $n = count($lines2);

        if ($m === 0 && $n === 0) {
            return [];
        }

        // Build DP table for edit distance
        $dp = $this->buildDPTable($lines1, $lines2);

        // Backtrack to find differences
        $ops = $this->backtrack($lines1, $lines2, $dp);

        // Convert operations to diff lines
        return $this->convertToDiffLines($ops);
    }

    /**
     * Build dynamic programming table for edit distance
     */
    private function buildDPTable(array $lines1, array $lines2): array
    {
        $m = count($lines1);
        $n = count($lines2);

        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, ['cost' => 0, 'op' => 'match']));

        // Initialize base cases
        for ($i = 0; $i <= $m; $i++) {
            $dp[$i][0] = ['cost' => $i, 'op' => 'delete'];
        }
        for ($j = 0; $j <= $n; $j++) {
            $dp[0][$j] = ['cost' => $j, 'op' => 'insert'];
        }

        // Fill DP table
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($lines1[$i - 1] === $lines2[$j - 1]) {
                    $dp[$i][$j] = ['cost' => $dp[$i - 1][$j - 1]['cost'], 'op' => 'match'];
                } else {
                    $deleteCost = $dp[$i - 1][$j]['cost'] + 1;
                    $insertCost = $dp[$i][$j - 1]['cost'] + 1;
                    $replaceCost = $dp[$i - 1][$j - 1]['cost'] + 1;

                    if ($deleteCost <= $insertCost && $deleteCost <= $replaceCost) {
                        $dp[$i][$j] = ['cost' => $deleteCost, 'op' => 'delete'];
                    } elseif ($insertCost <= $replaceCost) {
                        $dp[$i][$j] = ['cost' => $insertCost, 'op' => 'insert'];
                    } else {
                        $dp[$i][$j] = ['cost' => $replaceCost, 'op' => 'replace'];
                    }
                }
            }
        }

        return $dp;
    }

    /**
     * Backtrack through DP table to find operations
     */
    private function backtrack(array $lines1, array $lines2, array $dp): array
    {
        $ops = [];
        $i = count($lines1);
        $j = count($lines2);

        while ($i > 0 || $j > 0) {
            $current = $dp[$i][$j];

            if ($current['op'] === 'match' && $i > 0 && $j > 0) {
                array_unshift($ops, [
                    'op' => 'match',
                    'left' => $lines1[$i - 1],
                    'right' => $lines2[$j - 1]
                ]);
                $i--;
                $j--;
            } elseif ($current['op'] === 'delete' && $i > 0) {
                array_unshift($ops, [
                    'op' => 'delete',
                    'left' => $lines1[$i - 1]
                ]);
                $i--;
            } elseif ($current['op'] === 'insert' && $j > 0) {
                array_unshift($ops, [
                    'op' => 'insert',
                    'right' => $lines2[$j - 1]
                ]);
                $j--;
            } elseif ($current['op'] === 'replace' && $i > 0 && $j > 0) {
                array_unshift($ops, [
                    'op' => 'replace',
                    'left' => $lines1[$i - 1],
                    'right' => $lines2[$j - 1]
                ]);
                $i--;
                $j--;
            } else {
                break;
            }
        }

        return $ops;
    }

    /**
     * Convert operations to diff line format
     */
    private function convertToDiffLines(array $ops): array
    {
        $result = [];
        $leftNum = 1;
        $rightNum = 1;

        foreach ($ops as $op) {
            $operation = $op['op'];

            if ($operation === 'match') {
                $result[] = [
                    'leftNumber' => $leftNum++,
                    'rightNumber' => $rightNum++,
                    'left' => $op['left'] ?? '',
                    'right' => $op['right'] ?? '',
                    'status' => 'unchanged'
                ];
            } elseif ($operation === 'delete') {
                $result[] = [
                    'leftNumber' => $leftNum++,
                    'rightNumber' => null,
                    'left' => $op['left'] ?? '',
                    'right' => '',
                    'status' => 'removed'
                ];
            } elseif ($operation === 'insert') {
                $result[] = [
                    'leftNumber' => null,
                    'rightNumber' => $rightNum++,
                    'left' => '',
                    'right' => $op['right'] ?? '',
                    'status' => 'added'
                ];
            } elseif ($operation === 'replace') {
                $result[] = [
                    'leftNumber' => $leftNum++,
                    'rightNumber' => $rightNum++,
                    'left' => $op['left'] ?? '',
                    'right' => $op['right'] ?? '',
                    'status' => 'modified'
                ];
            }
        }

        return $result;
    }
}
