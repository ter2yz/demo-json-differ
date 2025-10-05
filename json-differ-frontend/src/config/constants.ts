export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api";

// Type-safe environment variables
export const ENV = {
  API_BASE_URL: import.meta.env.VITE_API_BASE_URL as string,
} as const;
