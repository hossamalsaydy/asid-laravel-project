import type { CapacitorConfig } from '@capacitor/cli';

const backendUrl = (globalThis as {
    process?: { env?: Record<string, string | undefined> };
}).process?.env?.MOBILE_BACKEND_URL?.trim().replace(/\/$/, '') || '';

let backendHost: string | undefined;
if (backendUrl) {
    try {
        backendHost = new URL(backendUrl).hostname;
    } catch {
        throw new Error('MOBILE_BACKEND_URL must be a valid URL.');
    }
}

const config: CapacitorConfig = {
    appId: 'com.asid.clinic',
    appName: 'ASID Clinic',
    webDir: 'mobile/www',
    backgroundColor: '#0f172a',
    loggingBehavior: 'none',
    android: {
        allowMixedContent: false,
        backgroundColor: '#0f172a',
    },
    server: backendHost ? {
        allowNavigation: [backendHost],
    } : undefined,
};

export default config;

// The Laravel UI is server-rendered. The packaged shell in mobile/www redirects
// to the configured Laravel backend while Capacitor owns the native container.
