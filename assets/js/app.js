require('../css/app.scss');

// Sentry
import * as Sentry from '@sentry/browser';
// Import routing
import Routing from 'fos-routing';

// Only bind when production mode is set
if (window.SENTRY_DSN) {
  // Create the default sentry client
  // This instance will communicate any default JS errors
  Sentry.init({
    dsn: window.SENTRY_DSN,
    release: window.SENTRY_RELEASE,
  });
}

// Create global $ and jQuery variables
const $ = require('jquery');
global.$ = global.jQuery = $;

global.Routing = Routing;

// Disable scroll restoration if possible
if ('scrollRestoration' in window.history) {
  // Back off, browser, I got this...
  window.history.scrollRestoration = 'manual';
}
