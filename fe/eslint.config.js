import js from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';
import jsxA11y from 'eslint-plugin-jsx-a11y';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';
// import simpleImportSort from 'eslint-plugin-simple-import-sort';
import importPlugin from 'eslint-plugin-import';
import unusedImports from 'eslint-plugin-unused-imports';
import unicorn from 'eslint-plugin-unicorn';
import globals from 'globals';
import tseslint from 'typescript-eslint';
import { defineConfig, globalIgnores } from 'eslint/config';

export default defineConfig([
  globalIgnores(['dist', 'coverage', 'node_modules']),

  {
    linterOptions: {
      reportUnusedDisableDirectives: 'error',
    },
  },

  {
    files: ['**/*.{ts,tsx}'],

    extends: [
      js.configs.recommended,

      // TypeScript strict type-aware linting
      tseslint.configs.strictTypeChecked,
      tseslint.configs.stylisticTypeChecked,

      // React
      react.configs.flat.recommended,
      react.configs.flat['jsx-runtime'],

      // Hooks
      reactHooks.configs.flat.recommended,

      // Accessibility
      jsxA11y.flatConfigs.recommended,

      // Vite React refresh rules
      reactRefresh.configs.vite,

      // Disable formatting conflicts
      eslintConfigPrettier,
    ],

    languageOptions: {
      ecmaVersion: 'latest',
      globals: globals.browser,

      parserOptions: {
        project: ['./tsconfig.app.json', './tsconfig.node.json'],
        tsconfigRootDir: import.meta.dirname,
      },
    },

    settings: {
      react: {
        version: 'detect',
      },
      'import/resolver': {
        typescript: true,
        node: true,
      },
    },

    plugins: {
      import: importPlugin,
      'unused-imports': unusedImports,
      unicorn,
    },

    rules: {
      //
      // TypeScript correctness
      //

      '@typescript-eslint/consistent-type-imports': [
        'error',
        {
          prefer: 'type-imports',
        },
      ],

      '@typescript-eslint/no-floating-promises': 'error',

      '@typescript-eslint/no-misused-promises': [
        'error',
        {
          checksVoidReturn: {
            attributes: false,
          },
        },
      ],

      '@typescript-eslint/no-unnecessary-condition': 'error',

      '@typescript-eslint/no-unnecessary-type-assertion': 'error',

      '@typescript-eslint/switch-exhaustiveness-check': 'error',

      '@typescript-eslint/prefer-nullish-coalescing': 'error',

      '@typescript-eslint/prefer-optional-chain': 'error',

      '@typescript-eslint/only-throw-error': 'error',

      //
      // Unused code
      //

      '@typescript-eslint/no-unused-vars': 'off',

      'unused-imports/no-unused-imports': 'error',

      'unused-imports/no-unused-vars': [
        'error',
        {
          vars: 'all',
          varsIgnorePattern: '^_',
          args: 'after-used',
          argsIgnorePattern: '^_',
        },
      ],

      //
      // Import ordering
      //

      'import/order': [
        'error',
        {
          groups: ['builtin', 'external', 'internal', ['parent', 'sibling', 'index'], 'type'],
          pathGroups: [
            {
              pattern: '@/**',
              group: 'internal',
            },
          ],
          pathGroupsExcludedImportTypes: ['builtin'],
          'newlines-between': 'always',
          alphabetize: {
            order: 'asc',
            caseInsensitive: true,
          },
        },
      ],

      'import/no-duplicates': 'error',

      //
      // React
      //

      'react/prop-types': 'off',

      'react/jsx-no-useless-fragment': 'error',

      'react/self-closing-comp': 'error',

      'react/no-unstable-nested-components': 'warn',

      //
      // Unicorn (selected useful rules)
      //

      'unicorn/prefer-node-protocol': 'error',

      'unicorn/prefer-array-find': 'error',

      'unicorn/prefer-array-some': 'error',

      'unicorn/prefer-modern-dom-apis': 'error',

      'unicorn/prefer-structured-clone': 'error',

      'unicorn/prefer-optional-catch-binding': 'error',

      'unicorn/no-array-for-each': 'warn',

      //
      // General quality
      //

      'no-console': [
        'warn',
        {
          allow: ['warn', 'error'],
        },
      ],
    },
  },

  //
  // Tests
  //

  {
    files: ['**/*.{test,spec}.{ts,tsx}'],

    rules: {
      '@typescript-eslint/no-floating-promises': 'off',
    },
  },

  //
  // Config files don't need type checking
  //

  {
    files: ['vite.config.ts', 'eslint.config.js'],

    extends: [tseslint.configs.disableTypeChecked],
  },
]);
