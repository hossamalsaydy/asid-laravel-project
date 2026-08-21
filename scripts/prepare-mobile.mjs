import { mkdir, writeFile } from 'node:fs/promises';

const backendUrl = (process.env.MOBILE_BACKEND_URL || '').trim().replace(/\/$/, '');

if (!backendUrl) {
    console.warn('MOBILE_BACKEND_URL is empty; the mobile shell will show a configuration error.');
}

await mkdir('mobile/www', { recursive: true });
await writeFile(
    'mobile/www/runtime-config.js',
    `// Generated file. Do not commit environment-specific backend URLs.\nwindow.__ASID_BACKEND_URL__ = ${JSON.stringify(backendUrl)};\n`,
    'utf8',
);

console.log(`Prepared mobile shell for ${backendUrl || 'an unset backend URL'}.`);
