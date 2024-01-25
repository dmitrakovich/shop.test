import * as Sentry from '@sentry/browser';

Sentry.init({
  dsn: 'https://29c9b17aba169d65d6ce3570ffb78738@o4505659571961856.ingest.sentry.io/4506633176154112',
  integrations: [],
  // Performance Monitoring
  tracesSampleRate: 0.1,
});
