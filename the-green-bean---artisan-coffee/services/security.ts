
/**
 * Security Service
 * Implements PBKDF2 for password hashing and salting.
 * Implements Magic Number detection for file type validation.
 */

export const hashPassword = async (password: string, salt: Uint8Array): Promise<string> => {
  const encoder = new TextEncoder();
  const passwordKey = await window.crypto.subtle.importKey(
    'raw',
    encoder.encode(password),
    { name: 'PBKDF2' },
    false,
    ['deriveBits', 'deriveKey']
  );

  // Fix: Removed the extra '{ name: 'PBKDF2' }' argument. 
  // deriveKey expects: algorithm, baseKey, derivedKeyType, extractable, keyUsages
  const key = await window.crypto.subtle.deriveKey(
    {
      name: 'PBKDF2',
      salt: salt,
      iterations: 100000,
      hash: 'SHA-256',
    },
    passwordKey,
    { name: 'AES-GCM', length: 256 },
    true,
    ['encrypt', 'decrypt']
  );

  const exportedKey = await window.crypto.subtle.exportKey('raw', key);
  return btoa(String.fromCharCode(...new Uint8Array(exportedKey)));
};

export const generateSalt = (): Uint8Array => {
  return window.crypto.getRandomValues(new Uint8Array(16));
};

export const validateEmail = (email: string): boolean => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
};

/**
 * Detects file type based on magic numbers (header bytes)
 */
export const isValidImageFile = async (file: File): Promise<boolean> => {
  const buffer = await file.arrayBuffer();
  const uint = new Uint8Array(buffer);
  const bytes: string[] = [];
  uint.slice(0, 4).forEach((byte) => {
    bytes.push(byte.toString(16));
  });
  const hex = bytes.join('').toUpperCase();

  const magicNumbers: Record<string, string> = {
    '89504E47': 'image/png',
    'FFD8FFDB': 'image/jpeg',
    'FFD8FFE0': 'image/jpeg',
    'FFD8FFE1': 'image/jpeg',
    'FFD8FFE2': 'image/jpeg',
  };

  // Check PNG and JPEG signatures
  return hex.startsWith('89504E47') || hex.startsWith('FFD8FF');
};
