
import './aos';
import './fade-page';
import './sticky';
import './svg-injector';

(() => {
  if (typeof $ === 'undefined') {
    throw new TypeError('Medium Rare JavaScript requires jQuery. jQuery must be included before theme.js.');
  }
})();
