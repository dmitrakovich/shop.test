module.exports = {
  env: {
    browser: true,
    es2021: true,
    jquery: true,
  },
  extends: 'standard',
  overrides: [
    {
      env: {
        node: true,
      },
      files: [
        '.eslintrc.{js,cjs}',
      ],
      parserOptions: {
        sourceType: 'script',
      },
    },
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
  },
  rules: {
    // 'array-bracket-newline': ['error', 'consistent'],
    // 'array-element-newline': ['error', 'consistent'],
    // 'arrow-body-style': 0,
    // 'arrow-parens': 0,
    // 'brace-style': ['error', '1tbs', { allowSingleLine: true }],
    // 'callback-return': 0,
    // 'capitalized-comments': 0,
    // 'class-methods-use-this': 0,
    'comma-dangle': ['error', 'always-multiline'],
    // 'dot-location': 0,
    // 'eol-last': 0,
    // 'func-names': 0,
    // 'func-style': 0,
    // 'function-call-argument-newline': 0,
    // 'function-paren-newline': 0,
    // 'guard-for-in': ['error'],
    // 'id-length': 0,
    // 'init-declarations': 0,
    // 'line-comment-position': 0,
    // 'linebreak-style': 0,
    // 'max-depth': ['error', 3],
    // 'max-len': 0,
    // 'max-lines': ['error', 600],
    // 'max-nested-callbacks': ['error', 2],
    // 'max-params': ['error', 6],
    // 'max-statements-per-line': ['error', { max: 2 }],
    // 'max-statements': ['error', 39],
    // 'multiline-comment-style': 0,
    // 'multiline-ternary': ['error', 'always-multiline'],
    // 'new-cap': 1,
    // 'no-alert': 0,
    // 'no-console': 0,
    // 'no-duplicate-imports': 0,
    // 'no-else-return': 0,
    // 'no-empty-function': 0,
    // 'no-extra-boolean-cast': 0,
    // 'no-extra-parens': 0,
    // 'no-extra-semi': 0,
    // 'no-implicit-coercion': 0,
    // 'no-inline-comments': 0,
    // 'no-invalid-this': 0,
    // 'no-magic-numbers': ['error', {ignore: [0, 1, 1000], ignoreArrayIndexes: true }],
    'no-multiple-empty-lines': ['error', { max: 2 }],
    // 'no-mixed-operators': 0,
    // 'no-negated-condition': 2,
    // 'no-param-reassign': 0,
    // 'no-plusplus': 0,
    // 'no-process-env': 0,
    // 'no-ternary': 0,
    // 'no-trailing-spaces': 0,
    // 'no-undef': 0,
    // 'no-underscore-dangle': 0,
    // 'no-unused-expressions': 0,
    // 'no-unused-vars': 0,
    // 'no-useless-escape': 0,
    // 'no-warning-comments': 'warn',
    // 'object-curly-spacing': 0,
    // 'object-property-newline': ['error', { allowAllPropertiesOnSameLine: true }],
    // 'object-shorthand': 0,
    // 'one-var': 0,
    // 'operator-linebreak': ['warn', 'before'],
    // 'padded-blocks': 0,
    // 'prefer-const': 'warn',
    // 'prefer-destructuring': 1,
    // 'prefer-template': 0,
    // 'quote-props': 0,
    semi: 'off',
    // 'sort-imports': 0,
    // 'sort-keys': 0,
    // 'sort-vars': 0,
    // 'space-before-function-paren': 0,
    // 'spaced-comment': 0,
    // "camelcase": ["warn", { "properties": "never" }],
    // "no-unused-expressions": 2,
    // indent: 0,
    // quotes: 0,
  },
}
