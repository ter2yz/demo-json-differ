export const validateJSON = (input: string): { valid: boolean; error?: string; data?: any } => {
  if (!input.trim()) {
    return { valid: false, error: "JSON cannot be empty" };
  }

  try {
    const parsed = JSON.parse(input);
    return { valid: true, data: parsed };
  } catch (err: any) {
    return { valid: false, error: `Invalid JSON: ${err.message}` };
  }
};
