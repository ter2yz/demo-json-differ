import type { DiffChars } from "@/types";

// Tokenize string into words based on boundaries
export const tokenize = (str: string): string[] => {
  const tokens: string[] = [];
  let current = "";
  let lastType = "";

  for (let i = 0; i < str.length; i++) {
    const char = str.charAt(i); // Use charAt() instead of [] to guarantee string
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
};

// Word-based diff using dynamic programming
export const diffChars = (oldStr: string, newStr: string): DiffChars[] => {
  const oldTokens = tokenize(oldStr);
  const newTokens = tokenize(newStr);

  const m = oldTokens.length;
  const n = newTokens.length;

  // Initialize with proper types
  const dp: number[][] = Array.from({ length: m + 1 }, () =>
    Array.from({ length: n + 1 }, () => 0)
  );

  for (let i = 0; i <= m; i++) {
    for (let j = 0; j <= n; j++) {
      if (i === 0) {
        dp[i]![j] = j;
      } else if (j === 0) {
        dp[i]![j] = i;
      } else if (oldTokens[i - 1] === newTokens[j - 1]) {
        dp[i]![j] = dp[i - 1]![j - 1]!;
      } else {
        dp[i]![j] = 1 + Math.min(dp[i - 1]![j]!, dp[i]![j - 1]!, dp[i - 1]![j - 1]!);
      }
    }
  }

  const result: DiffChars[] = [];
  let i = m,
    j = n;

  while (i > 0 || j > 0) {
    if (i > 0 && j > 0 && oldTokens[i - 1] === newTokens[j - 1]) {
      result.unshift({ value: oldTokens[i - 1]! });
      i--;
      j--;
    } else if (j > 0 && (i === 0 || dp[i]![j - 1]! <= dp[i - 1]![j]!)) {
      result.unshift({ value: newTokens[j - 1]!, added: true });
      j--;
    } else if (i > 0) {
      result.unshift({ value: oldTokens[i - 1]!, removed: true });
      i--;
    }
  }

  const merged: DiffChars[] = [];
  for (const part of result) {
    const last = merged[merged.length - 1];
    if (last && last.added === part.added && last.removed === part.removed) {
      last.value += part.value;
    } else {
      merged.push({ ...part });
    }
  }

  return merged;
};
