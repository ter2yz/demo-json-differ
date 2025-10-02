import React, { useEffect, useState } from "react";

import { diffFixture } from "@/fixtures/diff";
// import { diffChars } from "diff";

type DiffStatus = "added" | "removed" | "modified" | "unchanged";

export interface DiffLine {
  leftNumber: number | null;
  rightNumber: number | null;
  left: string;
  right: string;
  status: DiffStatus;
}

// Tokenize string into words based on boundaries
function tokenize(str: string): string[] {
  const tokens: string[] = [];
  let current = "";
  let lastType = "";

  for (let i = 0; i < str.length; i++) {
    const char = str[i];
    let currentType = "";

    if (/[a-zA-Z]/.test(char)) {
      currentType = "letter";
    } else if (/[0-9]/.test(char)) {
      currentType = "number";
    } else if (/\s/.test(char)) {
      currentType = "space";
    } else {
      currentType = "punctuation";
    }

    // Start new token if type changes
    if (lastType && lastType !== currentType) {
      tokens.push(current);
      current = char;
    } else {
      current += char;
    }

    lastType = currentType;
  }

  if (current) {
    tokens.push(current);
  }

  return tokens;
}

// Word-based diff using dynamic programming
function diffChars(oldStr: string, newStr: string) {
  const oldTokens = tokenize(oldStr);
  const newTokens = tokenize(newStr);

  const m = oldTokens.length;
  const n = newTokens.length;
  const dp: number[][] = Array(m + 1)
    .fill(0)
    .map(() => Array(n + 1).fill(0));

  // Fill the DP table
  for (let i = 0; i <= m; i++) {
    for (let j = 0; j <= n; j++) {
      if (i === 0) {
        dp[i][j] = j;
      } else if (j === 0) {
        dp[i][j] = i;
      } else if (oldTokens[i - 1] === newTokens[j - 1]) {
        dp[i][j] = dp[i - 1][j - 1];
      } else {
        dp[i][j] = 1 + Math.min(dp[i - 1][j], dp[i][j - 1], dp[i - 1][j - 1]);
      }
    }
  }

  // Backtrack to find the diff
  const result: Array<{ value: string; added?: boolean; removed?: boolean }> =
    [];
  let i = m,
    j = n;

  while (i > 0 || j > 0) {
    if (i > 0 && j > 0 && oldTokens[i - 1] === newTokens[j - 1]) {
      result.unshift({ value: oldTokens[i - 1] });
      i--;
      j--;
    } else if (j > 0 && (i === 0 || dp[i][j - 1] <= dp[i - 1][j])) {
      result.unshift({ value: newTokens[j - 1], added: true });
      j--;
    } else if (i > 0) {
      result.unshift({ value: oldTokens[i - 1], removed: true });
      i--;
    }
  }

  // Merge consecutive same-type changes
  const merged: Array<{ value: string; added?: boolean; removed?: boolean }> =
    [];
  for (const part of result) {
    const last = merged[merged.length - 1];
    if (last && last.added === part.added && last.removed === part.removed) {
      last.value += part.value;
    } else {
      merged.push({ ...part });
    }
  }

  return merged;
}

// Render left side (removed parts highlighted)
const renderLeftDiff = (oldLine: string, newLine: string) => {
  const diffResult = diffChars(oldLine, newLine);
  return (
    <span className="flex-1 whitespace-pre-wrap">
      {diffResult.map((part, idx) => {
        if (part.removed) {
          return (
            <span key={idx} className="bg-red-300">
              {part.value}
            </span>
          );
        } else if (!part.added) {
          return (
            <span key={idx} className="text-zinc-900 whitespace-pre-wrap">
              {part.value}
            </span>
          );
        }
        return null;
      })}
    </span>
  );
};

// Render right side (added parts highlighted)
const renderRightDiff = (oldLine: string, newLine: string) => {
  const diffResult = diffChars(oldLine, newLine);
  return (
    <span className="flex-1 whitespace-pre-wrap">
      {diffResult.map((part, idx) => {
        if (part.added) {
          return (
            <span key={idx} className="bg-green-300">
              {part.value}
            </span>
          );
        } else if (!part.removed) {
          return (
            <span key={idx} className="text-zinc-900 whitespace-pre-wrap">
              {part.value}
            </span>
          );
        }
        return null;
      })}
    </span>
  );
};

export default function SideBySideDiffViewer() {
  const [diffs, setDiffs] = useState<DiffLine[]>([]);

  useEffect(() => {
    setTimeout(() => {
      setDiffs(diffFixture);
    }, 300);
  }, []);

  const getBgColor = (status: DiffStatus, side: "left" | "right") => {
    if (status === "modified")
      return side === "left" ? "bg-red-100" : "bg-green-100";
    if (status === "added" && side === "right") return "bg-green-100";
    if (status === "added" && side === "left") return "bg-zinc-300";
    if (status === "removed" && side === "left") return "bg-red-100";
    return "";
  };

  return (
    <div className="grid grid-cols-2 font-mono text-sm bg-gray-100 p-4 rounded-lg">
      {/* Left Column - Original */}
      <div className="border-r">
        <div className="font-bold border-b pb-2 mb-2 bg-gray-200 px-2">
          Original
        </div>
        {diffs.map((line, idx) => (
          <div
            key={`left-${idx}`}
            className={`flex ${getBgColor(line.status, "left")}`}
          >
            <div className="w-10 text-right pr-2 text-gray-500 select-none">
              {line.status === "added" ? "" : (line.leftNumber ?? "")}
            </div>
            {line.status === "modified" ? (
              renderLeftDiff(line.left, line.right)
            ) : line.status === "added" ? (
              <div className="flex-1">&nbsp;</div>
            ) : (
              <div className="flex-1 text-zinc-900 whitespace-pre-wrap">
                {line.left}
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Right Column - Modified */}
      <div>
        <div className="font-bold border-b pb-2 mb-2 bg-gray-200 px-2">
          Modified
        </div>
        {diffs.map((line, idx) => (
          <div
            key={`right-${idx}`}
            className={`flex ${getBgColor(line.status, "right")}`}
          >
            <div className="w-10 text-right pr-2 text-gray-500 select-none">
              {line.rightNumber ?? ""}
            </div>
            {line.status === "modified" ? (
              renderRightDiff(line.left, line.right)
            ) : (
              <div className="flex-1 text-zinc-900 whitespace-pre-wrap">
                {line.right}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
