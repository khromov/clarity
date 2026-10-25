
//
//
// aos.js
//
// Initialises the animate on scroll plugin

import AOS from 'aos';

$(window).on('load', () => {
  AOS.init({ once: true });
});