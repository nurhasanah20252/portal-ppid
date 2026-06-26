/// <reference types="vitest" />
import { resolve } from 'path';
import { defineConfig } from 'vitest/config';

export default defineConfig({
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
    test: {
        globals: true,
        environment: 'node',
        include: ['tests/js/**/*.{test,spec}.{ts,tsx}'],
    },
});
