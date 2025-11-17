import globals from "globals";
import pluginReact from "eslint-plugin-react";
import {defineConfig} from "eslint/config";
import tseslint from "typescript-eslint";

export default defineConfig([
  {
    settings: {
      react: {
        version: "detect",
      },
    },
    files: ['**/*.{js,mjs,cjs,ts,mts,cts,jsx,tsx}'],
    languageOptions: {globals: globals.browser}
  },
  pluginReact.configs.flat.recommended,
  pluginReact.configs.flat['jsx-runtime'],
  tseslint.configs.recommended,
]);
