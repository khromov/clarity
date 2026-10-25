//
// svg-injector.js
//

// Initialize the injection of SVGs into the DOM from src on img tags

import { SVGInjector } from '@tanem/svg-injector';

SVGInjector(document.querySelectorAll('[data-inject-svg]'));
